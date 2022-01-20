<?php

declare(strict_types=1);

namespace Ilyamur\TaskApp\Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use Ilyamur\TasksApp\Services\JWTCodec;
use Ilyamur\TasksApp\Gateways\UserGateway;
use Ilyamur\TasksApp\Gateways\RefreshTokenGateway;
use Ilyamur\TaskApp\Tests\Unit\Controllers\TestDoubles\TokenControllerChild;
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

    public function testDoesNotValidateInputDataIfItIsIncorrect()
    {
        $controllerMock = $this->getMockBuilder(RefreshTokenControllerChild::class)
            ->setConstructorArgs([
                'POST',
                $this->createMock(UserGateway::class),
                $this->createMock(RefreshTokenGateway::class),
                $this->createMock(JWTCodec::class),
                ['email' => 'email@email.com', 'password' => '12345']
            ])
            ->onlyMethods(['respondMissingToken'])->getMock();

        $controllerMock->expects($this->once())->method('respondMissingToken');

        $this->assertFalse($controllerMock->validateInputData());
    }

    public function testValidateInputDataIfItIncludesKey()
    {
        $controllerMock = $this->getMockBuilder(RefreshTokenControllerChild::class)
            ->setConstructorArgs([
                'POST',
                $this->createMock(UserGateway::class),
                $this->createMock(RefreshTokenGateway::class),
                $this->createMock(JWTCodec::class),
                ['email' => 'email@email.com', 'refreshToken' => '12345']
            ])->onlyMethods(['respondMissingToken'])->getMock();

        $controllerMock->expects($this->never())->method('respondMissingToken');

        $this->assertTrue($controllerMock->validateInputData());
    }

    public function testJWTRespondCallInvalidToken()
    {
        $codecMock = $this->getMockBuilder(JWTCodec::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['decode'])->getMock();

        $codecMock->method('decode')->will($this->throwException(new \Exception));

        $controllerMock = $this->getMockBuilder(RefreshTokenControllerChild::class)
            ->setConstructorArgs([
                'POST',
                $this->createMock(UserGateway::class),
                $this->createMock(RefreshTokenGateway::class),
                $codecMock,
                ['email' => 'email@email.com', 'password' => '12345']
            ])->onlyMethods(['respondInvalidToken'])->getMock();

        $controllerMock->expects($this->once())->method('respondInvalidToken');

        $controllerMock->generateJWT();
    }

    public function testJWTRespondCallNotInWhitelist()
    {
        $codecMock = $this->getMockBuilder(JWTCodec::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['decode'])->getMock();
        $codecMock->method('decode')->willReturn(['42']);

        $refreshTokenGatewayMock = $this->getMockBuilder(RefreshTokenGateway::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getByToken'])->getMock();

        $refreshTokenGatewayMock->expects($this->once())->method('getByToken')->willReturn(false);

        $controllerMock = $this->getMockBuilder(RefreshTokenControllerChild::class)
            ->setConstructorArgs([
                'POST',
                $this->createMock(UserGateway::class),
                $refreshTokenGatewayMock,
                $codecMock,
                ['email' => 'email@email.com', 'refreshToken' => '12345']
            ])->onlyMethods(['respondTokenNotInWhiteList'])->getMock();

        $controllerMock->expects($this->once())->method('respondTokenNotInWhiteList');

        $controllerMock->generateJWT();
    }
}
