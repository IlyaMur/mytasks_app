<?php

declare(strict_types=1);

namespace TasksApp\Core;

use TasksApp\Exceptions\InvalidSignatureException;

class JWTCodec
{
    const JWT_REGEXP = "/^(?<header>.+)\.(?<payload>.+)\.(?<signature>.+)$/";

    public function __construct(private string $key)
    {
    }

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

    private function base64urlEncode(string $text): string
    {
        return str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode($text)
        );
    }

    private function base64urlDecode(string $text): string
    {
        return base64_decode(str_replace(
            ['-', '_'],
            ['+', '/'],
            $text
        ));
    }

    public function decode(string $token): array
    {
        // extracting tokens parts and throwing exception if token is invalid
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

        return $payload;
    }
}
