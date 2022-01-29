<?php

declare(strict_types=1);

namespace Ilyamur\TaskApp\Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use Ilyamur\TasksApp\Services\JWTCodec;
use Ilyamur\TasksApp\Gateways\UserGateway;
use Ilyamur\TaskApp\Tests\Unit\Services\TestDoubles\AuthChild;

class AuthTest extends TestCase
{
    public function testTypeOFAuthenticationDependsByConfigConst(): void
    {
        // JWT auth is default
        $authMock = $this->getMockBuilder(AuthChild::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['authenticateByJWT'])->getMock();
        $authMock->expects($this->once())->method('authenticateByJWT');
        $authMock->authenticate();
    }

    public function testRespondWarnMessageIfKeyIsMissingAndReturnFalse(): void
    {
        $authMock = $this->getMockBuilder(AuthChild::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAPIKeyFromHeader', 'respondWarnMessage'])->getMock();

        $authMock->expects($this->once())->method('getAPIKeyFromHeader')->willReturn(null);
        $authMock->expects($this->once())->method('respondWarnMessage')->with('missing API key');

        $this->assertFalse($authMock->authenticateByKey());
    }

    public function testRespondWarnMessageIfKeyIsInvalidAndReturnFalse(): void
    {
        $userGatewayMock = $this->getMockBuilder(UserGateway::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getByAPIKey'])->getMock();
        $userGatewayMock->expects($this->once())
            ->method('getByAPIKey')->with('foo')->willReturn(false);

        $authMock = $this->getMockBuilder(AuthChild::class)
            ->setConstructorArgs([
                $userGatewayMock,
                $this->createMock(JWTCodec::class),
            ])->onlyMethods(['getAPIKeyFromHeader', 'respondWarnMessage'])
            ->getMock();

        $authMock->expects($this->once())
            ->method('getAPIKeyFromHeader')->willReturn('foo');
        $authMock->expects($this->once())
            ->method('respondWarnMessage')->with('invalid API key', 401);

        $this->assertFalse($authMock->authenticateByKey());
    }

    public function testauthenticateByKeyIsCorrectWhenDataIsValid(): void
    {
        $userGatewayMock = $this->getMockBuilder(UserGateway::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getByAPIKey'])->getMock();
        $userGatewayMock->expects($this->once())
            ->method('getByAPIKey')->with('foo')->willReturn(['id' => '42']);

        $authMock = $this->getMockBuilder(AuthChild::class)
            ->setConstructorArgs([
                $userGatewayMock,
                $this->createMock(JWTCodec::class),
            ])->onlyMethods(['getAPIKeyFromHeader'])
            ->getMock();

        $authMock->expects($this->once())
            ->method('getAPIKeyFromHeader')->willReturn('foo');

        $this->assertTrue($authMock->authenticateByKey());
        $this->assertEquals('42', $authMock->getUserID());
    }

    public function testAuthenticateByJWTReturnFalseIfHeaderIsIncomplete(): void
    {
        $authMock = $this->getMockBuilder(AuthChild::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getJWTFromHeader', 'respondWarnMessage'])->getMock();
        $authMock->expects($this->exactly(2))
            ->method('getJWTFromHeader')->willReturn('Bearr');
        $authMock->expects($this->once())
            ->method('respondWarnMessage')->with('Incomplete authorization header');

        $this->assertFalse($authMock->authenticateByJWT());
    }
    public function testAuthenticateByJWTReturnFalseIfHeaderIsNotPersist(): void
    {
        $authMock = $this->getMockBuilder(AuthChild::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getJWTFromHeader', 'respondWarnMessage'])->getMock();
        $authMock->expects($this->once())
            ->method('getJWTFromHeader')->willReturn(null);
        $authMock->expects($this->once())
            ->method('respondWarnMessage')->with('Please enter an authorization token');

        $this->assertFalse($authMock->authenticateByJWT());
    }


    /**
     * @dataProvider exceptionForAuthProvider
     */

    public function testAuthenticateByJWTThrowAnException(string $exceptionClass, string $exceptionMsg, int $code): void
    {
        $codecMock = $this->getMockBuilder(JWTCodec::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['decode'])
            ->getMock();
        $codecMock->expects($this->once())
            ->method('decode')
            ->will($this->throwException(new $exceptionClass()));

        $authMock = $this->getMockBuilder(AuthChild::class)
            ->setConstructorArgs([
                $this->createMock(UserGateway::class),
                $codecMock
            ])
            ->onlyMethods(['getJWTFromHeader', 'respondWarnMessage'])->getMock();

        $authMock->expects($this->exactly(2))
            ->method('getJWTFromHeader')->willReturn('Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9');
        $authMock->expects($this->once())
            ->method('respondWarnMessage')->with($exceptionMsg, $code);

        $this->assertFalse($authMock->authenticateByJWT());
    }

    public function exceptionForAuthProvider(): array
    {
        return [
            ['Ilyamur\TasksApp\Exceptions\InvalidSignatureException', 'invalid signature', 401],
            ['Ilyamur\TasksApp\Exceptions\TokenExpiredException', 'token has expired', 401],
            ['\Exception', '', 400]
        ];
    }

    public function testAuthenticateByJWTReturnTrueIfKeyIsCorrect(): void
    {
        $codecMock = $this->getMockBuilder(JWTCodec::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['decode'])
            ->getMock();
        $codecMock->expects($this->once())
            ->method('decode')
            ->willReturn(['sub' => 1]);

        $authMock = $this->getMockBuilder(AuthChild::class)
            ->setConstructorArgs([
                $this->createMock(UserGateway::class),
                $codecMock
            ])
            ->onlyMethods(['getJWTFromHeader'])->getMock();

        $authMock->expects($this->exactly(2))
            ->method('getJWTFromHeader')->willReturn('Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9');

        $this->assertTrue($authMock->authenticateByJWT());
    }

    public function testRespondWarnMessageCorrectly()
    {
        $authMock = $this->getMockBuilder(AuthChild::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['renderJSON'])->getMock();

        $authMock->expects($this->once())
            ->method('renderJSON')->with(['message' => 'Test warning']);

        $authMock->respondWarnMessage('Test warning', 401);
    }

    public function testCorrectlyRenderJSON()
    {
        $authMock = $this->getMockBuilder(AuthChild::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])->getMock();

        $authMock->renderJSON('42');
        $this->expectOutputString('"42"');
    }
}
