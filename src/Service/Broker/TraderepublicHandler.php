<?php

declare(strict_types=1);

namespace App\Service\Broker;

use App\Enum\Broker;

class TraderepublicHandler extends BrokerHandler
{
    protected Broker $broker = Broker::TRADEREPUBLIC;
}
