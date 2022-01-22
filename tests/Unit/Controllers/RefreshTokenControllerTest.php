<?php

declare(strict_types=1);

namespace Ilyamur\TaskApp\Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use Ilyamur\TasksApp\Services\JWTCodec;
use Ilyamur\TasksApp\Gateways\UserGateway;
use Ilyamur\TasksApp\Gateways\RefreshTokenGateway;
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

        $codecMock->method('decode')->will($this->throwException(new \Exception()));

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

    public function testJWTRespondCallRespondInvalidAuthIfNoUser()
    {
        $codecMock = $this->getMockBuilder(JWTCodec::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['decode'])->getMock();
        $codecMock->method('decode')->willReturn(['sub' => 'correct']);

        $userGatewayMock = $this->getMockBuilder(UserGateway::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getByID'])->getMock();
        $userGatewayMock->method('getByID')->willReturn(false);

        $refreshTokenGatewayMock = $this->getMockBuilder(RefreshTokenGateway::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getByToken'])->getMock();
        $refreshTokenGatewayMock->expects($this->once())->method('getByToken')->willReturn(['correctToken']);

        $controllerMock = $this->getMockBuilder(RefreshTokenControllerChild::class)
            ->setConstructorArgs([
                'POST',
                $userGatewayMock,
                $refreshTokenGatewayMock,
                $codecMock,
                ['email' => 'email@email.com', 'refreshToken' => '12345']
            ])->onlyMethods(['respondInvalidAuth'])->getMock();

        $controllerMock->expects($this->once())->method('respondInvalidAuth');

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

    public function testGenerateJWTIfDataIsCorrect()
    {
        $codecMock = $this->getMockBuilder(JWTCodec::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['decode', 'encode'])->getMock();
        $codecMock->method('decode')->willReturn(['sub' => 'correct'])->with('token12345');

        $userGatewayMock = $this->getMockBuilder(UserGateway::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getByID'])->getMock();
        $userGatewayMock->method('getByID')->willReturn(['id' => '1', 'username' => 'foo']);

        $refreshTokenGatewayMock = $this->getMockBuilder(RefreshTokenGateway::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getByToken', 'delete', 'create'])->getMock();

        $refreshTokenGatewayMock->expects($this->once())
            ->method('getByToken')->willReturn(['correctToken'])->with('token12345');
        $refreshTokenGatewayMock->expects($this->once())->method('delete')->with('token12345');
        $refreshTokenGatewayMock->expects($this->once())->method('create');

        $controllerMock = $this->getMockBuilder(RefreshTokenControllerChild::class)
            ->setConstructorArgs([
                'POST',
                $userGatewayMock,
                $refreshTokenGatewayMock,
                $codecMock,
                ['email' => 'email@email.com', 'refreshToken' => 'token12345']
            ])->onlyMethods(['respondInvalidAuth', 'respondTokens'])->getMock();

        $controllerMock->generateJWT();
    }

    public function testDeleteRefreshTokenIfDataIsCorrect()
    {
        $refreshTokenGatewayMock = $this->getMockBuilder(RefreshTokenGateway::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['delete'])->getMock();
        $refreshTokenGatewayMock->expects($this->once())
            ->method('delete')->willReturn(1)->with('token12345');

        $controllerMock = $this->getMockBuilder(RefreshTokenControllerChild::class)
            ->setConstructorArgs([
                'POST',
                $this->createMock(UserGateway::class),
                $refreshTokenGatewayMock,
                $this->createMock(JWTCodec::class),
                ['refreshToken' => 'token12345']
            ])->onlyMethods(['respondTokenWasDeleted'])->getMock();
        $controllerMock->expects($this->once())->method('respondTokenWasDeleted');

        $controllerMock->deleteRefreshToken();
    }

    public function testDoesNotDeleteRefreshTokenIfDataIsIncorrect()
    {
        $refreshTokenGatewayMock = $this->getMockBuilder(RefreshTokenGateway::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['delete'])->getMock();
        $refreshTokenGatewayMock->expects($this->never())
            ->method('delete')->willReturn(1)->with('token12345');

        $controllerMock = $this->getMockBuilder(RefreshTokenControllerChild::class)
            ->setConstructorArgs([
                'POST',
                $this->createMock(UserGateway::class),
                $refreshTokenGatewayMock,
                $this->createMock(JWTCodec::class),
                ['incorrect' => 'data']
            ])->onlyMethods(['respondInvalidToken'])->getMock();
        $controllerMock->expects($this->once())->method('respondInvalidToken');

        $controllerMock->deleteRefreshToken();
    }

    /**
     * @dataProvider dataForRespondMethods
     */

    public function testRespondMethodsWorkCorrectly(array | string $data, string $method): void
    {
        $controllerMock = $this->getMockBuilder(RefreshTokenControllerChild::class)
            ->setConstructorArgs([
                'POST',
                $this->createMock(UserGateway::class),
                $this->createMock(RefreshTokenGateway::class),
                $this->createMock(JWTCodec::class),
                ['incorrect' => 'data']
            ])->onlyMethods(['renderJSON'])->getMock();

        $controllerMock->expects($this->once())->method('renderJSON')->with($data);

        $controllerMock->$method();
    }

    public function dataForRespondMethods(): array
    {
        return [
            [['message' => 'invalid authentication'], 'respondInvalidAuth'],
            [['message' => 'invalid token'], 'respondInvalidToken'],
            [['message' => 'missing token'], 'respondMissingToken'],
            [['message' => 'invalid token (not on whitelist)'], 'respondTokenNotInWhiteList'],
        ];
    }
}
