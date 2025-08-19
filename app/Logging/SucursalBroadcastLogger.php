<?php

namespace App\Logging;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class SucursalBroadcastLogger
{
    public function __invoke($logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new LineFormatter(
                "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
                'Y-m-d H:i:s',
                true,
                true
            ));
            
            // Registrar en un archivo específico para broadcast
            $handler->setHandler(new StreamHandler(
                storage_path('logs/broadcast.log'),
                Logger::DEBUG
            ));
        }
    }
}