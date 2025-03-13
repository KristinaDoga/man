<?php

/**
 * @file
 * Secure password hashing functions for user authentication.
 *
 * Based on the Portable PHP password hashing framework.
 * @see http://www.openwall.com/phpass/
 */

/**
 * The standard log2 number of iterations for password stretching.
 */
define('DRUPAL_HASH_COUNT', 15);

/**
 * The minimum allowed log2 number of iterations for password stretching.
 */
define('DRUPAL_MIN_HASH_COUNT', 7);

/**
 * The maximum allowed log2 number of iterations for password stretching.
 */
define('DRUPAL_MAX_HASH_COUNT', 30);

/**
 * The expected (and maximum) number of characters in a hashed password.
 */
define('DRUPAL_HASH_LENGTH', 55);

/**
 * Returns a string for mapping an int to the corresponding base 64 character.
 */
function _password_itoa64() {
    return './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
}

/**
 * Encodes bytes into printable base 64 using the *nix standard from crypt().
 *
 * @param $input
 *   The string containing bytes to encode.
 * @param $count
 *   The number of characters (bytes) to encode.
 *
 * @return
 *   Encoded string
 */
function _password_base64_encode($input, $count) {
    $output = '';
    $i = 0;
    $itoa64 = _password_itoa64();
    do {
        $value = ord($input[$i++]);
        $output .= $itoa64[$value & 0x3f];
        if ($i < $count) {
            $value |= ord($input[$i]) << 8;
        }
        $output .= $itoa64[($value >> 6) & 0x3f];
        if ($i++ >= $count) {
            break;
        }
        if ($i < $count) {
            $value |= ord($input[$i]) << 16;
        }
        $output .= $itoa64[($value >> 12) & 0x3f];
        if ($i++ >= $count) {
            break;
        }
        $output .= $itoa64[($value >> 18) & 0x3f];
    } while ($i < $count);

    return $output;
}

/**
 * Generates a random base 64-encoded salt prefixed with settings for the hash.
 *
 * @param $count_log2
 *   Integer that determines the number of iterations used in the hashing process.
 *
 * @return
 *   A 12 character string containing the iteration count and a random salt.
 */
function _password_generate_salt($count_log2) {
    $output = '$S$';
    $count_log2 = _password_enforce_log2_boundaries($count_log2);
    $itoa64 = _password_itoa64();
    $output .= $itoa64[$count_log2];
    $output .= _password_base64_encode(random_bytes(6), 6);
    return $output;
}

/**
 * Ensures that $count_log2 is within set bounds.
 *
 * @param $count_log2
 *   Integer that determines the number of iterations used in the hashing process.
 *
 * @return
 *   Integer within set bounds that is closest to $count_log2.
 */
function _password_enforce_log2_boundaries($count_log2) {
    if ($count_log2 < DRUPAL_MIN_HASH_COUNT) {
        return DRUPAL_MIN_HASH_COUNT;
    } elseif ($count_log2 > DRUPAL_MAX_HASH_COUNT) {
        return DRUPAL_MAX_HASH_COUNT;
    }

    return (int) $count_log2;
}

/**
 * Hash a password using a secure stretched hash.
 *
 * @param $algo
 *   The string name of a hashing algorithm usable by hash(), like 'sha512'.
 * @param $password
 *   Plain-text password up to 512 bytes (128 to 512 UTF-8 characters) to hash.
 * @param $setting
 *   An existing hash or the output of _password_generate_salt(). Must be at least 12 characters.
 *
 * @return
 *   A string containing the hashed password (and salt) or FALSE on failure.
 */
function _password_crypt($algo, $password, $setting) {
    if (strlen($password) > 512) {
        return FALSE;
    }
    $setting = substr($setting, 0, 12);

    if ($setting[0] != '$' || $setting[2] != '$') {
        return FALSE;
    }
    $count_log2 = _password_get_count_log2($setting);
    if ($count_log2 < DRUPAL_MIN_HASH_COUNT || $count_log2 > DRUPAL_MAX_HASH_COUNT) {
        return FALSE;
    }
    $salt = substr($setting, 4, 8);
    if (strlen($salt) != 8) {
        return FALSE;
    }

    $count = 1 << $count_log2;

    $hash = hash($algo, $salt . $password, TRUE);
    do {
        $hash = hash($algo, $hash . $password, TRUE);
    } while (--$count);

    $len = strlen($hash);
    $output = $setting . _password_base64_encode($hash, $len);
    $expected = 12 + ceil((8 * $len) / 6);
    return (strlen($output) == $expected) ? substr($output, 0, DRUPAL_HASH_LENGTH) : FALSE;
}

/**
 * Parse the log2 iteration count from a stored hash or setting string.
 */
function _password_get_count_log2($setting) {
    $itoa64 = _password_itoa64();
    return strpos($itoa64, $setting[3]);
}

/**
 * Hash a password using a secure hash.
 *
 * @param $password
 *   A plain-text password.
 * @param $count_log2
 *   Optional integer to specify the iteration count. Generally used only during mass operations.
 *
 * @return
 *   A string containing the hashed password (and a salt), or FALSE on failure.
 */
function user_hash_password($password, $count_log2 = 0) {
    if (empty($count_log2)) {
        $count_log2 = DRUPAL_HASH_COUNT;
    }
    return _password_crypt('sha512', $password, _password_generate_salt($count_log2));
}

// Main script

if ($argc > 1) {
    $password = $argv[1];
    $hashed_password = user_hash_password($password);
    echo "Hashed password: " . $hashed_password . PHP_EOL;
} else {
    echo "Usage: php hash_password.php trfnth23byf2H" . PHP_EOL;
}
