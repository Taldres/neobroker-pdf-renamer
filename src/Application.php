<?php

declare(strict_types=1);

namespace App;

use App\Exception\CommandCancelledException;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Throwable;

class Application extends BaseApplication
{
    public ?ContainerInterface $container;

    /**
     * @param iterable<Command> $commands
     * @param string $version
     */
    public function __construct(iterable $commands, string $version)
    {
        parent::__construct('Taldres Broker File Renamer', $version);

        foreach ($commands as $command) {
            $this->add($command);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function renderThrowable(Throwable $e, OutputInterface $output): void
    {
        if ($e instanceof CommandCancelledException) {
            return;
        }

        parent::renderThrowable($e, $output);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultInputDefinition(): InputDefinition
    {
        return parent::getDefaultInputDefinition();
    }
}
