<?php

namespace App\Commands;

use App\Classes\Translator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

#[AsCommand(name: 'translation:validate')]
class ValidateTranslationCommand extends Command
{
    private Translator $translator;

    public function __construct(string $name = null)
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
            $lang = strtolower(substr(strval($input->getOption('lang')), 0, 2));

            $this->translator = new Translator();
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
