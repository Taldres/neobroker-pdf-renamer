<?php

declare(strict_types=1);

namespace App\Helper;

use App\Enum\Broker;
use App\Enum\Language;
use App\Enum\Type;
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

        $output->writeln("<comment>Supported Languages:</comment>", );

        self::writeTable($output, ['Option', 'Language'], $rows);
    }

    /**
     * Prints a table with supported brokers to the console
     *
     * @param OutputInterface $output
     *
     * @return void
     */
    public static function writeSupportedBrokers(OutputInterface $output): void
    {
        $rows = [];

        $brokers = Broker::cases();

        ksort($brokers);

        foreach (Broker::cases() as $broker) {
            $rows[] = [
                $broker->value,
                $broker->label(),
                implode(',', array_map(fn (Language $language) => $language->value, $broker->supportedLanguages())),
            ];
        }

        $output->writeln("<comment>Supported Brokers:</comment>");

        self::writeTable($output, ['Option', 'Broker', 'Supported Languages'], $rows);
    }

    /**
     * Prints the selected configuration to the console
     *
     * @param OutputInterface $output
     * @param Language $language
     * @param Broker $broker
     * @param bool $groupTypes
     * @param bool $groupCodes
     * @param bool $keepOldFiles
     *
     * @return void
     */
    public static function writeConfiguration(
        OutputInterface $output,
        Language $language,
        Broker $broker,
        bool $groupTypes,
        bool $groupCodes,
        bool $keepOldFiles,
    ): void {
        $groupTypes = $groupTypes ? "✅" : "❌";
        $groupCodes = $groupCodes ? "✅" : "❌";
        $keepOldFiles = $keepOldFiles ? "✅" : "❌";

        $output->writeln(
            [
                "<comment>Configuration:</comment>",
                "🌐 Language: {$language->value} - {$language->label()}",
                "🏢 Broker: {$broker->label()}",
                $groupTypes . " Group Types",
                $groupCodes . " Group Codes",
                $keepOldFiles . " Keep already existing files in target directory",
                "",
            ]
        );
    }

    /**
     * @param OutputInterface $output
     * @param int $countSourceFiles
     * @param int $countTargetFiles
     * @param array<string, int> $countTargetTypes
     *
     * @return void
     */
    public static function writeResult(
        OutputInterface $output,
        int $countSourceFiles,
        int $countTargetFiles,
        array $countTargetTypes
    ): void {
        $tableRows = [];

        ksort($countTargetTypes);

        foreach ($countTargetTypes as $copiedType => $count) {
            $tableRows[] = [
                Type::from($copiedType)->label(),
                $count,
            ];
        }

        $output->writeln(
            [
                "\t<info>Done! </info>",
                "",
                "🎉 From <fg=yellow;options=bold>{$countSourceFiles}</> checked source files <fg=green;options=bold>{$countTargetFiles}</> were copied to the output directory ",
            ]
        );

        $table = new Table($output);
        $table
            ->setHeaders(['Type', 'Files'])
            ->setRows($tableRows)
        ;
        $table->render();
    }

    /**
     * @param OutputInterface $output
     * @param string $appName
     * @param string $appVersion
     *
     * @return void
     */
    public static function writeAppNameAndVersion(
        OutputInterface $output,
        string $appName,
        string $appVersion
    ): void {
        $output->writeln(["🤖 <info>{$appName} (v{$appVersion})</info>", ""]);
    }
}
