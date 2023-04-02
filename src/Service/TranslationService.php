<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\Broker;
use App\Enum\Directory\TargetDirectory;
use App\Enum\Language;
use App\Enum\TranslationCategory;
use App\Enum\Type;
use App\Exception\LanguageNotSupportedByBrokerException;
use App\Exception\Translation\MissingTranslationKeyException;
use Symfony\Component\Translation\Translator;

class TranslationService
{
    public function __construct(
        private readonly Translator $translator,
    ) {
    }

    /**
     * @param string $key
     *
     * @return string
     * @throws MissingTranslationKeyException
     */
    public function getTranslationOrError(string $key): string
    {
        if (!$this->translator->getCatalogue()->has($key)) {
            throw new MissingTranslationKeyException($key, code: 1680374627374);
        }

        return $this->translator->trans($key);
    }

    /**
     * @param Broker $broker
     * @param Type $type
     *
     * @return string
     * @throws MissingTranslationKeyException
     */
    public function translateIndicator(Broker $broker, Type $type): string
    {
        return $this->getTranslationOrError(
            sprintf('%s.%s.%s', $broker->value, TranslationCategory::INDICATORS->value, $type->value)
        );
    }

    /**
     * @param TargetDirectory $targetDirectory
     *
     * @return string
     * @throws MissingTranslationKeyException
     */
    public function translateTargetDirectory(TargetDirectory $targetDirectory): string
    {
        return $this->getTranslationOrError(
            sprintf('%s.%s', TranslationCategory::TARGET_DIRECTORIES->value, $targetDirectory->value)
        );
    }

    /**
     * @param Broker $broker
     * @param Language $language
     *
     * @return void
     * @throws LanguageNotSupportedByBrokerException
     */
    public static function checkBrokerLanguageCombination(Broker $broker, Language $language): void
    {
        if (!in_array($language, $broker->supportedLanguages())) {
            throw new LanguageNotSupportedByBrokerException($language, $broker, code: 1680439120857);
        }
    }
}
