<?php

namespace App\Classes;

use App\Enums\TargetDirectory;
use App\Enums\Type;
use Exception;

class Translator
{
    /** @var array<string, string> */
    private array $translations;

    private string $translationsDir;

    public const CATEGORY_INDICATORS         = 'indicators';
    public const CATEGORY_TARGET_DIRECTORIES = 'target_directories';

    public function __construct(?string $lang = null)
    {
        $this->translationsDir = dirname(__DIR__, 2) . '/translations';

        if (!empty($lang)) {
            $this->setLang($lang);
        }
    }

    /**
     * Sets the language
     *
     * @param string $lang
     *
     * @return void
     * @throws Exception
     */
    private function setLang(string $lang): void
    {
        if (strlen($lang) !== 2) {
            throw new Exception("Language code must have a length of 2.", 1679946833062);
        }

        $this->translations = $this->importTranslationFile($lang);

        $this->validateTranslation();
    }

    /**
     * Translates the key
     *
     * @param string $key
     * @param string|null $category
     *
     * @return string|null
     */
    public function translate(string $key, ?string $category = null): ?string
    {
        if (is_string($category)) {
            if (!array_key_exists($category, $this->translations)) {
                return null;
            }

            if (!array_key_exists($key, (array) $this->translations[$category])) {
                return null;
            }

            $category = (array) $this->translations[$category];

            return $category[$key];
        }

        return $this->translations[$key] ?? null;
    }

    /**
     * Validates the translation file
     *
     * @param array<string, string>|null $translations
     *
     * @return bool
     * @throws Exception
     */
    private function validateTranslation(?array $translations = null): bool
    {
        $translations = is_array($translations) ? $translations : $this->translations;

        $ignoreTypes = [
            Type::OTHER,
        ];

        foreach (Type::cases() as $requiredIndicator) {
            if (in_array($requiredIndicator, $ignoreTypes)) {
                continue;
            }

            if (!array_key_exists(self::CATEGORY_INDICATORS, $translations)) {
                throw new Exception(
                    "Invalid translation! Missing key: ['" . self::CATEGORY_INDICATORS . "']",
                    1679985640080
                );
            }

            if (!array_key_exists($requiredIndicator->value, (array) $translations[self::CATEGORY_INDICATORS])) {
                throw new Exception(
                    "Invalid translation! Missing key: ['"
                    . self::CATEGORY_INDICATORS
                    . "'][{$requiredIndicator->value}]", 1680013549400
                );
            }
        }

        foreach (TargetDirectory::cases() as $requiredTargetDirectory) {
            if (in_array($requiredTargetDirectory, $ignoreTypes)) {
                continue;
            }

            if (!array_key_exists(self::CATEGORY_TARGET_DIRECTORIES, $translations)) {
                throw new Exception(
                    "Invalid translation! Missing key: ['" . self::CATEGORY_TARGET_DIRECTORIES . "']",
                    1680013566604
                );
            }

            if (
                !array_key_exists(
                    $requiredTargetDirectory->value,
                    (array) $translations[self::CATEGORY_TARGET_DIRECTORIES]
                )
            ) {
                throw new Exception(
                    "Invalid translation! Missing key: ['"
                    . self::CATEGORY_TARGET_DIRECTORIES
                    . "'][{$requiredTargetDirectory->value}]", 1680013568626
                );
            }
        }

        return true;
    }

    /**
     * @param string $lang
     *
     * @return array<string, string>
     * @throws Exception
     */
    private function importTranslationFile(string $lang): array
    {
        $translationFile = $this->translationsDir . '/' . $lang . '.php';

        if (!file_exists($translationFile)) {
            throw new Exception("Language file for '{$lang}' does not exist.", 1679947138219);
        }

        return (array) include $translationFile;
    }

    /**
     * @param string $lang
     *
     * @return bool
     * @throws Exception
     */
    public function validateTranslationsFile(string $lang): bool
    {
        if (strlen($lang) !== 2) {
            throw new Exception("Language code must have a length of 2.", 1680017942179);
        }

        $translations = $this->importTranslationFile($lang);

        return $this->validateTranslation($translations);
    }
}
