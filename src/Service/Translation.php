<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\Broker;
use App\Enum\Directory\SystemDirectory;
use App\Enum\Directory\TargetDirectory;
use App\Enum\Language;
use App\Enum\Type;
use App\Exception\Translation\LanguageFileNotFoundException;
use App\Exception\Translation\MissingTranslationKeyException;
use Exception;

class Translation
{
    /** @var array<string, string> */
    private array $translations;

    private string $translationsDir;

    private Broker   $broker;
    private Language $lang;

    public const CATEGORY_INDICATORS         = 'indicators';
    public const CATEGORY_TARGET_DIRECTORIES = 'target_directories';

    /**
     * @param Language $lang
     * @param Broker $broker
     *
     * @throws Exception
     */
    public function __construct(Language $lang = Language::DE, Broker $broker = Broker::TRADEREPUBLIC)
    {
        $this->translationsDir = dirname(__DIR__, 2) . '/' . SystemDirectory::TRANSLATIONS->value;

        $this->setup($lang, $broker);
    }

    /**
     * Sets the language and the broker
     *
     * @param Language $lang
     * @param Broker $broker
     *
     * @return void
     * @throws Exception
     */
    public function setup(Language $lang, Broker $broker): void
    {
        $this->broker = $broker;
        $this->lang   = $lang;

        $this->translations = $this->importTranslationFile($this->lang);

        $this->validateTranslation(translations: $this->translations, specificBroker: $broker);
    }

    /**
     * @param TargetDirectory $targetDirectory
     *
     * @return string|null
     */
    public function translateDirectory(TargetDirectory $targetDirectory): ?string
    {
        if (!array_key_exists(self::CATEGORY_TARGET_DIRECTORIES, $this->translations)) {
            return null;
        }

        $categoryTranslation = (array) $this->translations[self::CATEGORY_TARGET_DIRECTORIES];

        if (!array_key_exists($targetDirectory->value, $categoryTranslation)) {
            return null;
        }

        return (string) $categoryTranslation[$targetDirectory->value];
    }

    /**
     * @param string $indicator
     *
     * @return string|null
     */
    public function translateIndicator(string $indicator): ?string
    {
        if (!array_key_exists($this->broker->value, $this->translations)) {
            return null;
        }

        $brokerTranslation = (array) $this->translations[$this->broker->value];

        if (!array_key_exists(self::CATEGORY_INDICATORS, $brokerTranslation)) {
            return null;
        }

        $indicatorTranslation = (array) $brokerTranslation[self::CATEGORY_INDICATORS];

        if (!array_key_exists($indicator, $indicatorTranslation)) {
            return null;
        }

        return (string) $indicatorTranslation[$indicator];
    }

    /**
     * Validates the translation file
     *
     * @param array<string, string>|null $translations
     * @param Broker|null $specificBroker
     *
     * @return bool
     * @throws Exception
     */
    private function validateTranslation(?array $translations = null, ?Broker $specificBroker = null): bool
    {
        $translations = is_array($translations) ? $translations : $this->translations;

        $ignoreTypes = [
            Type::OTHER,
        ];

        foreach (TargetDirectory::cases() as $requiredTargetDirectory) {
            if (in_array($requiredTargetDirectory, $ignoreTypes)) {
                continue;
            }

            if (!array_key_exists(self::CATEGORY_TARGET_DIRECTORIES, $translations)) {
                throw new MissingTranslationKeyException(
                    "['" . self::CATEGORY_TARGET_DIRECTORIES . "']",
                    code: 1680013566604
                );
            }

            if (
                !array_key_exists(
                    $requiredTargetDirectory->value,
                    (array) $translations[self::CATEGORY_TARGET_DIRECTORIES]
                )
            ) {
                throw new MissingTranslationKeyException(
                    "['" . self::CATEGORY_TARGET_DIRECTORIES . "'][{$requiredTargetDirectory->value}]",
                    code: 1680013568626
                );
            }
        }

        $brokers = $specificBroker === null ? Broker::cases() : [$specificBroker];

        foreach ($brokers as $broker) {
            if (!array_key_exists($broker->value, $translations)) {
                throw new MissingTranslationKeyException(
                    "['{$broker->value}']",
                    code: 1680113327995
                );
            }

            if (!array_key_exists(self::CATEGORY_INDICATORS, (array) $translations[$broker->value])) {
                throw new MissingTranslationKeyException(
                    "['{$broker->value}']['" . self::CATEGORY_INDICATORS . "']",
                    code: 1679985640080
                );
            }

            foreach (Type::cases() as $requiredIndicator) {
                if (in_array($requiredIndicator, $ignoreTypes)) {
                    continue;
                }

                $brokerTranslations = (array) $translations[$broker->value];

                if (
                    !array_key_exists(
                        $requiredIndicator->value,
                        $brokerTranslations[self::CATEGORY_INDICATORS]
                    )
                ) {
                    throw new MissingTranslationKeyException(
                        "['{$broker->value}']['" . self::CATEGORY_INDICATORS . "'][{$requiredIndicator->value}]",
                        code: 1680013549400
                    );
                }
            }
        }

        return true;
    }

    /**
     * @param Language $lang
     *
     * @return array<string, string>
     * @throws Exception
     */
    private function importTranslationFile(Language $lang): array
    {
        $translationFile = $this->translationsDir . '/' . $lang->value . '.php';

        if (!file_exists($translationFile)) {
            throw new LanguageFileNotFoundException($lang, code: 1679947138219);
        }

        return (array) include $translationFile;
    }

    /**
     * @param Language $lang
     *
     * @return bool
     * @throws Exception
     */
    public function validateTranslationsFile(Language $lang): bool
    {
        $translations = $this->importTranslationFile($lang);

        return $this->validateTranslation($translations);
    }
}
