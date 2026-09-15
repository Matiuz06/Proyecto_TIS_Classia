<?php

/**
 * Módulo de Autenticación de Dos Factores (2FA / TOTP)
 * Compatible con Google Authenticator, Microsoft Authenticator, Authy, 1Password, etc.
 * Basado en RFC 6238 (TOTP) y RFC 4226 (HOTP).
 */

class TOTP
{
    private const BASE32_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Genera un secreto aleatorio en Base32 (16 bytes = 26-32 caracteres Base32).
     */
    public static function generarSecreto(int $length = 16): string
    {
        $secret = '';
        $bytes = random_bytes($length);
        for ($i = 0; $i < $length; $i++) {
            $secret .= self::BASE32_CHARS[ord($bytes[$i]) % 32];
        }
        return $secret;
    }

    /**
     * Decodifica una cadena Base32 a binario.
     */
    private static function base32Decode(string $b32): string
    {
        $b32 = strtoupper(preg_replace('/[^A-Z2-7]/', '', $b32));
        $buffer = 0;
        $bitsLeft = 0;
        $binary = '';

        for ($i = 0, $len = strlen($b32); $i < $len; $i++) {
            $val = strpos(self::BASE32_CHARS, $b32[$i]);
            if ($val === false) {
                continue;
            }
            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;
            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $binary .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $binary;
    }

    /**
     * Calcula el código TOTP de 6 dígitos para un secreto y timestamp dado.
     */
    public static function calcularCodigo(string $secret, ?int $timestamp = null, int $period = 30): string
    {
        $time = $timestamp ?? time();
        $counter = (int) floor($time / $period);

        // Convertir contador a 8 bytes big-endian
        $counterBytes = pack('N*', 0) . pack('N*', $counter);
        $key = self::base32Decode($secret);

        $hash = hash_hmac('sha1', $counterBytes, $key, true);
        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;

        $binary = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        );

        $otp = $binary % 1000000;
        return str_pad((string) $otp, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Verifica un código TOTP ingresado por el usuario permitiendo una ventana de tolerancia.
     */
    public static function verificarCodigo(string $secret, string $codigo, int $tolerancia = 1, int $period = 30): bool
    {
        $codigo = trim($codigo);
        if (!preg_match('/^[0-9]{6}$/', $codigo)) {
            return false;
        }

        $now = time();
        for ($i = -$tolerancia; $i <= $tolerancia; $i++) {
            $t = $now + ($i * $period);
            if (hash_equals(self::calcularCodigo($secret, $t, $period), $codigo)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Genera la URI estándar otpauth:// para escanear en apps autenticadoras.
     */
    public static function generarUri(string $cuenta, string $secreto, string $emisor = 'Classia'): string
    {
        $label = rawurlencode($emisor) . ':' . rawurlencode($cuenta);
        return 'otpauth://totp/' . $label . '?secret=' . rawurlencode($secreto) . '&issuer=' . rawurlencode($emisor) . '&algorithm=SHA1&digits=6&period=30';
    }

    /**
     * Genera códigos de respaldo únicos (8 códigos de 8 caracteres).
     * Retorna array con ['codigos_claros' => [...], 'codigos_hasheados' => [...]]
     */
    public static function generarCodigosRespaldo(int $cantidad = 8): array
    {
        $claros = [];
        $hashes = [];

        for ($i = 0; $i < $cantidad; $i++) {
            $code = strtoupper(bin2hex(random_bytes(4))); // 8 caracteres hexadecimales
            $claros[] = substr($code, 0, 4) . '-' . substr($code, 4, 4);
            $hashes[] = hash('sha256', str_replace('-', '', $code));
        }

        return [
            'claros' => $claros,
            'hashes' => $hashes,
        ];
    }

    /**
     * Verifica y consume un código de respaldo.
     */
    public static function verificarYConsumirCodigoRespaldo(string $codigoIngresado, string &$backupCodesJson): bool
    {
        $limpio = strtoupper(preg_replace('/[^A-Z0-9]/', '', $codigoIngresado));
        if (strlen($limpio) !== 8) {
            return false;
        }

        $hashIngresado = hash('sha256', $limpio);
        $hashesGuardados = json_decode($backupCodesJson, true) ?: [];

        $indice = array_search($hashIngresado, $hashesGuardados, true);
        if ($indice !== false) {
            // Eliminar el código ya utilizado (de un solo uso)
            unset($hashesGuardados[$indice]);
            $backupCodesJson = json_encode(array_values($hashesGuardados));
            return true;
        }

        return false;
    }
}
