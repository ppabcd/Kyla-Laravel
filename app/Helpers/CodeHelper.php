<?php

namespace App\Helpers;

use App\Exceptions\InvalidJsonStringException;
use App\Exceptions\NullPointerException;
use Carbon\Carbon;
use Illuminate\Support\Str;

class CodeHelper
{
    /**
     * Parse command arguments from string
     */
    public static function parseCommand(?string $command): array
    {
        if (! $command) {
            return [];
        }

        $parts = explode(' ', trim($command));
        array_shift($parts); // Remove command itself

        $args = [];
        foreach ($parts as $part) {
            if (strpos($part, '=') !== false) {
                [$key, $value] = explode('=', $part, 2);
                $args[$key] = $value;
            } else {
                $args[] = $part;
            }
        }

        return $args;
    }

    /**
     * Convert object to URL encoded string
     */
    public static function objectToUrlEncoded(array $obj): string
    {
        $parts = [];
        foreach ($obj as $key => $value) {
            $parts[] = urlencode($key).'='.urlencode($value);
        }

        return implode('&', $parts);
    }

    /**
     * Ensure value is not null or throw exception
     */
    public static function ensureNotNull($value, string $errorMessage = 'The data null or undefined')
    {
        if ($value === null) {
            throw new NullPointerException($errorMessage);
        }

        return $value;
    }

    /**
     * Convert JSON strings to objects
     */
    public static function convertJsonStringsToObjects(array $params): array
    {
        $result = [];
        foreach ($params as $param) {
            try {
                $data = json_decode($param, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new InvalidJsonStringException($params);
                }
                $result[] = $data;
            } catch (\Exception $e) {
                throw new InvalidJsonStringException($params);
            }
        }

        return $result;
    }

    /**
     * Get enum key by value
     */
    public static function getEnumKeyByValue(array $enumType, $value): ?string
    {
        foreach ($enumType as $key => $enumValue) {
            if ($enumValue === $value) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Get enum by value
     */
    public static function getEnumByValue(array $enumType, string $value)
    {
        foreach ($enumType as $key => $enumValue) {
            if ($enumValue === $value) {
                return $enumValue;
            }
        }

        return null;
    }

    /**
     * Format date string with Carbon
     */
    public static function formatDateStringWithMoment(string $dateString, string $format = 'd-m-Y H:i'): string
    {
        return Carbon::parse($dateString)->format($format);
    }

    /**
     * Escape markdown characters
     */
    public static function escapeMarkdown(string $text): string
    {
        return preg_replace('/[_*\\[\]()~`>#\\+=|{}.\-!]/', '\\\\$0', $text);
    }

    /**
     * Extract signature from text and entities
     */
    public static function extractSignature(?string $text, ?array $entities): ?string
    {
        if (! $text || ! $entities) {
            return null;
        }

        $signaturePrefix = 'SIGN:';

        foreach ($entities as $entity) {
            if (
                isset($entity['type']) && $entity['type'] === 'code' &&
                isset($entity['offset']) && isset($entity['length'])
            ) {
                $substring = substr($text, $entity['offset'], strlen($signaturePrefix));
                if ($substring === $signaturePrefix) {
                    return substr(
                        $text,
                        $entity['offset'] + strlen($signaturePrefix),
                        $entity['length'] - strlen($signaturePrefix)
                    );
                }
            }
        }

        return null;
    }

    /**
     * Remove signature text from message
     */
    public static function removeSignatureText(string $text): string
    {
        return preg_replace('/SIGN:.*?$/s', '', $text);
    }

    /**
     * Create random number between min and max
     */
    public static function createRandomNumber(int $min, int $max): int
    {
        return rand($min, $max);
    }

    /**
     * Create random text with specified length
     */
    public static function createRandomText(int $length): string
    {
        return Str::random($length);
    }

    /**
     * Create random bold string in markdown format
     */
    public static function createRandomBoldStringMarkdown(string $boldData): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $result = '';

        for ($i = 0; $i < strlen($boldData); $i++) {
            // Add random character
            $result .= $characters[rand(0, strlen($characters) - 1)];
            // Add bold character in Markdown format
            $result .= '*'.$boldData[$i].'*';
        }

        // Add final random character
        $result .= $characters[rand(0, strlen($characters) - 1)];

        return $result;
    }

    /**
     * Capitalize first letter of each word
     */
    public static function capitalizeFirstLetterOfEachWord(string $str): string
    {
        return ucwords($str);
    }
}
