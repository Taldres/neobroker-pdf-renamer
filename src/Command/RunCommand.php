<?php

declare(strict_types=1);

namespace App\Command;

use App\Enum\Broker;
use App\Enum\Directory\SystemDirectory;
use App\Enum\Language;
use App\Exception\LanguageNotSupportedByBrokerException;
use App\Exception\NoSourceFilesException;
use App\Helper\ConsoleHelper;
use App\Model\File\SourceFile;
use App\Model\File\TargetFile;
use App\Service\AppService;
use App\Service\BrokerService;
use App\Service\FileHandler;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\Translator;
use Throwable;

#[AsCommand(name: 'rename:run')]
class RunCommand extends Command
{
    public function __construct(
        private readonly AppService $appService,
        private readonly FileHandler $fileHandler,
        private readonly BrokerService $brokerService,
        private readonly Translator $translator,
    ) {
        parent::__construct("rename:run");
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
                 'The language in which the broker files were generated. [default: "de"]',
             )
             ->addOption(
                 'broker',
                 'b',
                 InputOption::VALUE_OPTIONAL,
                 'The brokers name who generated the files. [default: "traderepublic"]',
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
            ConsoleHelper::writeAppNameAndVersion($output, $this->appService->appName, $this->appService->appVersion);

            if (!$this->setup($input, $output)) {
                return Command::FAILURE;
            }

            ConsoleHelper::writeConfiguration(
                $output,
                $this->appService->getLanguage(),
                $this->appService->getBroker(),
                $this->appService->isGroupTypes(),
                $this->appService->isGroupCodes(),
                $this->appService->isKeepOldFiles(),
            );

            # check directories
            $this->fileHandler->isDirectoryReadable(SystemDirectory::SOURCE->dirname());
            $this->fileHandler->isDirectoryWriteable(SystemDirectory::TARGET->dirname());

            if (!$this->appService->isKeepOldFiles()) {
                $output->writeln('<comment>Clearing target directory</comment>');

                $this->fileHandler->clearTargetDirectory();
            }

            $output->writeln(['<comment>Checking source files</comment>', '',]);

            $sourceFiles = $this->getSourceFiles();

            $progressBar = new ProgressBar($output, $this->appService->getCountSourceFiles());

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

        ConsoleHelper::writeResult(
            $output,
            $this->appService->getCountSourceFiles(),
            $this->appService->getCountTargetFiles(),
            $this->appService->getCountTargetTypes()
        );

        return Command::SUCCESS;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return bool
     * @throws LanguageNotSupportedByBrokerException
     */
    private function setup(InputInterface $input, OutputInterface $output): bool
    {
        $inputBroker = strval($input->getOption('broker'));

        try {
            $broker = !empty($inputBroker) ? Broker::from($inputBroker) : null;
        } catch (Throwable $throwable) {
            $output->writeln(
                [
                    "<error>Broker '{$inputBroker}' not found/supported.</error>",
                    "",
                ]
            );

            ConsoleHelper::writeSupportedBrokers($output);

            return false;
        }

        $inputLang = strval($input->getOption('lang'));

        try {
            $lang = !empty($inputLang) ? Language::from($inputLang) : null;
        } catch (Throwable $throwable) {
            $output->writeln(
                [
                    "<error>Language '{$inputLang}' not found/supported.</error>",
                    "",
                ]
            );

            ConsoleHelper::writeSupportedLanguages($output, $inputLang);

            return false;
        }

        $keepOldFiles = (bool) $input->getOption('keep-files');
        $groupCodes   = (bool) $input->getOption('group-code');
        $groupTypes   = (bool) $input->getOption('group-type');

        # Setup services
        if ($lang instanceof Language) {
            $this->appService->setLanguage($lang);
            $this->translator->setLocale($lang->value);
        }

        if ($broker instanceof Broker) {
            $this->appService->setBroker($broker);
        }

        $this->appService->setGroupTypes($groupTypes)
                         ->setGroupCodes($groupCodes)
                         ->setKeepOldFiles($keepOldFiles)
        ;

        return true;
    }

    /**
     * Checks all files in the source directory
     *
     * @return SourceFile[]
     * @throws FilesystemException
     * @throws NoSourceFilesException
     */
    private function getSourceFiles(): array
    {
        /** @var string[] $sourcePaths */
        $sourcePaths = $this->fileHandler->getPdfFilesFromSource()->map(
            fn (FileAttributes $i) => $i->path()
        );

        $sourceFiles = [];

        foreach ($sourcePaths as $sourcePath) {
            $sourceFiles[] = $this->fileHandler->buildSourceFile($sourcePath);
        }

        if ($this->appService->getCountSourceFiles() < 1) {
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
     * @throws FilesystemException
     */
    private function handleSourceFile(SourceFile $sourceFile): void
    {
        $brokerHandler = $this->brokerService->getHandler();

        $targetFile = $brokerHandler->buildTargetFileFromSource(
            $sourceFile
        );

        if (!$targetFile instanceof TargetFile) {
            return;
        }

        $brokerHandler->handleTargetFile($targetFile);
    }
}
