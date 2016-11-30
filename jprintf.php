<?php

namespace kafene;

use RuntimeException;

/**
 * @see <https://secure.php.net/manual/en/function.printf.php>
 * @param string $format
 * @param mixed $args
 * @return int
 */
function jprintf(string $format, ...$args) {
    return printf('%s', jvsprintf($format, $args));
}

/**
 * @see <https://secure.php.net/manual/en/function.vprintf.php>
 * @param string $format
 * @param array $args
 * @param int $jsonOptions json_encode options
 * @return int
 */
function jvprintf(string $format, array $args, int $jsonOptions = 0) {
    return vprintf('%s', [ jvsprintf($format, $args, $jsonOptions) ]);
}

/**
 * @see <https://secure.php.net/manual/en/function.sprintf.php>
 * @param string $format
 * @param mixed $args
 * @return string
 */
function jsprintf(string $format, ...$args) {
    return sprintf('%s', jvsprintf($format, $args));
}

/**
 * @see <https://secure.php.net/manual/en/function.vsprintf.php>
 * @throws RuntimeException For json_encode errors
 * @param string $format
 * @param array $args
 * @param int $jsonOptions json_encode options
 * @return string
 */
function jvsprintf(string $format, array $args, int $jsonOptions = 0) {
    // Apparently json_last_error() is still set even if `JSON_PARTIAL_OUTPUT_ON_ERROR`
    // is used, so this helper function determines whether to actually throw an exception.
    // @see <https://secure.php.net/manual/en/json.constants.php>
    static $checkJson;
    if ($checkJson === null) {
        $checkJson = function ($result, int $jsonOptions) {
            $errorCode = json_last_error();
            if ($errorCode === JSON_ERROR_NONE) {
                return $result;
            }
            $partialOutputOnErrorUsed = ($jsonOptions & JSON_PARTIAL_OUTPUT_ON_ERROR) !== 0;
            $ignored = [JSON_ERROR_RECURSION, JSON_ERROR_INF_OR_NAN, JSON_ERROR_UNSUPPORTED_TYPE];
            if ($partialOutputOnErrorUsed && in_array($errorCode, $ignored, true)) {
                return $result;
            } else {
                throw new RuntimeException(json_last_error_msg());
            }
        };
    };

    return vsprintf($format, array_map(function ($arg) use ($jsonOptions, $checkJson) {
        return is_string($arg) ? $arg : $checkJson(json_encode($arg, $jsonOptions), $jsonOptions);
    }, $args));
}

/**
 * @see <https://secure.php.net/manual/en/function.fprintf.php>
 * @param resource $handle
 * @param string $format
 * @param mixed $args
 * @return int
 */
function jfprintf($handle, string $format, ...$args) {
    return fprintf($handle, '%s', jvsprintf($format, $args));
}

/**
 * @see <https://secure.php.net/manual/en/function.vfprintf.php>
 * @param resource $handle
 * @param string $format
 * @param array $args
 * @param int $jsonOptions json_encode options
 * @return int
 */
function jvfprintf($handle, string $format, array $args, int $jsonOptions = 0) {
    return vfprintf($handle, '%s', [ jvsprintf($format, $args, $jsonOptions) ]);
}
