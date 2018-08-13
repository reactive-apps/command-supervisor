<?php declare(strict_types=1);

namespace ReactiveApps\Command\Supervisor;

use ApiClients\Client\Supervisord\AsyncClientInterface;
use ApiClients\Client\Supervisord\Resource\Async\Program;
use ApiClients\Client\Supervisord\Resource\ProgramInterface;
use Cake\Chronos\Chronos;
use Psr\Log\LoggerInterface;
use ReactiveApps\Command\Command;
use ReactiveApps\Rx\Shutdown;
use Rx\React\Promise;

final class RestartSupervisor implements Command
{
    const COMMAND = 'restart-supervisor';

    /**
     * @var AsyncClientInterface
     */
    private $supervisor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Shutdown
     */
    private $shutdown;

    /**
     * @var string
     */
    private $name;

    /**
     * @param AsyncClientInterface $supervisor
     * @param LoggerInterface $logger
     * @param Shutdown $shutdown
     * @param string $name
     */
    public function __construct(AsyncClientInterface $supervisor, LoggerInterface $logger, Shutdown $shutdown, string $name)
    {
        $this->supervisor = $supervisor;
        $this->logger = $logger;
        $this->shutdown = $shutdown;
        $this->name = $name;
    }

    public function __invoke()
    {
        if ($this->name === '') {
            yield from $this->restartSupervisor();
        }

        yield from $this->restartProgram();

        $this->shutdown->onCompleted();
    }

    private function restartSupervisor()
    {
        $this->logger->info('Restarting supervisor');

        yield $this->supervisor->restart();

        $this->logger->info('Supervisor restarted');
    }

    private function restartProgram()
    {

        /** @var Program $program */
        $program = yield Promise::fromObservable(
            $this->supervisor->programs()->filter(function (ProgramInterface $program) {
                return $program->name() === $this->name;
            })->take(1)
        );

        $this->logger->info('"' . $program->name() . '" has been up and running for ' . $this->formatUptime($program) . ', restarting');

        $program = yield $program->restart();

        $this->logger->info('Restarted "' . $program->name() . '" up and running for ' . $this->formatUptime($program));
    }

    private function formatUptime(ProgramInterface $program): string
    {
        $seconds = $program->now() - $program->start();
        return Chronos::create()->subSeconds($seconds)->diffForHumans(null, true);
    }
}
