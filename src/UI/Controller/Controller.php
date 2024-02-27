<?php

namespace App\UI\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Domain\Query\GetInformationsFromIcalAsJsonQueryInterface;
use App\Application\Message\Command\SendJsonDataToS3BucketCommand;


#[Route('/', name: 'api_')]
class Controller
{
    public function __construct(
        public readonly GetInformationsFromIcalAsJsonQueryInterface $getInformationsFromIcalAsJsonQuery,
        public readonly MessageBusInterface $messageBus
    )
    {
    }

    #[Route('/', name: 'project_index', methods:['get'])]
    public function index(): Response
    {
        return new JsonResponse(['status' => 'ok']);
    }

    /* Receives Json with source file_name and destination_file_name */
    #[Route('/api/invoke', name: 'project_ivoke', methods:['POST'] )]
    public function processJsonData(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        if (!isset($data['file_name'], $data['destination_file_name'])) {
            return new JsonResponse(['error' => 'Missing required fields in JSON'], Response::HTTP_BAD_REQUEST);
        }

        $sourceFileName = $data['file_name'];
        $destinationFileName = $data['destination_file_name'];

        $json = $this->getInformationsFromIcalAsJsonQuery->execute($sourceFileName);
        $this->messageBus->dispatch(new SendJsonDataToS3BucketCommand(json_encode($json, JSON_PRETTY_PRINT), $destinationFileName));

        return new JsonResponse($json);
    }
}