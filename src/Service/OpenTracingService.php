<?php

declare(strict_types=1);

namespace Adtechpotok\Bundle\SymfonyOpenTracing\Service;

use Jaeger\Factory;
use OpenTracing\Tracer;

class OpenTracingService
{
    /**
     * @var Tracer
     */
    protected $tracer;

    /**
     * @param string $appName
     * @param string $host
     * @param int    $port
     * @param bool   $isDisabled = false
     */
    public function __construct(string $appName, string $host, int $port, bool $isDisabled = false)
    {
        $factory = Factory::getInstance();

        $factory->setDisabled($isDisabled);

        $tracer = $factory->initTracer($appName, $host, $port);

        $this->tracer = $tracer;
    }

    /**
     * @return Tracer
     */
    public function getTracer(): Tracer
    {
        return $this->tracer;
    }
}
