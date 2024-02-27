<?php

namespace App\Infrastructure\Query;

use App\Domain\Query\GetInformationsFromIcalAsJsonQueryInterface;
use Psr\Log\LoggerInterface;
use Aws\Exception\AwsException;
use App\Domain\ApiClient\ApiClientInterface;

class GetInformationsFromIcalAsJsonQuery implements GetInformationsFromIcalAsJsonQueryInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ApiClientInterface $s3Client
    )
    {
    }

    public function execute(string $fileName): array
    {
        $data = $this->s3Client->get($fileName);
        if ($data === false) {
            self->logger->error('Error reading file. File is not found or not readable.');
            throw new \Exception('Error reading file');
        }
        $this->logger->info('File read successfully');

        $lines = explode("\n", $data);
        $json = [];
        $event = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }
            list($property, $value) = explode(':', $line, 2) + [null, null];
            switch ($property) {
                case 'BEGIN':
                    if($value === 'VEVENT') {
                        $event = [];
                    }
                    break;
                case 'END':
                    if($value === 'VEVENT') {
                        $json[] = $event;
                    }
                    break;
                
                case 'UID':
                    $event['id'] = $value;
                    break;

                case 'SUMMARY':
                    $event['summary'] = $value;
                    break;
                case 'DTSTART;VALUE=DATE':
                    $event['start'] = date('Y-m-d', strtotime($value));
                    break;

                case 'DTEND;VALUE=DATE':    
                    $event['end'] = date('Y-m-d', strtotime($value));
                    break;

                default:
                    break;
            }
        }

        $this->logger->info('File converted to json successfully.');

        return $json;
    }
}