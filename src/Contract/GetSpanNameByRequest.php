<?php

declare(strict_types=1);

namespace Adtechpotok\Bundle\SymfonyOpenTracing\Contract;

use Symfony\Component\HttpFoundation\Request;

interface GetSpanNameByRequest
{
    /**
     * @param Request $request
     *
     * @return string
     */
    public function getNameByRequest(Request $request): string;
}
