<?php

declare(strict_types=1);

namespace Ilyamur\TaskApp\Tests\Unit\Controllers;

use Ilyamur\TaskApp\Tests\Unit\Controllers\TestDoubles\TaskControllerChild;
use Ilyamur\TasksApp\Controllers\TaskController;
use Ilyamur\TasksApp\Gateways\TaskGateway;
use PHPUnit\Framework\TestCase;

class TaskControllerTest extends TestCase
{
    /**
     * @dataProvider taskIdProvider
     */

    public function testProcessDependsOnId(?string $taskId, string $method): void
    {
        $controllerMock = $this->getMockBuilder(TaskControllerChild::class)
            ->setConstructorArgs([$this->createMock(TaskGateway::class), '1', 'GET', $taskId])
            ->onlyMethods([$method])
            ->getMock();

        $controllerMock->expects($this->once())->method($method);
        $controllerMock->processRequest();
    }

    public function taskIdProvider(): array
    {
        return [
            'request to specific resource' => [null, 'requestToResource'],
            'request to specific id' => ['1', 'requestToSingleEntity']
        ];
    }

    public function testRequestToSingleEntityReturnsIfNoTaskWasFound(): void
    {
        $gatewayMock = $this->getMockBuilder(TaskGateway::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getForUser'])
            ->getMock();
        $gatewayMock->method('getForUser')->with('100', '1')->willReturn(false);

        $controllerMock = $this->getMockBuilder(TaskControllerChild::class)
            ->setConstructorArgs([$gatewayMock, '1', 'GET', '100'])
            ->onlyMethods(['respondNotFound'])
            ->getMock();
        $controllerMock->expects($this->once())->method('respondNotFound');

        $this->assertNull($controllerMock->requestToSingleEntity());
    }

    public function testRequestToSingleEntityRenderTaskIfMethodIsGet(): void
    {
        $task = ['title' => 42, 'body' => 'foo'];

        $gatewayMock = $this->getMockBuilder(TaskGateway::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getForUser'])
            ->getMock();
        $gatewayMock->method('getForUser')->willReturn($task);

        $controllerMock = $this->getMockBuilder(TaskControllerChild::class)
            ->setConstructorArgs([$gatewayMock, '1', 'GET', '100'])
            ->onlyMethods(['renderJSON'])
            ->getMock();
        $controllerMock->expects($this->once())->method('renderJSON')->with($task);

        $controllerMock->requestToSingleEntity();
    }

    /**
     * @dataProvider methodForSingleEntityProvider
     */

    public function testRequestToSingleEntityCallCorrectMethod(string $verb, string $method): void
    {
        $gatewayMock = $this->getMockBuilder(TaskGateway::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getForUser'])
            ->getMock();
        $gatewayMock->method('getForUser')->willReturn(['title' => 42, 'body' => 'foo']);

        $controllerMock = $this->getMockBuilder(TaskControllerChild::class)
            ->setConstructorArgs([$gatewayMock, '1', $verb, '100'])
            ->onlyMethods([$method])
            ->getMock();
        $controllerMock->expects($this->once())->method($method);

        $controllerMock->requestToSingleEntity();
    }

    public function methodForSingleEntityProvider(): array
    {
        return [
            'DELETE request' => ['DELETE', 'processDeleteRequest'],
            'PATCH request' => ['PATCH', 'processUpdateRequest'],
            'Not allowed method' => ['POST', 'respondMethodNotAllowed'],
        ];
    }

    public function testRequestToResourceRenderAllTasksWhenMethodIsGet(): void
    {
        $gatewayMock = $this->getMockBuilder(TaskGateway::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAllForUser'])
            ->getMock();
        $gatewayMock->expects($this->once())->method('getAllForUser')->with(1);

        $controllerMock = $this->getMockBuilder(TaskControllerChild::class)
            ->setConstructorArgs([$gatewayMock, '1', 'GET', '100'])
            ->onlyMethods(['renderJSON'])
            ->getMock();

        $controllerMock->expects($this->once())->method('renderJSON');

        $controllerMock->requestToResource();
    }

    /**
     * @dataProvider methodForResourceProvider
     */

    public function testRequestToResourceCallCorrectMethod(string $verb, string $method): void
    {
        $controllerMock = $this->getMockBuilder(TaskControllerChild::class)
            ->setConstructorArgs([$this->createMock(TaskGateway::class), '1', $verb, '100'])
            ->onlyMethods([$method])
            ->getMock();

        $controllerMock->expects($this->once())->method($method);

        $controllerMock->requestToResource();
    }


    public function methodForResourceProvider(): array
    {
        return [
            'POST request' => ['POST', 'processCreateRequest'],
            'Not allowed method' => ['DELETE', 'respondMethodNotAllowed'],
        ];
    }

    /**
     * @dataProvider userDataForUpdateProvider
     */

    public function testProcessUpdateRequest(array $userData, int $callCount): void
    {
        $gatewayMock = $this->getMockBuilder(TaskGateway::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['updateForUser'])
            ->getMock();

        $gatewayMock->expects($this->exactly($callCount))
            ->method('updateForUser');

        $controllerMock = $this->getMockBuilder(TaskControllerChild::class)
            ->setConstructorArgs([$gatewayMock, '1', 'GET', '100'])
            ->onlyMethods(['getFromRequestBody', 'renderJSON'])
            ->getMock();

        $controllerMock->expects($this->once())
            ->method('getFromRequestBody')
            ->willReturn($userData);

        $controllerMock->processUpdateRequest();
    }

    public function userDataForUpdateProvider(): array
    {
        return [
            [['title' => 'baz', 'body' => 'foo'],  1],
            [['title' => 'baz', 'body' => 42],  1],
            [['title' => 'baz'],  0],
            [['body' => 'foo'],  0],
            [[],  0]
        ];
    }

    public function testProcessDeleteRequestCorrectly(): void
    {
        $gatewayMock = $this->getMockBuilder(TaskGateway::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['deleteForUser'])
            ->getMock();

        $gatewayMock->expects($this->once())
            ->method('deleteForUser')->with('100', '1')->willReturn(7);

        $controllerMock = $this->getMockBuilder(TaskControllerChild::class)
            ->setConstructorArgs([$gatewayMock, '1', 'GET', '100'])
            ->onlyMethods(['renderJSON'])
            ->getMock();

        $controllerMock->expects($this->once())
            ->method('renderJSON')->with(['message' => 'Task deleted', 'rows' => 7]);

        $controllerMock->processDeleteRequest();
    }

    public function testProcessCreateRequestCorrectly(): void
    {
        $data = ['title' => 'baz', 'body' => 'foo'];

        $gatewayMock = $this->getMockBuilder(TaskGateway::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createForUser'])
            ->getMock();

        $gatewayMock->expects($this->once())
            ->method('createForUser')->with($data, '1')->willReturn('10');

        $controllerMock = $this->getMockBuilder(TaskControllerChild::class)
            ->setConstructorArgs([$gatewayMock, '1', 'GET', '100'])
            ->onlyMethods(['getFromRequestBody', 'respondCreated'])
            ->getMock();

        $controllerMock->expects($this->once())
            ->method('getFromRequestBody')
            ->willReturn($data);

        $controllerMock->expects($this->once())
            ->method('respondCreated');

        $controllerMock->processCreateRequest();
    }

    public function testDoesNotProcessCreateRequestIfDataIsIncorrect(): void
    {
        $gatewayMock = $this->getMockBuilder(TaskGateway::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createForUser'])
            ->getMock();

        $gatewayMock->expects($this->never())
            ->method('createForUser');

        $controllerMock = $this->getMockBuilder(TaskControllerChild::class)
            ->setConstructorArgs([$gatewayMock, '1', 'GET', '100'])
            ->onlyMethods(['getFromRequestBody', 'renderJSON'])
            ->getMock();

        $controllerMock->expects($this->once())
            ->method('getFromRequestBody')
            ->willReturn(['title' => 'baz']);

        $controllerMock->processCreateRequest();
    }

    /**
     * @dataProvider dataForGetValidationErrorsProvider
     */

    public function testGetCorrectValidationErrrors(array $data, array $expectedErrors): void
    {
        $controllerMock = $this->getMockBuilder(TaskControllerChild::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->assertEquals($expectedErrors, $controllerMock->getValidationErrors($data));
    }

    public function dataForGetValidationErrorsProvider(): array
    {
        return [
            [['title' => '42', 'body' => 'foo'], []],
            [['title' => '42'], ['body' => 'body is required']],
            [['body' => 'foo'], ['title' => 'title is required']],
            [[], ['title' => 'title is required', 'body' => 'body is required']],
            [['body' => 'foo', 'title' => '42', 'priority' => 100], []],
            [['body' => 'foo', 'title' => '42', 'priority' => 'buz'], ['priority' => 'priority must be an integer']]
        ];
    }

    public function testCorrectRespondNotFound(): void
    {
        $controllerMock = $this->getMockBuilder(TaskControllerChild::class)
            ->setConstructorArgs([$this->createMock(TaskGateway::class), '1', 'GET', '100'])
            ->onlyMethods(['renderJSON'])
            ->getMock();

        $controllerMock->expects($this->once())
            ->method('renderJSON')->with(['message' => "Task with ID 100 not found"]);

        $controllerMock->respondNotFound();
    }

    public function testCorrectRespondCreated(): void
    {
        $controllerMock = $this->getMockBuilder(TaskControllerChild::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['renderJSON'])
            ->getMock();

        $controllerMock->expects($this->once())
            ->method('renderJSON')->with(["message" => "Task created", "id" => '111']);

        $controllerMock->respondCreated('111');
    }
}
