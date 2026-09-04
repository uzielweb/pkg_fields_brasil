<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Cnpj
 *
 * @copyright   (C) 2026 Uziel Almeida Oliveira <https://github.com/uzielweb>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Uziel\Plugin\Fields\Cnpj\Rule;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;

\defined('_JEXEC') or die;

/**
 * Form Rule class for CNPJ validation.
 * Fully compliant with Brazilian Normative Instruction RFB No. 2,229/2024.
 * Supports both traditional numeric CNPJs and new alphanumeric CNPJs starting July 2026.
 */
class CnpjRule extends FormRule
{
    /**
     * Tests if the given value is a valid CNPJ.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value.
     * @param   ?Registry          $input    An optional Registry object with the entire data set.
     * @param   ?Form              $form     The form object for which the field is being tested.
     *
     * @return  boolean  True if valid, false otherwise.
     */
    public function test(\SimpleXMLElement $element, $value, $group = null, ?Registry $input = null, ?Form $form = null)
    {
        $required = ((string) $element['required'] === 'true' || (string) $element['required'] === 'required');

        if (!$required && ($value === '' || $value === null)) {
            return true;
        }

        $allowAlphanumeric = true;
        if (isset($element['allow_alphanumeric']) && (string) $element['allow_alphanumeric'] === '0') {
            $allowAlphanumeric = false;
        }

        return self::validate((string) $value, $allowAlphanumeric);
    }

    /**
     * Universal validation for numeric and alphanumeric CNPJ according to RFB IN 2,229/2024.
     *
     * @param   string  $cnpj               Raw or formatted CNPJ string.
     * @param   bool    $allowAlphanumeric  Whether alphanumeric characters are allowed.
     * @return  boolean True if mathematically valid, false otherwise.
     */
    public static function validate(string $cnpj, bool $allowAlphanumeric = true): bool
    {
        // Remove punctuation and whitespace, converting to uppercase
        $clean = strtoupper(trim(preg_replace('/[^a-zA-Z0-9]/', '', $cnpj)));

        // Must contain exactly 14 characters
        if (strlen($clean) !== 14) {
            return false;
        }

        // If alphanumeric is disabled, accept only digits
        if (!$allowAlphanumeric && !ctype_digit($clean)) {
            return false;
        }

        // The last 2 positions (check digits) are ALWAYS strictly numeric (0-9)
        if (!ctype_digit(substr($clean, 12, 2))) {
            return false;
        }

        // Reject uniform repeated sequences (e.g. 00000000000000, 11111111111111, etc.)
        if (preg_match('/^([0-9a-zA-Z])\1{13}$/', $clean)) {
            return false;
        }

        // Weights for the 1st Check Digit
        $weights1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            // ASCII conversion according to RFB IN 2,229/2024: ord(char) - 48
            // Digits '0'-'9': ASCII 48-57 -> values 0 to 9
            // Letters 'A'-'Z': ASCII 65-90 -> values 17 to 42
            $val = ord($clean[$i]) - 48;
            $sum += $val * $weights1[$i];
        }

        $remainder = $sum % 11;
        $dv1 = ($remainder < 2) ? 0 : (11 - $remainder);

        if ((int) $clean[12] !== $dv1) {
            return false;
        }

        // Weights for the 2nd Check Digit (including the 1st calculated DV)
        $weights2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $val = ord($clean[$i]) - 48;
            $sum += $val * $weights2[$i];
        }
        $sum += $dv1 * 2;

        $remainder = $sum % 11;
        $dv2 = ($remainder < 2) ? 0 : (11 - $remainder);

        return (int) $clean[13] === $dv2;
    }

    /**
     * Formats the CNPJ string into standard XX.XXX.XXX/XXXX-XX layout.
     *
     * @param   string  $cnpj  Raw or unformatted CNPJ.
     * @return  string  Standard formatted CNPJ.
     */
    public static function format(string $cnpj): string
    {
        $clean = strtoupper(trim(preg_replace('/[^a-zA-Z0-9]/', '', $cnpj)));

        if (strlen($clean) !== 14) {
            return $cnpj;
        }

        return sprintf(
            '%s.%s.%s/%s-%s',
            substr($clean, 0, 2),
            substr($clean, 2, 3),
            substr($clean, 5, 3),
            substr($clean, 8, 4),
            substr($clean, 12, 2)
        );
    }

    /**
     * Masks the CNPJ string for LGPD data privacy compliance (e.g. masked root and branch).
     *
     * @param   string  $cnpj  Raw or formatted CNPJ.
     * @return  string  LGPD-compliant masked CNPJ.
     */
    public static function maskLgpd(string $cnpj): string
    {
        $clean = strtoupper(trim(preg_replace('/[^a-zA-Z0-9]/', '', $cnpj)));

        if (strlen($clean) === 14) {
            return '**.***.***/****-' . substr($clean, 12, 2);
        }

        return $cnpj;
    }
}
