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

    public function testRequestToSingleEntityReturnsIfNoTaskWasFound()
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

    public function testRequestToSingleEntityRenderTaskIfMethodIsGet()
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
}
