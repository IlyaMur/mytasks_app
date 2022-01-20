<?php

declare(strict_types=1);

namespace Ilyamur\TaskApp\Tests\Unit\Controllers;

use Ilyamur\TaskApp\Tests\Unit\Controllers\TestDoubles\TokenControllerChild;
use PHPUnit\Framework\TestCase;
use Ilyamur\TasksApp\Gateways\UserGateway;
use Ilyamur\TasksApp\Gateways\refreshTokenGateway;
use Ilyamur\TasksApp\Services\JWTCodec;

class TokenControllerTest extends TestCase
{
    public function testCheckMethodReturnTrueIfMethodIsPost()
    {
        $controllerMock = $this->getMockBuilder(TokenControllerChild::class)
            ->setConstructorArgs([
                'POST',
                $this->createMock(UserGateway::class),
                $this->createMock(RefreshTokenGateway::class),
                $this->createMock(JWTCodec::class),
                ['email' => 'email@email.com', 'password' => '12345']
            ])->onlyMethods([])->getMock();

        $this->assertTrue($controllerMock->checkMethod());
    }

    public function testCheckMethodReturnFalseIfMethodIsNotPost()
    {
        $controllerMock = $this->getMockBuilder(TokenControllerChild::class)
            ->setConstructorArgs([
                'GET',
                $this->createMock(UserGateway::class),
                $this->createMock(RefreshTokenGateway::class),
                $this->createMock(JWTCodec::class),
                ['email' => 'email@email.com', 'password' => '12345']
            ])->onlyMethods([])->getMock();

        $this->assertFalse($controllerMock->checkMethod());
    }

    public function testValidateIfDataIsCorrect()
    {
        $controllerMock = $this->getMockBuilder(TokenControllerChild::class)
            ->setConstructorArgs([
                'POST',
                $this->createMock(UserGateway::class),
                $this->createMock(RefreshTokenGateway::class),
                $this->createMock(JWTCodec::class),
                ['email' => 'email@email.com', 'password' => '12345']
            ])->onlyMethods([])->getMock();

        $this->assertTrue($controllerMock->validateInputData());
    }

    public function testNotValidateWithoutPassword()
    {
        $controllerMock = $this->getMockBuilder(TokenControllerChild::class)
            ->setConstructorArgs([
                'POST',
                $this->createMock(UserGateway::class),
                $this->createMock(RefreshTokenGateway::class),
                $this->createMock(JWTCodec::class),
                ['email' => 'email@email.com']
            ])->onlyMethods(['respondMissingCredentials'])->getMock();
        $controllerMock->expects($this->once())->method('respondMissingCredentials');

        $this->assertFalse($controllerMock->validateInputData());
    }

