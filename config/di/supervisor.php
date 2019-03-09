<?php

use ApiClients\Client\Supervisord\AsyncClientInterface;
use Psr\Log\LoggerInterface;
use ReactiveApps\Command\Supervisor\RestartSupervisor;
use ReactiveApps\Rx\Shutdown;

return [
    RestartSupervisor::class => \DI\factory(function (
        LoggerInterface $logger,
        Shutdown $shutdown,
        AsyncClientInterface $supervisord,
        string $name = ''
    ) {
        return new RestartSupervisor(
            $supervisord,
            $logger,
            $shutdown,
            $name
        );
    })
        ->parameter('name', \DI\get('config.supervisor.process.name')),
];
