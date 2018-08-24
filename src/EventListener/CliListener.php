<?php

declare(strict_types=1);

namespace Adtechpotok\Bundle\SymfonyOpenTracing\EventListener;

use Adtechpotok\Bundle\SymfonyOpenTracing\Contract\GetSpanNameByCommand;
use Adtechpotok\Bundle\SymfonyOpenTracing\Service\OpenTracingService;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;

class CliListener
{
    /**
     * @var OpenTracingService
     */
    protected $openTracing;

    /**
     * @var GetSpanNameByCommand
     */
    protected $nameGetter;

    /**
     * HttpListener constructor.
     *
     * @param OpenTracingService   $service
     * @param GetSpanNameByCommand $nameGetter
     */
    public function __construct(OpenTracingService $service, GetSpanNameByCommand $nameGetter)
    {
        $this->openTracing = $service;
        $this->nameGetter = $nameGetter;
    }

    /**
     * @param ConsoleCommandEvent $event
     */
    public function onConsoleCommand(ConsoleCommandEvent $event): void
    {
        $tracer = $this->openTracing->getTracer();

        $name = $this->nameGetter->getNameByCommand($event->getCommand(), $event->getInput());

        $scope = $tracer->startActiveSpan($name);

        foreach ($event->getInput()->getOptions() as $key => $value) {
            $scope->getSpan()->setTag('cli.options.' . $key, $value);
        }
    }

    /**
     * @param ConsoleTerminateEvent $event
     */
    public function onConsoleTerminate(ConsoleTerminateEvent $event): void
    {
        $span = $this->openTracing->getTracer()->getActiveSpan();

        if ($span) {
            $span->finish();
        }

        $this->openTracing->getTracer()->flush();
    }

    /**
     * @param ConsoleErrorEvent $event
     */
    public function onConsoleError(ConsoleErrorEvent $event): void
    {
        $span = $this->openTracing->getTracer()->getActiveSpan();

        if ($span) {
            $span->log([
                'error.kind'   => 'Error',
                'error.object' => \get_class($event->getError()),
                'message'      => $event->getError()->getMessage(),
                'stack'        => $event->getError()->getTraceAsString(),
            ]);
            $span->setTag('error', true);
        }
    }
}
