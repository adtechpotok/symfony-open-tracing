<?php

declare(strict_types=1);

namespace Adtechpotok\Bundle\SymfonyOpenTracing\Contract;

use Symfony\Component\Console\Command\Command;

interface GetSpanNameByCommand
{
    /**
     * @param Command $command
     *
     * @return string
     */
    public function getNameByCommand(Command $command): string;
}
