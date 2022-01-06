<?php

namespace TasksApp\Core;

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
        if (preg_match(static::JWT_REGEXP, $token, $matches) !== 1) {
            throw new \InvalidArgumentException("Ivalid token format");
        }

        $signature = hash_hmac(
            'sha256',
            $matches['header'] . '.' . $matches['payload'],
            $this->key,
            true
        );

        $signatureFromToken = $this->base64urlDecode($matches['signature']);

        if (!hash_equals($signature, $signatureFromToken)) {
            throw new \Exception("Signature doesn't match format");
        }

        $payload = json_decode($this->base64urlDecode($matches['payload']), true);

        return $payload;
    }
}
