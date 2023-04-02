<?php

namespace App\EventSubscriber;

use App\Event\HandleSourceFileEvent;
use App\Service\AppService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SourceFileSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly AppService $appService)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            HandleSourceFileEvent::class => 'onHandleSourceFile',
        ];
    }

    public function onHandleSourceFile(HandleSourceFileEvent $event): void
    {
        $this->appService->countSourceFile();
    }
}
