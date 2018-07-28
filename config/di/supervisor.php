<?php

use ApiClients\Client\Supervisord\AsyncClientInterface;
use Psr\Log\LoggerInterface;
use ReactiveApps\Command\Supervisor\RestartSupervisor;
use ReactiveApps\Rx\Shutdown;

return [
    RestartSupervisor::class => \DI\factory(function (AsyncClientInterface $supervisor, LoggerInterface $logger, Shutdown $shutdown, string $name) {
        return new RestartSupervisor($supervisor, $logger, $shutdown, $name);
    })
    ->parameter('name', \DI\get('config.supervisor.process.name')),
];
