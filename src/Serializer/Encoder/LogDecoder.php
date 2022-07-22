<?php

declare(strict_types=1);

namespace App\Serializer\Encoder;

use Symfony\Component\Serializer\Encoder\DecoderInterface;

class LogDecoder implements DecoderInterface
{

    public const FORMAT           = 'log';
    public const SERVICE_NAME_KEY = 'serviceName';
    public const LOG_DATE_KEY     = 'logDate';
    public const ACTION_KEY       = 'action';
    public const STATUS_CODE_KEY  = 'statusCode';
    private const DECODE_KEY       = [
            self::SERVICE_NAME_KEY,
            self::LOG_DATE_KEY,
            self::ACTION_KEY,
            self::STATUS_CODE_KEY
        ];


    public function decode(string $data, string $format, array $context = []): array
    {
        $encodedData = [];
        $lines       = file($data, FILE_IGNORE_NEW_LINES);
        foreach ($lines as $line) {
            $matches = [];
            preg_match('/(.*) - ? - ? \[(.*)\] ?(\".*\") ?(\d*)/', $line, $matches);
            unset($matches[0]);
            $encodedData[] = array_combine(self::DECODE_KEY, $matches);
        }
        return $encodedData;
    }

    public function supportsDecoding(string $format): bool
    {
        return self::FORMAT === $format;
    }

}
