<?php

namespace App\Commands;

use App\Classes\Translator;
use App\Enums\Type;
use App\Models\File;
use Exception;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Smalot\PdfParser\Parser;
use SplFileInfo;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

#[AsCommand(name: 'app:run')]
class RunCommand extends Command
{
    private string $sourceRoot;
    private string $targetRoot;
    private string $projectRoot;

    private int $countCopiedFiles = 0;

    private bool   $groupByCode  = false;
    private bool   $groupByType  = false;
    private bool   $keepOldFiles = false;
    private string $lang         = 'de';

    private Parser $parser;

    private Translator $translator;

    public function __construct(string $name = null)
    {
        parent::__construct($name);

        $this->projectRoot = dirname(__DIR__, 2);
        $this->sourceRoot = $this->projectRoot . "/input";
        $this->targetRoot = $this->projectRoot . "/output";

        $this->parser = new Parser();
    }

    /**
     * Configures the command
     *
     * @return void
     */
    protected function configure(): void
    {
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
                 'Group files by type like stocks or crypto.',
             )
             ->addOption(
                 'group-code',
                 'c',
                 InputOption::VALUE_NONE,
                 'Group files by code like ISIN or cryptocurrency abbreviation .',
             )
             ->addOption(
                 'lang',
                 'l',
                 InputOption::VALUE_OPTIONAL,
                 'The language in which the Trade Republic files were generated. (two letters, like: de)',
                 'de'
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
            $this->lang         = strtolower(substr(strval($input->getOption('lang')), 0, 2));
            $this->keepOldFiles = (bool) $input->getOption('keep-files');
            $this->groupByCode  = (bool) $input->getOption('group-code');
            $this->groupByType  = (bool) $input->getOption('group-type');

            $this->translator = new Translator($this->lang);

            $this->checkDirectories();

            if (!$this->keepOldFiles) {
                $this->clearTargetDirectory();
            }

            $output->writeln('Checking files');

            $sourceFiles = $this->getSourceFiles();

            $countSourceFiles = count($sourceFiles);

            $progressBar = new ProgressBar($output, $countSourceFiles);

            foreach ($sourceFiles as $sourceFile) {
                $targetFile = $this->buildModel($sourceFile);

                if ($targetFile instanceof File) {
                    $this->copyFile($sourceFile, $targetFile);
                }

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
            "\n<info>Done!</info>\n Copied <info>{$this->countCopiedFiles}</info> files from source to target directory"
        );

        return Command::SUCCESS;
    }

