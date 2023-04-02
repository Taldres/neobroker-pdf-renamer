<?php

declare(strict_types=1);

namespace App;

use App\Enum\Broker;
use App\Enum\Language;
use App\Exception\CommandCancelledException;
use App\Helper\ConsoleHelper;
use App\Service\AppService;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Translation\Translator;
use Throwable;

class Application extends BaseApplication
{
    public ?ContainerInterface $container;

    /**
     * @param string $version
     * @param iterable<Command> $commands
     * @param iterable<EventSubscriberInterface> $eventSubscribers
     * @param EventDispatcher $dispatcher
     * @param AppService $appService
     * @param Translator $translator
     * @param Filesystem $filesystem
     * @param Dotenv $dotenv
     *
     * @throws FilesystemException
     */
    public function __construct(
        string $version,
        iterable $commands,
        iterable $eventSubscribers,
        private readonly EventDispatcher $dispatcher,
        private readonly AppService $appService,
        private readonly Translator $translator,
        private readonly Filesystem $filesystem,
        private readonly Dotenv $dotenv,
    ) {
        parent::__construct('Taldres Broker File Renamer', $version);

        foreach ($commands as $command) {
            $this->add($command);
        }

        foreach ($eventSubscribers as $eventSubscriber) {
            $this->dispatcher->addSubscriber($eventSubscriber);
        }

        $this->setupLanguageAndBroker();
    }

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

    /**
     * @throws FilesystemException
     */
    private function setupLanguageAndBroker(): void
    {
        if (!$this->filesystem->fileExists('.env')) {
            return;
        }

        $this->dotenv->loadEnv('.env');

        $output = new ConsoleOutput();

        if (isset($_ENV['LANGUAGE']) && !empty($_ENV['LANGUAGE'])) {
            try {
                $language = Language::from($_ENV['LANGUAGE']);

                $this->appService->setLanguage($language);
                $this->translator->setLocale($language->value);
            } catch (Throwable $throwable) {
                ConsoleHelper::writeAppNameAndVersion(
                    $output,
                    $this->appService->appName,
                    $this->appService->appVersion
                );

                $output->writeln(
                    [
                        "<error>Broker '{$_ENV['BROKER']}' not found/supported. Check your .env file!</error>",
                        "",
                    ]
                );

                ConsoleHelper::writeSupportedLanguages($output, $_ENV['LANGUAGE']);

                exit();
            }
        }

        if (isset($_ENV['BROKER']) && !empty($_ENV['BROKER'])) {
            try {
                $this->appService->setBroker(Broker::from($_ENV['BROKER']));
            } catch (Throwable $throwable) {
                ConsoleHelper::writeAppNameAndVersion(
                    $output,
                    $this->appService->appName,
                    $this->appService->appVersion
                );

                $output->writeln(
                    [
                        "<error>Broker '{$_ENV['BROKER']}' not found/supported. Check your .env file!</error>",
                        "",
                    ]
                );

                ConsoleHelper::writeSupportedBrokers($output);

                exit();
            }
        }
    }
}
