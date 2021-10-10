<?php

/**
 * JWT token encode/decode related utility function.
 */
class JwtUtil
{
    public static function genPayload($uid)
    {
        $now = new DateTime();
        $timestamp = $now->getTimestamp();
        $issue = Config::getConfig(Config::$KEY_AUTH)["issue"];
        $expire = Config::getConfig(Config::$KEY_AUTH)["expire"];
        $payload = array(
            "iss" => $issue,
            "iat" => $timestamp,
            "exp" => $timestamp + $expire,
            "uid" => $uid
        );
        return $payload;
    }

    public static function encodePayload(array $payload, $key = "", $alg = "SHA256")
    {
        if ($key === "") {
            $key = Config::getConfig(Config::$KEY_AUTH)["key"];
        }
        $key = md5($key);
        $jwt = static::urlsafeB64Encode(json_encode(["typ" => "JWT", "alg" => $alg]));
        $jwt .= "." . static::urlsafeB64Encode(json_encode($payload));
        return $jwt . '.' . static::signature($jwt, $key, $alg);
    }

    public static function decodePayload($jwt, $key = "")
    {
        if ($key === "") {
            $key = Config::getConfig(Config::$KEY_AUTH)["key"];
        }
        $tokens = explode('.', $jwt);
        $key = md5($key);
        if (count($tokens) != 3) {
            throw new UnauthorizedError("JWT token format is wrong.");
        }

        list($header64, $payload64, $sign) = $tokens;
        $header = json_decode(static::urlsafeB64Decode($header64), JSON_OBJECT_AS_ARRAY);
        if (empty($header['alg'])) {
            throw new UnauthorizedError("Algorithm is empty.");
        }
        if (static::signature($header64 . '.' . $payload64, $key, $header['alg']) !== $sign) {
            throw new UnauthorizedError("Signature error.");
        }

        $payload = json_decode(static::urlsafeB64Decode($payload64), JSON_OBJECT_AS_ARRAY);
        $time = $_SERVER['REQUEST_TIME'];
        if (isset($payload['iat']) && $payload['iat'] > $time) {
            throw new UnauthorizedError("Issue time is newer than now time.");
        }
        return $payload;
    }

    private static function signature($input, $key, $alg)
    {
        return hash_hmac($alg, $input, $key);
    }

    private static function urlsafeB64Encode($input)
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    private static function urlsafeB64Decode($input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }
}
