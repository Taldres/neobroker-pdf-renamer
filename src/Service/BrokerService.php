<?php

namespace App\Service;

use App\Enum\Broker;
use App\Service\Broker\BrokerHandler;
use App\Service\Broker\TraderepublicHandler;

class BrokerService
{
    public function __construct(
        private readonly AppService $appService,
        private readonly TraderepublicHandler $traderepublicHandler
    ) {
    }

    public function getHandler(?Broker $broker = null): BrokerHandler
    {
        $broker ??= $this->appService->getBroker();

        return match ($broker) {
            Broker::TRADEREPUBLIC => $this->traderepublicHandler,
        };
    }
}
