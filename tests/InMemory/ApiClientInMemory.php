<?php

namespace App\Tests\InMemory;

use App\Domain\ApiClient\ApiClientInterface;

class ApiClientInMemory implements ApiClientInterface
{
    const TEST_ICS_FILE = 'tests/InMemory/21c0ed902d012461d28605cdb2a8b7a2.ics';

    private array $data = [];

    public function send(string $jsonData, string $fileName): void
    {
        $this->data[$fileName] = $jsonData;
    }

    public function get(string $fileName): string
    {
        return file_get_contents(self::TEST_ICS_FILE);
    }
}