    public function testCheckUserCredentialsReturnTrueIfCorrect()
    {
        $userGatewayMock = $this->getMockBuilder(UserGateway::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getByEmail'])
            ->getMock();
        $userGatewayMock->method('getByEmail')->with('email@email.com')
            ->willReturn(['password_hash' => '$2y$10$ruJLCOfdyfIQmJZUVrwOMOPlzCM5xqB54W9FuC3dR7ScPNZE0IjsW']);

        $controllerMock = $this->getMockBuilder(TokenControllerChild::class)
            ->setConstructorArgs([
                'POST',
                $userGatewayMock,
                $this->createMock(RefreshTokenGateway::class),
                $this->createMock(JWTCodec::class),
                ['email' => 'email@email.com', 'password' => '12345']
            ])->onlyMethods([])->getMock();

        $this->assertTrue($controllerMock->checkUserCredentials());
    }

    public function testCheckUserCredentialsReturnFalseIfPasswordIncorrect()
    {
        $userGatewayMock = $this->getMockBuilder(UserGateway::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getByEmail'])
            ->getMock();
        $userGatewayMock->method('getByEmail')->with('email@email.com')
            ->willReturn(['password_hash' => '$2y$10$ruJLCOfdyfIQmJZUVrwOMOPlzCM5xqB54W9FuC3dR7ScPNZE0IjsW']);

        $controllerMock = $this->getMockBuilder(TokenControllerChild::class)
            ->setConstructorArgs([
                'POST',
                $userGatewayMock,
                $this->createMock(RefreshTokenGateway::class),
                $this->createMock(JWTCodec::class),
                ['email' => 'email@email.com', 'password' => '123456']
            ])->onlyMethods(['respondInvalidAuth'])->getMock();

        $this->assertFalse($controllerMock->checkUserCredentials());
    }

    public function testCheckUserCredentialsReturnFalseIfNoUserWasFound()
    {
        $userGatewayMock = $this->getMockBuilder(UserGateway::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getByEmail'])
            ->getMock();
        $userGatewayMock->method('getByEmail')->with('email@email.com')
            ->willReturn(false);

        $controllerMock = $this->getMockBuilder(TokenControllerChild::class)
            ->setConstructorArgs([
                'POST',
                $userGatewayMock,
                $this->createMock(RefreshTokenGateway::class),
                $this->createMock(JWTCodec::class),
                ['email' => 'email@email.com', 'password' => '12345']
            ])->onlyMethods(['respondInvalidAuth'])->getMock();

        $this->assertFalse($controllerMock->checkUserCredentials());
    }

    public function testCorrectlyGenerateJWT()
    {
        $tokenMock = $this->getMockBuilder(RefreshTokenGateway::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $tokenMock->expects($this->once())->method('create')->with($this->anything());

        $codecMock = $this->getMockBuilder(JWTCodec::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['encode'])
            ->getMock();
        $codecMock->expects($this->exactly(2))->method('encode')->with($this->anything())->willReturn('42');

        $controllerMock = $this->getMockBuilder(TokenControllerChild::class)
            ->setConstructorArgs([
                'POST',
                $this->createMock(UserGateway::class),
                $tokenMock,
                $codecMock,
                ['email' => 'email@email.com', 'password' => '12345']
            ])->onlyMethods(['respondTokens'])->getMock();

        $controllerMock->expects($this->once())
            ->method('respondTokens')
            ->with([
                'accessToken' => '42',
                'refreshToken' => '42'
            ]);

        $controllerMock->user = ['id' => 1, 'username' => 'foo'];

        $controllerMock->generateJWT();
    }

    public function testProcessRequestGenerateJWTIfCheckingsAreOk()
    {
        $controllerMock = $this->getMockBuilder(TokenControllerChild::class)
            ->setConstructorArgs([
                'POST',
                $this->createMock(UserGateway::class),
                $this->createMock(RefreshTokenGateway::class),
                $this->createMock(JWTCodec::class),
                ['email' => 'email@email.com', 'password' => '12345']
            ])->onlyMethods(['checkMethod', 'validateInputData', 'checkUserCredentials', 'generateJWT'])->getMock();

        $controllerMock->expects($this->once())->method('checkMethod')->willReturn(true);
        $controllerMock->expects($this->once())->method('validateInputData')->willReturn(true);
        $controllerMock->expects($this->once())->method('checkUserCredentials')->willReturn(true);

        $controllerMock->expects($this->once())->method('generateJWT');

        $controllerMock->processRequest();
    }

    /**
     * @dataProvider renderJSONProvider
     */

    public function testCallRenderJSONCorrectly(array | string $data, string $method): void
    {
        $controllerMock = $this->getMockBuilder(TokenControllerChild::class)
            ->setConstructorArgs([
                'POST',
                $this->createMock(UserGateway::class),
                $this->createMock(RefreshTokenGateway::class),
                $this->createMock(JWTCodec::class),
                ['email' => 'email@email.com', 'password' => '12345']
            ])->onlyMethods(['renderJSON'])->getMock();

        $controllerMock->expects($this->once())->method('renderJSON')->with($data);

        $controllerMock->$method();
    }

    public function renderJSONProvider(): array
    {
        return [
            [['general' => 'No user with this data was found'], 'respondInvalidAuth'],
            [['message' => 'Token was deleted'], 'respondTokenWasDeleted'],
            [['general' => 'missing login credentials'], 'respondMissingCredentials'],
        ];
    }

    public function testRespondTokentsCorrectly(): void
    {
        $data = ['accessToken' => 123, 'refreshToken' => 456];
        $controllerMock = $this->getMockBuilder(TokenControllerChild::class)
            ->setConstructorArgs([
                'POST',
                $this->createMock(UserGateway::class),
                $this->createMock(RefreshTokenGateway::class),
                $this->createMock(JWTCodec::class),
                ['email' => 'email@email.com', 'password' => '12345']
            ])->onlyMethods(['renderJSON'])->getMock();

        $controllerMock->expects($this->once())->method('renderJSON')->with($data);

        $controllerMock->respondTokens($data);
    }

    public function testRenderJSON(): void
    {
        $controllerMock = $this->getMockBuilder(TokenControllerChild::class)
            ->setConstructorArgs([
                'POST',
                $this->createMock(UserGateway::class),
                $this->createMock(RefreshTokenGateway::class),
                $this->createMock(JWTCodec::class),
                ['email' => 'email@email.com', 'password' => '12345']
            ])->onlyMethods([])->getMock();

        $controllerMock->renderJSON("foobar");
        $this->expectOutputString('"foobar"');
    }
}
