<?php

declare(strict_types=1);

namespace App\Service\Broker;

use App\Enum\Broker;
use App\Enum\Type;
use App\Exception\Translation\MissingTranslationKeyException;
use App\Helper\ParseHelper;
use App\Model\File\SourceFile;
use App\Model\File\TargetFile;
use App\Service\AppService;
use App\Service\FileHandler;
use App\Service\TranslationService;
use League\Flysystem\FilesystemException;
use Smalot\PdfParser\Parser;
use Throwable;

abstract class BrokerHandler
{
    protected Broker $broker;

    /**
     * @param AppService $appService
     * @param FileHandler $fileHandler
     * @param TranslationService $translationService
     */
    public function __construct(
        private readonly AppService $appService,
        private readonly FileHandler $fileHandler,
        private readonly TranslationService $translationService,
        private readonly Parser $pdfParser
    ) {
    }

    /**
     * @param SourceFile $sourceFile
     *
     * @return TargetFile|null
     * @throws MissingTranslationKeyException
     */
    public function buildTargetFileFromSource(SourceFile $sourceFile): ?TargetFile
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
        $path     = $this->defineTargetDirectory($type, $code);

        return new TargetFile($path, $filename, $sourceFile, $type, $code, $date);
    }

    /**
     * Returns the type of the file
     *
     * @param string $text
     *
     * @return Type|null
     * @throws MissingTranslationKeyException
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

            $translatedIndicator = $this->translationService->translateIndicator(
                $this->appService->getBroker(),
                $supportedType
            );

            if (ParseHelper::checkIndicator(text: $text, indicator: $translatedIndicator)) {
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
        $timestamp = is_int(strtotime($date)) ? strtotime($date) : null;
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

    public function defineTargetDirectory(Type $type, string $code): string
    {
        $targetDirectory = "";

        if ($this->appService->isGroupTypes()) {
            if ($type->targetDirectory()->parentDirectory() !== null) {
                try {
                    $translatedParentDirectory = $this->translationService->translateTargetDirectory(
                        $type->targetDirectory()->parentDirectory(),
                    );

                    $targetDirectory .= $translatedParentDirectory . "/";
                } catch (Throwable $e) {
                }
            }

            try {
                $translatedTargetDirectory = $this->translationService->translateTargetDirectory(
                    $type->targetDirectory(),
                );

                $targetDirectory .= $translatedTargetDirectory;
            } catch (Throwable $e) {
            }
        }

        if ($this->appService->isGroupCodes()) {
            if (!empty($targetDirectory)) {
                $targetDirectory .= "/";
            }

            $targetDirectory .= $code;
        }

        return $targetDirectory;
    }
}
