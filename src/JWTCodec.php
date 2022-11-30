<?php

class JWTCodec
{
    public function __construct(private string $key)
    {
    }
    public function encode(array $payload): string
    {
        $header = json_encode([
            "typ" => "JWT",
            "alg" => "HS256"
        ]);
        $header = $this->base64urlEncoder($header);

        $payload = json_encode($payload);
        $payload = $this->base64urlEncoder($payload);

        $signature = hash_hmac(
            "SHA256",
            $header . "." . $payload,
            "$this->key",
            true
        );
        $signature = $this->base64urlEncoder($signature);
        return $header . "." . $payload . "." . $signature;
    }

    public function decode(string $token): array
    {
        if (preg_match(
            "/^(?<header>.+)\.(?<payload>.+)\.(?<signature>.+)$/",
            $token,
            $matches
        ) !== 1) {
            throw new InvalidArgumentException("invalid token format");
        }

        $signature = hash_hmac(
            "sha256",
            $matches['header'] . "." . $matches['payload'],
            "$this->key",
            true
        );
        $signature_from_token = $this->base64urlDecoder($matches['signature']);
        if (!hash_equals($signature, $signature_from_token)) {
            throw new InvalidSignatureException();
        }

        $payload = json_decode($this->base64urlDecoder($matches['payload']), true);

        if ($payload['exp'] < time()) {
            throw new TokenExpiredException();
        }

        return $payload;
    }

    private function base64urlEncoder(string $text): string
    {
        return str_replace(
            ["+", "/", "="],
            ["-", "_", ""],
            base64_encode($text)
        );
    }

    private function base64urlDecoder(string $text): string
    {
        return base64_decode(str_replace(
            ["-", "_"],
            ["+", "/"],
            $text
        ));
    }
}
