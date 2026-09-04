<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Telefone
 *
 * @copyright   (C) 2026 Uziel Almeida Oliveira <https://github.com/uzielweb>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Uziel\Plugin\Fields\Telefone\Rule;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;

\defined('_JEXEC') or die;

/**
 * Form Rule class for Brazilian Phone & Mobile numbers with area code (DDD) validation.
 */
class TelefoneRule extends FormRule
{
    /**
     * List of valid Brazilian area codes (DDDs) according to Anatel.
     *
     * @var int[]
     */
    public static array $validDdds = [
        11, 12, 13, 14, 15, 16, 17, 18, 19,
        21, 22, 24, 27, 28,
        31, 32, 33, 34, 35, 37, 38,
        41, 42, 43, 44, 45, 46, 47, 48, 49,
        51, 53, 54, 55,
        61, 62, 63, 64, 65, 66, 67, 68, 69,
        71, 73, 74, 75, 77, 79,
        81, 82, 83, 84, 85, 86, 87, 88, 89,
        91, 92, 93, 94, 95, 96, 97, 98, 99
    ];

    /**
     * Tests if the given value is a valid Brazilian phone number.
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

        return self::validate((string) $value);
    }

    /**
     * Validates Brazilian landline (10 digits) or mobile (11 digits with 9th digit) numbers.
     *
     * @param   string  $phone  Raw or formatted phone string.
     * @return  boolean True if valid, false otherwise.
     */
    public static function validate(string $phone): bool
    {
        $clean = preg_replace('/\D/', '', $phone);
        $len   = strlen($clean);

        if ($len !== 10 && $len !== 11) {
            return false;
        }

        // Reject uniform repetitive sequences
        if (preg_match('/^(\d)\1+$/', $clean)) {
            return false;
        }

        // Extract and validate DDD
        $ddd = (int) substr($clean, 0, 2);
        if (!in_array($ddd, self::$validDdds, true)) {
            return false;
        }

        // 11 digits: mobile number MUST begin with 9
        if ($len === 11 && $clean[2] !== '9') {
            return false;
        }

        // 10 digits: landline typically begins with 2, 3, 4, or 5
        if ($len === 10 && !in_array($clean[2], ['2', '3', '4', '5'], true)) {
            return false;
        }

        return true;
    }

    /**
     * Formats phone number into (XX) XXXX-XXXX or (XX) XXXXX-XXXX.
     *
     * @param   string  $phone
     * @return  string
     */
    public static function format(string $phone): string
    {
        $clean = preg_replace('/\D/', '', $phone);
        $len   = strlen($clean);

        if ($len === 11) {
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $clean);
        }

        if ($len === 10) {
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $clean);
        }

        return $phone;
    }
}
