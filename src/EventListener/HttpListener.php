<?php

declare(strict_types=1);

namespace Adtechpotok\Bundle\SymfonyOpenTracing\EventListener;

use Adtechpotok\Bundle\SymfonyOpenTracing\Services\OpenTracingService;
use OpenTracing\Formats;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

class HttpListener
{
    /**
     * @var OpenTracingService
     */
    protected $openTracing;

    /**
     * @var string
     */
    protected $activeSpanName;

    /**
     * HttpListener constructor.
     *
     * @param OpenTracingService $service
     * @param string             $activeSpanName (default value is http_request)
     */
    public function __construct(OpenTracingService $service, string $activeSpanName = 'http_request')
    {
        $this->openTracing = $service;
        $this->activeSpanName = $activeSpanName;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event): void
    {
        $tracer = $this->openTracing->getTracer();

        $tracer->extract(Formats\HTTP_HEADERS, $event->getRequest()->request->all());

        $tracer->startActiveSpan($this->activeSpanName);
    }

    /**
     * @param PostResponseEvent $event
     */
    public function onKernelTerminate(PostResponseEvent $event): void
    {
        $span = $this->openTracing->getTracer()->getActiveSpan();

        if ($span) {
            $span->finish();
        }
    }

    /**
     * @param PostResponseEvent $event
     */
    public function onKernelException(PostResponseEvent $event): void
    {
        $span = $this->openTracing->getTracer()->getActiveSpan();

        if ($span) {
            $span->finish();
        }
    }
}
