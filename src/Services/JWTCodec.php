<?php

declare(strict_types=1);

namespace Ilyamur\TasksApp\Services;

use Ilyamur\TasksApp\Exceptions\TokenExpiredException;
use Ilyamur\TasksApp\Exceptions\InvalidSignatureException;

/**
 * JWTCodec
 *
 * PHP version 8.0
 */
class JWTCodec
{
    /**
     * Regexp for parsing JWT
     *
     * @var string
     */
    private const JWT_REGEXP = "/^(?<header>.+)\.(?<payload>.+)\.(?<signature>.+)$/";

    /**
     * Class constructor
     *
     * @param string $key secret key
     *
     * @return void
     */
    public function __construct(private string $key)
    {
    }

    /**
     * Encode array to JWT
     *
     * @param array $payload Payload for encrypting
     *
     * @return string
     */
    public function encode(array $payload): string
    {
        $header = json_encode([
            'typ' => 'JWT',
            'alg' => 'HS256'
        ]);

        $header = $this->base64urlEncode($header);
        $payload = $this->base64urlEncode(json_encode($payload));

        $signature = hash_hmac(
            'sha256',
            $header . '.' . $payload,
            $this->key,
            true
        );

        $signature = $this->base64urlEncode($signature);

        return $header . '.' . $payload . '.' . $signature;
    }

    /**
     * URL friendly encoding to base64
     *
     * @param string $text String to encode
     *
     * @return string
     */
    private function base64urlEncode(string $text): string
    {
        return str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode($text)
        );
    }

    /**
     * Decoding from URL friendly base64
     *
     * @param string $text String to decode
     *
     * @return string
     */
    private function base64urlDecode(string $text): string
    {
        return base64_decode(str_replace(
            ['-', '_'],
            ['+', '/'],
            $text
        ));
    }

    /**
     * Decoding input JWT
     *
     * @param string $token JWT
     *
     * @return array
     */
    public function decode(string $token): array
    {
        // Extracting tokens parts and throwing an exception if JWT is invalid
        if (preg_match(static::JWT_REGEXP, $token, $matches) !== 1) {
            throw new \InvalidArgumentException("ivalid token format");
        }

        $signature = hash_hmac(
            'sha256',
            $matches['header'] . '.' . $matches['payload'],
            $this->key,
            true
        );

        $signatureFromToken = $this->base64urlDecode($matches['signature']);

        if (!hash_equals($signature, $signatureFromToken)) {
            throw new InvalidSignatureException("signature doesn't match");
        }

        $payload = json_decode($this->base64urlDecode($matches['payload']), true);

        // Throwing an exception if access token is expired
        if ($payload['exp'] < time()) {
            throw new TokenExpiredException();
        }
        return $payload;
    }
}
