<?php

declare(strict_types=1);

namespace Ilyamur\TaskApp\Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use Ilyamur\TasksApp\Services\JWTCodec;

class JWTCodecTest extends TestCase
{
    public function setUp(): void
    {
        $this->codec = new JWTCodec('DUMMKEY');
        $this->correctJWTFixt = file_get_contents(dirname(__DIR__) .
            '/__fixtures__/correctJWT.txt');
    }

    public function testEncodeToJWTCorrectly(): void
    {
        $encodeString = $this->codec->encode([
            'sub' => 1,
            'name' => 'foo',
            'exp' => 2000000000
        ]);

        $this->assertEquals($this->correctJWTFixt, $encodeString);
    }

    public function testDecodeJWTCorrectly(): void
    {
        $decodedPayload = $this->codec->decode($this->correctJWTFixt);
        $expectedOutput = [
            'sub' => 1,
            'name' => 'foo',
            'exp' => 2000000000
        ];

        $this->assertEquals($expectedOutput, $decodedPayload);
    }

    /**
     * @dataProvider exceptionProvider
     */

    public function testDecodeJWTThrowException(string $exception, string $filename): void
    {
        $fixtString = file_get_contents(dirname(__DIR__) .
            "/__fixtures__/$filename.txt");

        $this->expectException($exception);

        $this->codec->decode($fixtString);
    }

    public function exceptionProvider(): array
    {
        return [
            ['\InvalidArgumentException', 'invalidFormatJWT'],
            ['Ilyamur\TasksApp\Exceptions\InvalidSignatureException', 'invalidSignatureJWT'],
            ['Ilyamur\TasksApp\Exceptions\TokenExpiredException', 'expiredJWT'],
        ];
    }
}
