<?php

declare(strict_types=1);

namespace Adtechpotok\Bundle\SymfonyOpenTracing\Contract;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

interface GetSpanNameByCommand
{
    /**
     * @param Command $command
     *
     * @return string
     */
    public function getNameByCommand(Command $command, InputInterface $input): string;
}
