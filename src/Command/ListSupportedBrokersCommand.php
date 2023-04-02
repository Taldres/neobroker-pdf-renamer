<?php

declare(strict_types=1);

namespace App\Command;

use App\Helper\ConsoleHelper;
use App\Service\AppService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'list:brokers')]
class ListSupportedBrokersCommand extends Command
{
    public function __construct(
        private readonly AppService $appService,
    ) {
        parent::__construct("list:brokers");
    }

    /**
     * Configures the command
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setDescription('List supported brokers');
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
        ConsoleHelper::writeAppNameAndVersion($output, $this->appService->appName, $this->appService->appVersion);
        ConsoleHelper::writeSupportedBrokers($output);

        return Command::SUCCESS;
    }
}
