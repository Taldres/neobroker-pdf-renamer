<?php

declare(strict_types=1);

namespace App\Command;

use App\Enum\Broker;
use App\Enum\Directory\SystemDirectory;
use App\Enum\Language;
use App\Enum\Type;
use App\Exception\NoSourceFilesException;
use App\Helper\ConsoleHelper;
use App\Model\File\SourceFile;
use App\Model\File\TargetFile;
use App\Service\Broker\BrokerHandler;
use App\Service\Broker\TraderepublicHandler;
use App\Service\FileHandler;
use App\Service\Translation;
use Exception;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

#[AsCommand(name: 'rename:run')]
class RunCommand extends Command
{
    private string $sourceRoot;
    private string $targetRoot;
    private string $projectRoot;

    private int $countSourceFiles = 0;
    private int $countTargetFiles = 0;

    /** @var array<string, int> $countTargetTypes */
    private array $countTargetTypes = [];

    private bool $groupByCode = false;
    private bool $groupByType = false;

    private BrokerHandler $brokerFileHandler;

    public function __construct(
        private readonly Translation $translation,
        private readonly TraderepublicHandler $traderepublicHandler,
        private readonly FileHandler $fileSystemHandler,
    ) {
        parent::__construct("rename:run");

        $this->projectRoot = dirname(__DIR__, 2);
        $this->sourceRoot  = $this->projectRoot . "/" . SystemDirectory::SOURCE->value;
        $this->targetRoot  = $this->projectRoot . "/" . SystemDirectory::TARGET->value;
    }

    /**
     * Configures the command
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setDescription('Renames Neobroker PDF files');

        $this->addOption(
            'keep-files',
            'k',
            InputOption::VALUE_NONE,
            'Keep already existing files in target directory instead of deleting them at startup.',
        )
             ->addOption(
                 'group-type',
                 't',
                 InputOption::VALUE_NONE,
                 'Group files by type like securities or crypto.',
             )
             ->addOption(
                 'group-code',
                 'c',
                 InputOption::VALUE_NONE,
                 'Group files by code like ISIN or cryptocurrency abbreviation .',
             )->addOption(
                 'lang',
                 'l',
                 InputOption::VALUE_OPTIONAL,
                 'The language in which the broker files were generated.',
                 Language::DE->value
             )
             ->addOption(
                 'broker',
                 'b',
                 InputOption::VALUE_OPTIONAL,
                 'The brokers name who generated the files.',
                 Broker::TRADEREPUBLIC->value
             )
        ;
    }

    /**
     * Executes the Command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $inputBroker = strval($input->getOption('broker'));

            try {
                $broker = Broker::from($inputBroker);
            } catch (Throwable $throwable) {
                ConsoleHelper::writeSupportedBrokers($output, $inputBroker);

                return Command::INVALID;
            }

            $inputLang = strval($input->getOption('lang'));

            try {
                $lang = Language::from($inputLang);
            } catch (Throwable $throwable) {
                ConsoleHelper::writeSupportedLanguages($output, $inputLang);

                return Command::INVALID;
            }

            $keepOldFiles      = (bool) $input->getOption('keep-files');
            $this->groupByCode = (bool) $input->getOption('group-code');
            $this->groupByType = (bool) $input->getOption('group-type');

            ConsoleHelper::writeConfiguration($output, $lang, $broker, $this->groupByType, $this->groupByCode);

            # Setup services
            $this->translation->setup($lang, $broker);
            $this->brokerFileHandler = $this->getBrokerHandler($broker);

            # check directories
            $this->fileSystemHandler->isDirectoryReadable($this->sourceRoot);
            $this->fileSystemHandler->isDirectoryWriteable($this->targetRoot);

            if (!$keepOldFiles) {
                $output->writeln('<comment>Clearing target directory</comment>');

                $this->fileSystemHandler->clearTargetDirectory();
            }

            $output->writeln(['<comment>Checking source files</comment>', '',]);

            $sourceFiles = $this->getSourceFiles();

            $progressBar = new ProgressBar($output, $this->countSourceFiles);

            foreach ($sourceFiles as $sourceFile) {
                $this->handleSourceFile($sourceFile);

                $progressBar->advance();
            }

            $progressBar->finish();
        } catch (Throwable $throwable) {
            $output->writeln(
                "<error>{$throwable->getMessage()}</error>"
            );

            return Command::FAILURE;
        }

        $output->writeln(
            [
                "\t<info>Done! </info>",
                "",
                "🎉 Copied <fg=green;options=bold>{$this->countTargetFiles}</> files from source to target directory:",
            ]
        );

        $this->writeResult($output);

        return Command::SUCCESS;
    }

    /**
     * @param OutputInterface $output
     *
     * @return void
     */
    public function writeResult(OutputInterface $output): void
    {
        $tableRows = [];

        ksort($this->countTargetTypes);

        foreach ($this->countTargetTypes as $copiedType => $count) {
            $tableRows[] = [
                Type::from($copiedType)->label(),
                $count,
            ];
        }

        $table = new Table($output);
        $table
            ->setHeaders(['Type', 'Files'])
            ->setRows($tableRows)
        ;
        $table->render();
    }

    /**
     * Checks all files in the source directory
     *
     * @return SourceFile[]
     * @throws FilesystemException
     * @throws Exception
     */
    private function getSourceFiles(): array
    {
        /** @var string[] $sourcePaths */
        $sourcePaths = $this->fileSystemHandler->getPdfFilesFromSource()->map(
            fn (FileAttributes $i) => $i->path()
        );

        $sourceFiles = [];

        foreach ($sourcePaths as $sourcePath) {
            $sourceFiles[] = $this->fileSystemHandler->buildSourceFile($sourcePath);
        }

        $this->countSourceFiles = count($sourceFiles);

        if ($this->countSourceFiles < 1) {
            throw new NoSourceFilesException(code: 1680287496936);
        }

        return $sourceFiles;
    }

    /**
     * Handles the source file
     *
     * @param SourceFile $sourceFile
     *
     * @return void
     * @throws Exception
     * @throws FilesystemException
     */
    private function handleSourceFile(SourceFile $sourceFile): void
    {
        $targetFile = $this->brokerFileHandler->buildModelFromSource(
            $sourceFile,
            $this->groupByType,
            $this->groupByCode
        );

        if (!$targetFile instanceof TargetFile) {
            return;
        }

        if ($this->brokerFileHandler->handleTargetFile($targetFile)
        ) {
            if (isset($this->countTargetTypes[$targetFile->type->value])) {
                $this->countTargetTypes[$targetFile->type->value]++;
            } else {
                $this->countTargetTypes[$targetFile->type->value] = 1;
            }

            $this->countTargetFiles++;
        }
    }

    /**
     * Returns the handler for the specific broker
     *
     * @param Broker $broker
     *
     * @return TraderepublicHandler
     */
    private function getBrokerHandler(Broker $broker): BrokerHandler
    {
        return match ($broker) {
            Broker::TRADEREPUBLIC => $this->traderepublicHandler,
        };
    }
}
