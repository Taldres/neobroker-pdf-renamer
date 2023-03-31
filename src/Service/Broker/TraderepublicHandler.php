<?php

declare(strict_types=1);

namespace App\Service\Broker;

use App\Enum\Broker;
use App\Service\FileHandler;
use App\Service\Translation;

class TraderepublicHandler extends BrokerHandler
{
    protected Broker $broker = Broker::TRADEREPUBLIC;

    /**
     * @inheritDoc
     */
    public function __construct(
        private readonly Translation $translation,
        private readonly FileHandler $fileHandler
    ) {
        parent::__construct($this->translation, $this->fileHandler);
    }
}
