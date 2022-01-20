<?php

declare(strict_types=1);

namespace Ilyamur\TaskApp\Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use Ilyamur\TasksApp\Gateways\UserGateway;
use Ilyamur\TaskApp\Tests\Unit\Controllers\TestDoubles\RefreshTokenControllerChild;

class RefreshTokenControllerTest extends TestCase
{
    public function testProcessRequestIfDataIsCorrect()
    {
        $controllerMock = $this->getMockBuilder(RefreshTokenControllerChild::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['checkMethod', 'validateInputData', 'generateJWT'])->getMock();

        $controllerMock->expects($this->once())->method('checkMethod')->willReturn(true);
        $controllerMock->expects($this->once())->method('validateInputData')->willReturn(true);

        $controllerMock->expects($this->once())->method('generateJWT');

        $controllerMock->processRequest();
    }

    public function testDoesNotProcessRequestIfDataIsInorrect()
    {
        $controllerMock = $this->getMockBuilder(RefreshTokenControllerChild::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['checkMethod', 'validateInputData', 'generateJWT'])->getMock();

        $controllerMock->expects($this->once())->method('checkMethod')->willReturn(false);
        $controllerMock->method('validateInputData')->willReturn(false);

        $controllerMock->expects($this->never())->method('generateJWT');

        $controllerMock->processRequest();
    }
}
