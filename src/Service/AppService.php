<?php

namespace App\Service;

use App\Enum\Broker;
use App\Enum\Language;
use App\Enum\Type;
use App\Exception\LanguageNotSupportedByBrokerException;

class AppService
{
    private int $countSourceFiles = 0;
    private int $countTargetFiles = 0;

    /** @var array<string, int> */
    private array $countTargetTypes = [];

    private Language $language     = Language::DE;
    private Broker   $broker       = Broker::TRADEREPUBLIC;
    private bool     $groupTypes   = false;
    private bool     $groupCodes   = false;
    private bool     $keepOldFiles = false;

    public function __construct(
        public readonly string $appName,
        public readonly string $appVersion,
        public readonly string $projectDirectory,
        public readonly string $sourceDirectory,
        public readonly string $targetDirectory,
    ) {
    }

    /**
     * @return array<string, int>
     */
    public function getCountTargetTypes(): array
    {
        return $this->countTargetTypes;
    }

    /**
     * @param Type $type
     * @param int $count
     *
     * @return AppService
     */
    public function countTargetType(Type $type, int $count = 1): AppService
    {
        if (isset($this->countTargetTypes[$type->value])) {
            $this->countTargetTypes[$type->value] += $count;
        } else {
            $this->countTargetTypes[$type->value] = $count;
        }

        return $this;
    }

    /**
     * @param int $count
     */
    public function countSourceFile(int $count = 1): AppService
    {
        $this->countSourceFiles += $count;

        return $this;
    }

    /**
     * @return int
     */
    public function getCountSourceFiles(): int
    {
        return $this->countSourceFiles;
    }

    /**
     * @param Broker $broker
     *
     * @return AppService
     * @throws LanguageNotSupportedByBrokerException
     */
    public function setBroker(Broker $broker): AppService
    {
        TranslationService::checkBrokerLanguageCombination($broker, $this->language);

        $this->broker = $broker;

        return $this;
    }

    /**
     * @return Broker
     */
    public function getBroker(): Broker
    {
        return $this->broker;
    }

    /**
     * @param Language $language
     *
     * @return AppService
     * @throws LanguageNotSupportedByBrokerException
     */
    public function setLanguage(Language $language): AppService
    {
        TranslationService::checkBrokerLanguageCombination($this->broker, $language);

        $this->language = $language;

        return $this;
    }

    /**
     * @return Language
     */
    public function getLanguage(): Language
    {
        return $this->language;
    }

    /**
     * @param bool $groupTypes
     *
     * @return AppService
     */
    public function setGroupTypes(bool $groupTypes): AppService
    {
        $this->groupTypes = $groupTypes;

        return $this;
    }

    /**
     * @return bool
     */
    public function isGroupTypes(): bool
    {
        return $this->groupTypes;
    }

    /**
     * @param bool $groupCodes
     *
     * @return AppService
     */
    public function setGroupCodes(bool $groupCodes): AppService
    {
        $this->groupCodes = $groupCodes;

        return $this;
    }

    /**
     * @return bool
     */
    public function isGroupCodes(): bool
    {
        return $this->groupCodes;
    }

    /**
     * @param int $count
     *
     * @return AppService
     */
    public function countTargetFile(int $count = 1): AppService
    {
        $this->countTargetFiles += $count;

        return $this;
    }

    /**
     * @return int
     */
    public function getCountTargetFiles(): int
    {
        return $this->countTargetFiles;
    }

    /**
     * @param bool $keepOldFiles
     *
     * @return AppService
     */
    public function setKeepOldFiles(bool $keepOldFiles): AppService
    {
        $this->keepOldFiles = $keepOldFiles;

        return $this;
    }

    /**
         * @return bool
         */
    public function isKeepOldFiles(): bool
    {
        return $this->keepOldFiles;
    }
}
