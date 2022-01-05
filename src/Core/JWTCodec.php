<?

declare(strict_types=1);

namespace TasksApp\Core;

class JWTCodec
{
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
            '2A472D4B6150645367566B59703373367639792F423F4528482B4D6251655468',
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
}
