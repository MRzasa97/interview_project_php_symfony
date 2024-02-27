<?php

namespace App\Application\MessageHandler;

use App\Application\Message\Command\SendJsonDataToS3BucketCommand;
use App\Application\Message\ExternalMessage;
use App\Domain\ApiClient\ApiClientInterface;
use Aws\Exception\AwsException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendJsonDataToS3BucketMessageHandler
{
    public function __construct(
        private readonly ApiClientInterface $s3Client,
        private readonly LoggerInterface $logger
    )
    {
    }

    public function __invoke(SendJsonDataToS3BucketCommand $command): void
    {
        $this->logger->info('Sending json data to S3 bucket...');
        try {
            $this->s3Client->send($command->getJsonData(), $command->getFileName());
            $this->logger->info('Data uploaded to S3 bucket');
        } catch (AwsException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}