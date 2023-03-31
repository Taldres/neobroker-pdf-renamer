<?php

declare(strict_types=1);

namespace App\Helper;

use App\Enum\Broker;
use App\Enum\Language;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleHelper
{
    /**
     * @param OutputInterface $output
     * @param array<int, mixed> $headers
     * @param array<int, mixed> $rows
     *
     * @return void
     */
    public static function writeTable(OutputInterface $output, array $headers, array $rows): void
    {
        $table = new Table($output);
        $table->setHeaders($headers)
              ->setRows($rows)
        ;
        $table->render();
    }

    /**
     * Prints a table with supported languages to the console
     *
     * @param OutputInterface $output
     * @param string $inputLanguage
     *
     * @return void
     */
    public static function writeSupportedLanguages(OutputInterface $output, string $inputLanguage): void
    {
        $rows = [];

        $languages = Language::cases();

        ksort($languages);

        foreach (Language::cases() as $language) {
            $rows[] = [
                $language->value,
                $language->label(),
            ];
        }

        $output->writeln(
            [
                "<error>Language '{$inputLanguage}' not found/supported.</error>",
                "",
                "<comment>Supported Languages:</comment>",
            ]
        );

        self::writeTable($output, ['Option', 'Language'], $rows);
    }

    /**
     * Prints a table with supported brokers to the console
     *
     * @param OutputInterface $output
     * @param string $inputBroker
     *
     * @return void
     */
    public static function writeSupportedBrokers(OutputInterface $output, string $inputBroker): void
    {
        $rows = [];

        $brokers = Broker::cases();

        ksort($brokers);

        foreach (Broker::cases() as $broker) {
            $rows[] = [
                $broker->value,
                $broker->label(),
            ];
        }

        $output->writeln(
            [
                "<error>Broker '{$inputBroker}' not found/supported.</error>",
                "",
                "<comment>Supported Brokers:</comment>",
            ]
        );

        self::writeTable($output, ['Option', 'Broker'], $rows);
    }

    /**
     * Prints the selected configuration to the console
     *
     * @param OutputInterface $output
     * @param Language $language
     * @param Broker $broker
     * @param bool $groupTypes
     * @param bool $groupCodes
     *
     * @return void
     */
    public static function writeConfiguration(
        OutputInterface $output,
        Language $language,
        Broker $broker,
        bool $groupTypes,
        bool $groupCodes
    ): void {
        $groupTypes = $groupTypes ? "✅" : "❌";
        $groupCodes = $groupCodes ? "✅" : "❌";

        $output->writeln(
            [
                "<comment>Configuration:</comment>",
                "🌐 Language: {$language->value} - {$language->label()}",
                "🏢 Broker: {$broker->label()}",
                $groupTypes . " Group Types",
                $groupCodes . " Group Codes",
                ""
            ]
        );
    }
}
