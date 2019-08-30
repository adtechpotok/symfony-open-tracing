<?php

declare(strict_types=1);

namespace Adtechpotok\Bundle\SymfonyOpenTracing\EventListener;

use Adtechpotok\Bundle\SymfonyOpenTracing\Contract\GetSpanNameByRequest;
use Adtechpotok\Bundle\SymfonyOpenTracing\Service\OpenTracingService;
use OpenTracing\Formats;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

class HttpListener
{
    /**
     * @var OpenTracingService
     */
    protected $openTracing;

    /**
     * @var GetSpanNameByRequest
     */
    protected $nameGetter;

    /**
     * HttpListener constructor.
     *
     * @param OpenTracingService   $service
     * @param GetSpanNameByRequest $nameGetter
     */
    public function __construct(OpenTracingService $service, GetSpanNameByRequest $nameGetter)
    {
        $this->openTracing = $service;
        $this->nameGetter = $nameGetter;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event): void
    {
        $tracer = $this->openTracing->getTracer();
        $request = $event->getRequest();

        $headers = [];
        foreach ($request->headers->all() as $key => $values) {
            $headers[$key] = is_array($values) ? $values[0] : $values;
        }
        $context = $tracer->extract(Formats\HTTP_HEADERS, $headers);

        if ($context) {
            $tracer->startActiveSpan($this->nameGetter->getNameByRequest($request), ['child_of' => $context]);
        } else {
            $tracer->startActiveSpan($this->nameGetter->getNameByRequest($request));
        }

        $tracer->getActiveSpan()->setTag('http.method', $event->getRequest()->getMethod());
        $tracer->getActiveSpan()->setTag('http.url', $event->getRequest()->getUri());
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event): void
    {
        $span = $this->openTracing->getTracer()->getActiveSpan();

        if ($span) {
            $headers = [];
            $this->openTracing->getTracer()->inject($span->getContext(), Formats\HTTP_HEADERS, $headers);
            $event->getResponse()->headers->add($headers);
        }
    }

    /**
     * @param PostResponseEvent $event
     */
    public function onKernelTerminate(PostResponseEvent $event): void
    {
        $span = $this->openTracing->getTracer()->getActiveSpan();

        if ($span) {
            $span->setTag('http.status_code', $event->getResponse()->getStatusCode());

            $span->finish();
        }

        $this->openTracing->getTracer()->flush();
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        $span = $this->openTracing->getTracer()->getActiveSpan();

        if ($span) {
            if ($event->hasResponse()) {
                $headers = [];
                $this->openTracing->getTracer()->inject($span->getContext(), Formats\HTTP_HEADERS, $headers);
                $event->getResponse()->headers->add($headers);

                $span->setTag('http.status_code', $event->getResponse()->getStatusCode());
            }

            $span->log([
                'error.kind'   => 'Exception',
                'error.object' => \get_class($event->getException()),
                'message'      => $event->getException()->getMessage(),
                'stack'        => $event->getException()->getTraceAsString(),
            ]);

            $span->setTag('error', true);

            $span->finish();
        }

        $this->openTracing->getTracer()->flush();
    }
}
