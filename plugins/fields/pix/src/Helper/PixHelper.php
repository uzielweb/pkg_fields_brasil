<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Pix
 *
 * @copyright   (C) 2026 Uziel Almeida Oliveira <https://github.com/uzielweb>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Uziel\Plugin\Fields\Pix\Helper;

use Uziel\Plugin\Fields\Cpf\Rule\CpfRule;
use Uziel\Plugin\Fields\Cnpj\Rule\CnpjRule;
use Uziel\Plugin\Fields\Telefone\Rule\TelefoneRule;

\defined('_JEXEC') or die;

/**
 * Pix Helper for EMVCo Payload Generation and Key Validation.
 * Compliant with Central Bank of Brazil (BACEN) Pix initiation specifications.
 */
class PixHelper
{
    /**
     * Identifies the type of Pix key provided.
     *
     * @param   string  $key
     * @return  string|null 'cpf', 'cnpj', 'email', 'phone', 'evp' or null if invalid.
     */
    public static function getKeyType(string $key): ?string
    {
        $trimmed = trim($key);

        if ($trimmed === '') {
            return null;
        }

        // Email key
        if (filter_var($trimmed, FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }

        // Random EVP Key (UUIDv4)
        if (preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $trimmed)) {
            return 'evp';
        }

        // Phone explicitly starting with international prefix '+'
        if (str_starts_with($trimmed, '+')) {
            $digits = preg_replace('/\D/', '', $trimmed);
            if (strlen($digits) >= 10 && strlen($digits) <= 13) {
                return 'phone';
            }
        }

        // Phone formatted with parentheses e.g. (11) 99999-9999
        if (str_contains($trimmed, '(') && str_contains($trimmed, ')')) {
            return 'phone';
        }

        // Clean digits
        $digits = preg_replace('/\D/', '', $trimmed);

        if (strlen($digits) === 10) {
            return 'phone';
        }

        if (strlen($digits) === 14) {
            return 'cnpj';
        }

        if (strlen($digits) === 11) {
            // Check if valid CPF
            if (class_exists(CpfRule::class) && CpfRule::validate($digits)) {
                return 'cpf';
            }
            // Check if valid Brazilian mobile phone
            if (class_exists(TelefoneRule::class) && TelefoneRule::validate($digits)) {
                return 'phone';
            }
            // Default to CPF if strictly 11 digits
            return 'cpf';
        }

        // Alphanumeric CNPJ (14 chars)
        $cleanAlnum = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $trimmed));
        if (strlen($cleanAlnum) === 14) {
            return 'cnpj';
        }

        return null;
    }

    /**
     * Normalizes the Pix key into official BACEN format.
     *
     * @param   string  $key
     * @param   string  $type
     * @return  string
     */
    public static function normalizeKey(string $key, string $type): string
    {
        $trimmed = trim($key);

        switch ($type) {
            case 'cpf':
            case 'cnpj':
                return strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $trimmed));

            case 'phone':
                $digits = preg_replace('/\D/', '', $trimmed);
                if (!str_starts_with($digits, '55')) {
                    $digits = '55' . $digits;
                }
                return '+' . $digits;

            case 'email':
            case 'evp':
            default:
                return $trimmed;
        }
    }

    /**
     * Formats an EMVCo TLV (Tag-Length-Value) segment.
     *
     * @param   string  $tag
     * @param   string  $value
     * @return  string
     */
    public static function formatTlv(string $tag, string $value): string
    {
        $len = strlen($value);
        return $tag . str_pad((string) $len, 2, '0', STR_PAD_LEFT) . $value;
    }

    /**
     * Calculates CRC16-CCITT (0xFFFF, polynomial 0x1021) for the Pix payload.
     *
     * @param   string  $payload
     * @return  string  4-character uppercase hexadecimal checksum.
     */
    public static function calculateCrc16(string $payload): string
    {
        $payloadWithCrcTag = $payload . '6304';
        $polynom = 0x1021;
        $crc     = 0xFFFF;
        $length  = strlen($payloadWithCrcTag);

        for ($i = 0; $i < $length; $i++) {
            $crc ^= (ord($payloadWithCrcTag[$i]) << 8);
            for ($j = 0; $j < 8; $j++) {
                if (($crc & 0x8000) !== 0) {
                    $crc = (($crc << 1) ^ $polynom) & 0xFFFF;
                } else {
                    $crc = ($crc << 1) & 0xFFFF;
                }
            }
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }

    /**
     * Generates complete static EMVCo "Pix Copia e Cola" payload string.
     *
     * @param   string       $key           Pix key.
     * @param   string       $merchantName  Beneficiary Name (max 25 characters).
     * @param   string       $merchantCity  Beneficiary City (max 15 characters).
     * @param   float|null   $amount        Transaction amount (optional).
     * @param   string       $txid          Transaction identifier (default '***').
     * @param   string       $description   Transaction description / message (optional, max 40 characters).
     * @return  string
     */
    public static function generatePayload(
        string $key,
        string $merchantName = 'BENEFICIARIO',
        string $merchantCity = 'SAO PAULO',
        ?float $amount = null,
        string $txid = '***',
        string $description = ''
    ): string {
        $type = self::getKeyType($key);
        if (!$type) {
            return '';
        }

        $normalizedKey = self::normalizeKey($key, $type);

        // Merchant Account Information (Tag 26)
        $gui     = self::formatTlv('00', 'br.gov.bcb.pix');
        $keyTlv  = self::formatTlv('01', $normalizedKey);
        $descTlv = '';

        if (trim($description) !== '') {
            $cleanDesc = substr(iconv('UTF-8', 'ASCII//TRANSLIT', trim($description)), 0, 40);
            $descTlv   = self::formatTlv('02', $cleanDesc);
        }

        $mai = self::formatTlv('26', $gui . $keyTlv . $descTlv);

        // Sanitize Name and City (BACEN limits and ASCII characters)
        $cleanName = strtoupper(iconv('UTF-8', 'ASCII//TRANSLIT', substr($merchantName, 0, 25)));
        $cleanCity = strtoupper(iconv('UTF-8', 'ASCII//TRANSLIT', substr($merchantCity, 0, 15)));
        $cleanTxid = preg_replace('/[^a-zA-Z0-9*]/', '', substr($txid, 0, 25)) ?: '***';

        $payload = self::formatTlv('00', '01')                  // Payload Format Indicator
            . $mai                                              // Merchant Account Info
            . self::formatTlv('52', '0000')                     // Merchant Category Code
            . self::formatTlv('53', '986');                     // Currency BRL

        // Tag 54: Transaction Amount (optional)
        if ($amount !== null && $amount > 0) {
            $payload .= self::formatTlv('54', number_format($amount, 2, '.', ''));
        }

        $payload .= self::formatTlv('58', 'BR')                 // Country Code
            . self::formatTlv('59', $cleanName)                 // Merchant Name
            . self::formatTlv('60', $cleanCity)                 // Merchant City
            . self::formatTlv('62', self::formatTlv('05', $cleanTxid)); // Additional Data Field (TxID)

        // Calculate and append Tag 63 (CRC16)
        $crc = self::calculateCrc16($payload);

        return $payload . '6304' . $crc;
    }
}
