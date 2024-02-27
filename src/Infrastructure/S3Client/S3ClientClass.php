<?php

namespace App\Infrastructure\S3Client;

use App\Domain\ApiClient\ApiClientInterface;
use Aws\S3\S3Client;

class S3ClientClass implements ApiClientInterface
{
    private S3Client $client;
    
    public function __construct(
        public readonly string $accessKey,
        public readonly string $secretKey,
        public readonly string $region,
        public readonly string $bucketName,
    )
    {
        $this->client = new S3Client([
            'version' => 'latest',
            'region' => $region,
            'credentials' => [
                'key' => $accessKey,
                'secret' => $secretKey,
            ],
        ]);
    }

    public function send(string $jsonData, string $fileName): void
    {
        try {
            $result = $this->client->putObject([
                'Bucket' => $this->bucketName,
                'Key' => $fileName,
                'Body' => $jsonData,
                'ContentType' => 'application/json',
            ]);
        } catch (AwsException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function get(string $fileName): string
    {
        try {
            $result = $this->client->getObject([
                'Bucket' => $this->bucketName,
                'Key' => $fileName,
            ]);
            return $result['Body'];
        } catch (AwsException $e) {
            throw new \Exception($e->getMessage());
        }
    }
}