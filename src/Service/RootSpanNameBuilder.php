<?php

declare(strict_types=1);

namespace Adtechpotok\Bundle\SymfonyOpenTracing\Service;

use Adtechpotok\Bundle\SymfonyOpenTracing\Contract\GetSpanNameByCommand;
use Adtechpotok\Bundle\SymfonyOpenTracing\Contract\GetSpanNameByRequest;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\HttpFoundation\Request;

class RootSpanNameBuilder implements GetSpanNameByRequest, GetSpanNameByCommand
{
    protected const ROUTE_NOT_FOUND = 'route_not_found';

    /**
     * @var string
     */
    protected $httpNamePrefix;

    /**
     * @var string
     */
    protected $cliNamePrefix;

    /**
     * RootSpanNameBuilder constructor.
     *
     * @param string $httpNamePrefix
     * @param string $cliNamePrefix
     */
    public function __construct(string $httpNamePrefix = 'http-tracing', string $cliNamePrefix = 'cli-tracing')
    {
        $this->httpNamePrefix = $httpNamePrefix;
        $this->cliNamePrefix = $cliNamePrefix;
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    public function getNameByRequest(Request $request): string
    {
        return sprintf('%s.%s', $this->httpNamePrefix, $request->attributes->get('_route', self::ROUTE_NOT_FOUND));
    }

    /**
     * @param Command $command
     * @param InputInterface $input
     *
     * @return string
     */
    public function getNameByCommand(Command $command, InputInterface $input): string
    {
        $arguments = '';

        foreach ($command->getDefinition()->getArguments() as $argument) {
            if ($argument->getName() === 'command') {
                continue;
            }
            $arguments .= ' ' . $input->getArgument($argument->getName());
        }

        if ($arguments) {
            return sprintf('%s.%s %s', $this->cliNamePrefix, $command->getName(), $arguments);
        } else {
            return sprintf('%s.%s', $this->cliNamePrefix, $command->getName());
        }
    }
}
