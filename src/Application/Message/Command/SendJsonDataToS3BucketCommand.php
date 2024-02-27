<?php

namespace App\Application\Message\Command;

use App\Application\Message\ExternalMessage;

class SendJsonDataToS3BucketCommand implements ExternalMessage
{
    public function __construct(
        private string $jsonData,
        private string $fileName
    ) {
    }

    public function getJsonData(): string
    {
        return $this->jsonData;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }
}  