<?php

use ApiClients\Client\Supervisord\AsyncClient;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use ReactiveApps\Command\Supervisor\RestartSupervisor;
use ReactiveApps\Rx\Shutdown;

return [
    RestartSupervisor::class => \DI\factory(function (
        LoopInterface $loop,
        LoggerInterface $logger,
        Shutdown $shutdown,
        string $host,
        string $name = ''
    ) {
        return new RestartSupervisor(
            AsyncClient::create(
                $host,
                $loop
            ),
            $logger,
            $shutdown,
            $name
        );
    })
        ->parameter('name', \DI\get('config.supervisor.process.name'))
        ->parameter('host', \DI\get('config.supervisor.process.host')),
];
