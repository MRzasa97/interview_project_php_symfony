<?php

namespace App\Domain\Query;

interface GetInformationsFromIcalAsJsonQueryInterface
{
    public function execute(string $url): array;
}