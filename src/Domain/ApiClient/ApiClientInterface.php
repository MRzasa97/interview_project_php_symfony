<?php

namespace App\Domain\ApiClient;

interface ApiClientInterface
{
    public function send(string $jsonData, string $fileName): void;
    public function get(string $fileName): string;
} 