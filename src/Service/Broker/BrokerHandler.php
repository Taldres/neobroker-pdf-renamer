<?php

declare(strict_types=1);

namespace App\Service\Broker;

use App\Enum\Broker;
use App\Enum\Type;
use App\Helper\ParseHelper;
use App\Model\File\SourceFile;
use App\Model\File\TargetFile;
use App\Service\FileHandler;
use App\Service\Translation;
use League\Flysystem\FilesystemException;
use Smalot\PdfParser\Parser as PdfParser;
use Throwable;

abstract class BrokerHandler
{
    protected Broker $broker;

    protected PdfParser $pdfParser;

    /**
     * @param Translation $translation
     * @param FileHandler $fileHandler
     */
    public function __construct(private readonly Translation $translation, private readonly FileHandler $fileHandler)
    {
        $this->pdfParser = new PdfParser();
    }

    /**
     * @param SourceFile $sourceFile
     * @param bool $groupTypes
     * @param bool $groupCodes
     *
     * @return TargetFile|null
     */
    public function buildModelFromSource(SourceFile $sourceFile, bool $groupTypes = false, bool $groupCodes = false): ?TargetFile
    {
        try {
            $parse = $this->pdfParser->parseFile($sourceFile->path)->getText(1);
        } catch (Throwable $throwable) {
            return null;
        }

        $date = ParseHelper::parseDate($parse);
        $type = $this->getType($parse);

        if ($date === null || $type === null) {
            return null;
        }

        $code = ParseHelper::parseCode($parse, $type);

        if ($code === null) {
            return null;
        }

        $filename = $this->defineTargetFilename($code, $date);
        $path = $this->defineTargetDirectory($type, $code, $groupTypes, $groupCodes);

        return new TargetFile($path, $filename, $sourceFile, $type, $code, $date);
    }

    /**
     * Returns the type of the file
     *
     * @param string $text
     *
     * @return Type|null
     */
    public function getType(string $text): ?Type
    {
        $ignoredTypes = [
            Type::OTHER,
        ];

        foreach ($this->broker->supportedTypes() as $supportedType) {
            if (in_array($supportedType, $ignoredTypes)) {
                continue;
            }

            $translatedIndicator = $this->translation->translateIndicator(
                $supportedType->value
            );

            if (
                $translatedIndicator !== null
                && ParseHelper::checkIndicator(
                    text: $text,
                    indicator: $translatedIndicator
                )
            ) {
                return $supportedType;
            }
        }

        return null;
    }

    /**
     * Defines the target filename
     *
     * @param string $code
     * @param string $date
     *
     * @return string
     */
    public function defineTargetFilename(string $code, string $date): string
    {
        $timestamp     = is_int(strtotime($date)) ? strtotime($date) : null;
        $dateFormatted = date('Ymd', $timestamp);

        return $code . "_" . $dateFormatted;
    }

    /**
     * @param TargetFile $targetFile
     *
     * @return bool
     * @throws FilesystemException
     */
    public function handleTargetFile(TargetFile $targetFile): bool
    {
        return $this->fileHandler->copySourceFileToTarget(
            $targetFile->sourceFile,
            $targetFile
        );
    }

    public function defineTargetDirectory(Type $type, string $code, bool $groupTypes = false, bool $groupCodes = false): string
    {
        $targetDirectory = "";

        if ($groupTypes) {
            if ($type->targetDirectory()->parentDirectory() !== null) {
                $translatedParentDirectory = $this->translation->translateDirectory(
                    $type->targetDirectory()->parentDirectory(),
                );

                if ($translatedParentDirectory !== null) {
                    $targetDirectory .= $translatedParentDirectory . "/";
                }
            }

            $translatedTargetDirectory = $this->translation->translateDirectory(
                $type->targetDirectory(),
            );

            if ($translatedTargetDirectory !== null) {
                $targetDirectory .= $translatedTargetDirectory;
            }
        }

        if ($groupCodes) {
            if (!empty($targetDirectory)) {
                $targetDirectory .= "/";
            }

            $targetDirectory .= $code;
        }

        return $targetDirectory;
    }
}
