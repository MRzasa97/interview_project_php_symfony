<?php
namespace App\tests\UI\Controller;
use PHPUnit\Framework\TestCase;
use App\UI\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Domain\Query\GetInformationsFromIcalAsJsonQueryInterface;
use App\Application\Message\Command\SendJsonDataToS3BucketCommand;
use App\Tests\InMemory\ApiClientInMemory;
use App\Domain\ApiClient\ApiClientInterface;
use App\Infrastructure\Query\GetInformationsFromIcalAsJsonQuery;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;

//Write test for controller class

class ControllerUnitTest extends TestCase
{
    const TEST_JSON_FILE = 'tests/InMemory/calendar.json';
    const TEST_REQUEST_CONTENT = '{
        "file_name": "source/21c0ed902d012461d28605cdb2a8b7a2.ics",
        "destination_file_name": "json/output_rest2.json"
    }';

    private GetInformationsFromIcalAsJsonQueryInterface $getInformationsFromIcalAsJsonQuery;
    private ApiClientInterface $apiClient;
    private LoggerInterface $logger;
    protected function setUp(): void
    {
        $this->apiClient = new ApiClientInMemory();
        $this->apiClient->send('{"key":"value"}', 'calendar.json');
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->getInformationsFromIcalAsJsonQuery = new GetInformationsFromIcalAsJsonQuery($this->logger, $this->apiClient);
    }

    public function testIndex(): void
    {
        $messageBus= $this->createMock(MessageBusInterface::class);
        $controller = new Controller($this->getInformationsFromIcalAsJsonQuery, $messageBus);
        $response = $controller->index();
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testInvoke(): void
    {
        $messageBus= $this->createMock(MessageBusInterface::class);
        $command = new SendJsonDataToS3BucketCommand('{"key":"value"}', 'calendar.json');
        $messageBus->expects($this->once())->method('dispatch')->with(
            $this->isInstanceOf(SendJsonDataToS3BucketCommand::class))
            ->willReturn(new Envelope($command));
        $controller = new Controller($this->getInformationsFromIcalAsJsonQuery, $messageBus);
        $request = $this->createMock(Request::class);
        $request->method('getContent')->willReturn(
            self::TEST_REQUEST_CONTENT
        );
        $response = $controller->processJsonData($request);
        $testFileContent = file_get_contents(self::TEST_JSON_FILE);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(json_decode($testFileContent), json_decode($response->getContent()));
    }

    public function testInvokeWithInvalidJson(): void
    {
        $messageBus= $this->createMock(MessageBusInterface::class);
        $controller = new Controller($this->getInformationsFromIcalAsJsonQuery, $messageBus);
        $request = $this->createMock(Request::class);
        $request->method('getContent')->willReturn(
            'invalid json'
        );
        $response = $controller->processJsonData($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals('{"error":"Invalid JSON"}', $response->getContent());
    }

    public function testInvokeWithNoRequiredFields(): void
    {
        $messageBus= $this->createMock(MessageBusInterface::class);
        $controller = new Controller($this->getInformationsFromIcalAsJsonQuery, $messageBus);
        $request = $this->createMock(Request::class);
        $request->method('getContent')->willReturn(
            '{"key":"value"}'
        );
        $response = $controller->processJsonData($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals('{"error":"Missing required fields in JSON"}', $response->getContent());
    }
}