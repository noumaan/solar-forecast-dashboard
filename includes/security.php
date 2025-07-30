<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Get the encryption key for securing the Solcast API key.
 * 
 * - If `SOLFORDASH_SECRET_KEY` is defined in wp-config.php, use that.
 * - Otherwise, auto-generate and store a persistent key in the database.
 * 
 * @return string Binary encryption key
 */
function solfordash_get_secret_key() {
    // ✅ Prefer SOLFORDASH_SECRET_KEY from wp-config.php for backward compatibility
    if (defined('SOLFORDASH_SECRET_KEY')) {
        return hash('sha256', SOLFORDASH_SECRET_KEY, true); // hashed to 32 bytes (binary)
    }

    // ✅ Auto-generate and store key in DB if not already present
    $key = get_option('solfordash_secret_key');
    if (!$key) {
        $key = base64_encode(random_bytes(32)); // 256-bit key
        add_option('solfordash_secret_key', $key, '', 'no'); // don't autoload
    }

    return hash('sha256', base64_decode($key), true);
}

/**
 * Encrypt a plaintext string using AES-256-CBC.
 *
 * @param string $plaintext
 * @return string Base64-encoded ciphertext (IV + encrypted content)
 */
function solfordash_encrypt($plaintext) {
    $iv = openssl_random_pseudo_bytes(16);
    $ciphertext = openssl_encrypt($plaintext, 'AES-256-CBC', solfordash_get_secret_key(), OPENSSL_RAW_DATA, $iv);
    return base64_encode($iv . $ciphertext);
}

/**
 * Decrypt a base64-encoded ciphertext.
 *
 * @param string $encoded Base64-encoded (IV + ciphertext)
 * @return string|false Decrypted plaintext or false on failure
 */
function solfordash_decrypt($encoded) {
    $data = base64_decode($encoded);
    if (!$data || strlen($data) < 17) {
        return false; // Malformed or empty input
    }

    $iv = substr($data, 0, 16);
    $ciphertext = substr($data, 16);
    return openssl_decrypt($ciphertext, 'AES-256-CBC', solfordash_get_secret_key(), OPENSSL_RAW_DATA, $iv);
}