    /**
     * Returns all files of the source directory
     *
     * @return string[]
     */
    private function getSourceFiles(): array
    {
        $files = [];

        $rii = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $this->sourceRoot,
                FilesystemIterator::SKIP_DOTS
            )
        );

        /** @var SplFileInfo $file */
        foreach ($rii as $file) {
            if ($file->isDir()) {
                continue;
            }

            if (mime_content_type($file->getPathname()) !== 'application/pdf') {
                continue;
            }

            $files[] = $file->getPathname();
        }

        return $files;
    }

    /**
     * Parses the pdf file and returns the text or null
     *
     * @param string $file
     *
     * @return string|null
     */
    private function parsePdf(string $file): ?string
    {
        try {
            return $this->parser->parseFile($file)->getText(1);
        } catch (Throwable $throwable) {
            return null;
        }
    }

    /**
     * Parses the text and returns the ISIN or null
     *
     * @param string $text
     *
     * @return string|null
     */
    private function parseIsin(string $text): ?string
    {
        $pregMatch = preg_match("/ISIN:\s*([A-Z]{2}[A-Z0-9]{9}[0-9])/m", $text, $matches);

        if ((bool) $pregMatch === false || count($matches) < 2) {
            return null;
        }

        return $matches[1];
    }

    /**
     * Parses the text  and returns the date or null
     *
     * @param string $text
     *
     * @return string|null
     */
    private function parseDate(string $text): ?string
    {
        $pregMatch = preg_match(
            "/DATUM\s*((3[01]|[12][0-9]|0?[1-9])\.(1[012]|0?[1-9])\.((?:19|20)\d{2}))/m",
            $text,
            $matches
        );

        if ((bool) $pregMatch === false || count($matches) < 2) {
            return null;
        }

        return $matches[1];
    }

    /**
     * Returns the abbreviation of a cryptocurrency
     *
     * @param string $text
     *
     * @return string|null
     */
    private function getCryptoAbbreviation(string $text): ?string
    {
        $pregMatch = preg_match("/([[:alnum:][:blank:]*]+)\s\(([A-Z]{3,5})\)/m", $text, $matches);

        if ((bool) $pregMatch === false || count($matches) < 2) {
            return null;
        }

        return $matches[2];
    }

    /**
     * Returns the code of the asset or cryptocurrency
     *
     * @param string $text
     * @param Type $type
     *
     * @return string|null
     */
    private function getCode(string $text, Type $type): ?string
    {
        return match ($type) {
            Type::CRYPTO_TRADE => $this->getCryptoAbbreviation($text),
            default => $this->parseIsin($text)
        };
    }

    /**
     * Returns the file model or null
     *
     * @param string $file
     *
     * @return File|null
     */
    private function buildModel(string $file): ?File
    {
        $parse = $this->parsePdf($file);

        if ($parse === null) {
            return null;
        }

        $date = $this->parseDate($parse);
        $type = $this->getType($parse);

        if ($date === null) {
            return null;
        }

        $code = $this->getCode($parse, $type);

        if ($code === null) {
            return null;
        }

        $timestamp     = is_int(strtotime($date)) ? strtotime($date) : null;
        $dateFormatted = date('Ymd', $timestamp);

        return new File(
            $code,
            $dateFormatted,
            $type
        );
    }

    /**
     * Returns the type of the file
     *
     * @param string $text
     *
     * @return Type
     */
    private function getType(string $text): Type
    {
        return match (true) {
            (bool) preg_match(
                "/" . strtoupper(
                    (string) $this->translator->translate(Type::STOCK_TRADE->value, Translator::CATEGORY_INDICATORS)
                ) . "/m",
                $text
            ) => Type::STOCK_TRADE,

            (bool) preg_match(
                "/" . strtoupper(
                    (string) $this->translator->translate(Type::CRYPTO_TRADE->value, Translator::CATEGORY_INDICATORS)
                ) . "/m",
                $text
            ) => Type::CRYPTO_TRADE,

            (bool) preg_match(
                "/"
                . strtoupper(
                    (string) $this->translator->translate(Type::PAYOUT->value, Translator::CATEGORY_INDICATORS)
                )
                . "/m",
                $text
            ) => Type::PAYOUT,

            default => Type::OTHER
        };
    }

    /**
     * Copies the file to the destination directory and renames it
     *
     * @param string $source
     * @param File $target
     *
     * @return void
     * @throws Exception
     */
    private function copyFile(string $source, File $target): void
    {
        $targetDirectory = "{$this->targetRoot}/";

        if ($this->groupByType) {
            if ($target->type->targetDirectory()->parentDirectory() !== null) {
                $targetDirectory .= $this->translator->translate(
                    $target->type->targetDirectory()->parentDirectory()->value,
                    Translator::CATEGORY_TARGET_DIRECTORIES
                ) . "/";
            }

            $targetDirectory .= $this->translator->translate(
                $target->type->targetDirectory()->value,
                Translator::CATEGORY_TARGET_DIRECTORIES
            ) . "/";
        }

        if ($this->groupByCode) {
            $targetDirectory .= "{$target->code}/";
        }

        $targetFilename = "{$target->code}_{$target->date}";

        if (!is_dir($targetDirectory) && !mkdir(directory: $targetDirectory, recursive: true)) {
            throw new Exception("Could not create directory {$targetDirectory}", 1679927836185);
        }

        if (file_exists($targetDirectory . "/" . $targetFilename . ".pdf")) {
            $i = 1;

            $newFilename = $targetFilename;
            while (file_exists($targetDirectory . "/" . $newFilename . ".pdf")) {
                $newFilename = "{$target->code}_{$target->date}-{$i}";

                $i++;
            }

            $targetFilename = $newFilename;
        }

        if (!copy($source, $targetDirectory . "/" . $targetFilename . ".pdf")) {
            throw new Exception(
                "Could not copy file from {$source} to {$targetDirectory}/{$targetFilename}",
                1679927073635
            );
        }

        $this->countCopiedFiles++;
    }

    /**
     * Checks if the directories exist and their permissions
     *
     * @return void
     * @throws Exception
     */
    private function checkDirectories(): void
    {
        if (!is_dir($this->sourceRoot) && !is_readable($this->sourceRoot)) {
            throw new Exception(
                "Path '{$this->sourceRoot}' does not exist or is not readable.",
                1679989741039
            );
        }

        if (!is_dir($this->targetRoot) && !is_writable($this->targetRoot)) {
            throw new Exception(
                "Path '{$this->targetRoot}' does not exist or is not writable.",
                1679989793176
            );
        }
    }

    /**
     * Cleans the target directory
     *
     * @return void
     * @throws Exception
     */
    private function clearTargetDirectory()
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->targetRoot, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            if ($file->getFilename() === '.gitkeep') {
                continue;
            }

            if ($file->isDir()) {
                if (!rmdir($file->getRealPath())) {
                    throw new Exception(
                        "Could not delete directory {$file->getRealPath()}",
                        1679992583360
                    );
                }

                continue;
            }

            if (!unlink($file->getRealPath())) {
                throw new Exception(
                    "Could not delete file {$file->getRealPath()}",
                    1679992610066
                );
            }
        }
    }
}
