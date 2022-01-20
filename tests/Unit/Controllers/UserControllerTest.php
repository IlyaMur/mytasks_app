<?php

declare(strict_types=1);

namespace Ilyamur\TaskApp\Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use Ilyamur\TasksApp\Services\JWTCodec;
use Ilyamur\TasksApp\Gateways\UserGateway;
use Ilyamur\TasksApp\Gateways\RefreshTokenGateway;
use Ilyamur\TaskApp\Tests\Unit\Controllers\TestDoubles\UserControllerChild;

class UserControllerTest extends TestCase
{
    public function testProcessRequestCallRespondJWTIfInputDataIsCorrect(): void
    {
        $controllerMock = $this->getMockBuilder(UserControllerChild::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['checkMethod', 'validateInputData', 'respondJWT'])
            ->getMock();

        $controllerMock->method('checkMethod')->willReturn(true);
        $controllerMock->method('validateInputData')->willReturn(true);

        $controllerMock->expects($this->once())->method('respondJWT');

        $controllerMock->processRequest();
    }

    public function testValidateInputDataReturnsTrueIfDataIsCorrect(): void
    {
        $controllerMock = $this->getMockBuilder(UserControllerChild::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getValidationErrors'])
            ->getMock();

        $controllerMock->method('getValidationErrors')->willReturn([]);

        $this->assertTrue($controllerMock->validateInputData());
    }

    public function testValidateInputDataReturnsFalseIfDataIsCorrect(): void
    {
        $controllerMock = $this->getMockBuilder(UserControllerChild::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getValidationErrors', 'respondUnprocessableEntity'])
            ->getMock();

        $controllerMock->expects($this->once())->method('respondUnprocessableEntity');
        $controllerMock->method('getValidationErrors')->willReturn(['error']);

        $this->assertFalse($controllerMock->validateInputData());
    }

    public function testCheckMethodReturnFalseIfMethodIsNotPost(): void
    {
        $controllerMock = $this->getMockBuilder(UserControllerChild::class)
            ->setConstructorArgs([
                $this->createMock(UserGateway::class),
                $this->createMock(RefreshTokenGateway::class),
                $this->createMock(JWTCodec::class),
                'GET',
                ['foo' => 'bar']
            ])->onlyMethods(['respondMethodNotAllowed'])
            ->getMock();

        $controllerMock->expects($this->once())->method('respondMethodNotAllowed');

        $this->assertFalse($controllerMock->checkMethod());
    }

    public function testCheckMethodReturnTrueIfMethodIsPost(): void
    {
        $controllerMock = $this->getMockBuilder(UserControllerChild::class)
            ->setConstructorArgs([
                $this->createMock(UserGateway::class),
                $this->createMock(RefreshTokenGateway::class),
                $this->createMock(JWTCodec::class),
                'POST',
                ['foo' => 'bar']
            ])->onlyMethods(['respondMethodNotAllowed'])
            ->getMock();

        $controllerMock->expects($this->never())->method('respondMethodNotAllowed');

        $this->assertTrue($controllerMock->checkMethod());
    }

    public function testRespondJWTReturnsIfNotUserWasNotCreated(): void
    {
        $data = ['foo' => 'bar'];
        $gatewayMock = $this->getMockBuilder(UserGateway::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $gatewayMock->expects($this->once())->method('create')->with($data)->willReturn(false);

        $controllerMock = $this->getMockBuilder(UserControllerChild::class)
            ->setConstructorArgs([
                $gatewayMock,
                $this->createMock(RefreshTokenGateway::class),
                $this->createMock(JWTCodec::class),
                'POST',
                $data
            ])->onlyMethods(['respondCreated', 'respondUnprocessableEntity'])
            ->getMock();

        $controllerMock->expects($this->never())->method('respondCreated');
        $controllerMock->expects($this->once())->method('respondUnprocessableEntity')
            ->with(['userReg' => "Server can't handle the request"]);

        $controllerMock->respondJWT();
    }

    public function testRespondJWTIfDataCorrect(): void
    {
        $data = ['foo' => 'bar'];
        $gatewayMock = $this->getMockBuilder(UserGateway::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $gatewayMock->expects($this->once())->method('create')->with($data)->willReturn(['42', '42']);

        $controllerMock = $this->getMockBuilder(UserControllerChild::class)
            ->setConstructorArgs([
                $gatewayMock,
                $this->createMock(RefreshTokenGateway::class),
                $this->createMock(JWTCodec::class),
                'POST',
                $data
            ])->onlyMethods(['respondCreated', 'generateJWT'])
            ->getMock();

        $controllerMock->expects($this->once())->method('generateJWT')->willReturn(['accessToken' => '42']);
        $controllerMock->expects($this->once())->method('respondCreated')->with(['accessToken' => '42']);

        $controllerMock->respondJWT();
    }

    /**
     * @dataProvider dataForGetValidationErrorsProvider
     */

    public function testGetCorrectValidationErrors(array $data, array $expectedErrors, array $user = []): void
    {
        $gatewayMock = $this->getMockBuilder(UserGateway::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getByEmail'])
            ->getMock();

        $gatewayMock->method('getByEmail')
            ->with($data['email'] ?? '')
            ->willReturn($user);

        $controllerMock = $this->getMockBuilder(UserControllerChild::class)
            ->setConstructorArgs([
                $gatewayMock,
                $this->createMock(RefreshTokenGateway::class),
                $this->createMock(JWTCodec::class),
                'POST',
                $data
            ])
            ->onlyMethods([])
            ->getMock();

        $this->assertEquals($expectedErrors, $controllerMock->getValidationErrors());
    }

    public function dataForGetValidationErrorsProvider(): array
    {
        return [
            [
                ['username' => 'foo', 'email' => 'mail@mail.ru', 'password' => '123'], []
            ],
            [
                ['username' => 'foo', 'email' => 'mail@mail.ru', 'password' => '123'],
                ['email' => 'User with this email already exists'], ['user']
            ],
            [
                ['username' => 'foo', 'email' => 'mail@mail.ru'],
                ['password' => 'Please input your password']
            ],
            [
                ['email' => 'mail@mail.ru', 'password' => '123'],
                ['username' => 'Please input your username']
            ],
            [
                ['email' => 'mail', 'password' => '123', 'username' => 'foo'],
                ['email' => 'Please input correct email']
            ],
            [
                ['username' => 'foo', 'password' => '123'],
                ['email' => 'Please input your email']
            ],
        ];
    }

    public function testCorrectlyGenerateAndReturnJWT()
    {
        $tokenGatewayMock = $this->getMockBuilder(refreshTokenGateway::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $tokenGatewayMock->expects($this->once())->method('create')->with('42', $this->anything());

        $codecMock = $this->getMockBuilder(JWTCodec::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['encode'])
            ->getMock();
        $codecMock->expects($this->exactly(2))->method('encode')->with($this->anything())->willReturn('42');

        $controllerMock = $this->getMockBuilder(UserControllerChild::class)
            ->setConstructorArgs([
                $this->createMock(UserGateway::class),
                $tokenGatewayMock,
                $codecMock,
                'POST',
                ['username' => 'foo']
            ])->onlyMethods([])->getMock();

        $this->assertEquals(
            [
                'accessToken' => '42',
                'refreshToken' => '42'
            ],
            $controllerMock->generateJWT('11')
        );
    }

    /**
     * @dataProvider dataForJSONProvider
     */

    public function testCallRenderJSONCorrectly(array | string $data, string $method): void
    {
        $controllerMock = $this->getMockBuilder(UserControllerChild::class)
            ->setConstructorArgs([
                $this->createMock(UserGateway::class),
                $this->createMock(RefreshTokenGateway::class),
                $this->createMock(JWTCodec::class),
                'POST',
                ['foo' => 'bar']
            ])->onlyMethods(['renderJSON'])->getMock();

        $controllerMock->expects($this->once())->method('renderJSON')->with($data);

        $controllerMock->$method($data);
    }

    public function dataForJSONProvider(): array
    {
        return [
            [['errors' => '42'], 'respondUnprocessableEntity'],
            [['message' => 'User created'], 'respondCreated'],
        ];
    }

    public function testCorrectlyRenderJSON()
    {
        $controllerMock = $this->getMockBuilder(UserControllerChild::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])->getMock();

        $controllerMock->renderJSON('42');
        $this->expectOutputString('"42"');
    }
}
