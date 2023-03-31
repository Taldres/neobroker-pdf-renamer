<?php

declare(strict_types=1);

namespace App\Command;

use App\Enum\Language;
use App\Service\Translation;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

#[AsCommand(name: 'translation:validate')]
class ValidateTranslationCommand extends Command
{
    private Translation $translator;

    public function __construct(string $name = "translation:validate")
    {
        parent::__construct($name);
    }

    /**
     * Configures the command
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->addOption(
            'lang',
            'l',
            InputOption::VALUE_REQUIRED,
            'The language wich should be validated. (two letters, like: de)'
        );
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
            try {
                $lang = Language::from(strval($input->getOption('lang')));
            } catch (Throwable $throwable) {
                $output->writeln(
                    array_merge(
                        [
                            "<error>Language '" . strval($input->getOption('lang')) . "' not found/supported.</error>",
                            "<comment>Supported Languages:</comment>",
                        ],
                        array_column(Language::cases(), 'value')
                    )
                );

                return Command::INVALID;
            }

            $this->translator = new Translation();
            $this->translator->validateTranslationsFile($lang);
        } catch (Throwable $throwable) {
            $output->writeln(
                "<error>{$throwable->getMessage()}</error>"
            );

            return Command::FAILURE;
        }

        $output->writeln(
            "Successfully validated!"
        );

        return Command::SUCCESS;
    }
}
