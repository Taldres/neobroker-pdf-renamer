<?php

namespace App\EventSubscriber;

use App\Event\CopyTargetFileEvent;
use App\Service\AppService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TargetFileSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly AppService $appService)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CopyTargetFileEvent::class => 'onCopyTargetFile',
        ];
    }

    public function onCopyTargetFile(CopyTargetFileEvent $event): void
    {
        $this->appService->countTargetFile();
        $this->appService->countTargetType($event->targetFile->type);
    }
}
