<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Cpf
 *
 * @copyright   (C) 2026 Uziel Almeida Oliveira <https://github.com/uzielweb>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Uziel\Plugin\Fields\Cpf\Rule;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;

\defined('_JEXEC') or die;

/**
 * Form Rule class for CPF validation.
 * In compliance with Brazilian Federal Law No. 14,534/2023 and the official Modulo 11 algorithm from Receita Federal.
 */
class CpfRule extends FormRule
{
    /**
     * Tests if the given value is a valid CPF.
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
     * Validates a CPF using Modulo 11 check digits calculation.
     *
     * @param   string  $cpf  Raw or formatted CPF string.
     * @return  boolean True if the CPF is mathematically valid, false otherwise.
     */
    public static function validate(string $cpf): bool
    {
        // Strip punctuation and non-digit characters
        $clean = preg_replace('/\D/', '', $cpf);

        // Must have exactly 11 digits
        if (strlen($clean) !== 11) {
            return false;
        }

        // Reject repeated sequences (e.g. 000.000.000-00, 111.111.111-11, etc.)
        if (preg_match('/^(\d)\1{10}$/', $clean)) {
            return false;
        }

        // Calculate 1st Check Digit (weights from 10 down to 2)
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += ((int) $clean[$i]) * (10 - $i);
        }
        $remainder = $sum % 11;
        $dv1 = ($remainder < 2) ? 0 : (11 - $remainder);

        if ((int) $clean[9] !== $dv1) {
            return false;
        }

        // Calculate 2nd Check Digit (weights from 11 down to 2)
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += ((int) $clean[$i]) * (11 - $i);
        }
        $remainder = $sum % 11;
        $dv2 = ($remainder < 2) ? 0 : (11 - $remainder);

        return (int) $clean[10] === $dv2;
    }

    /**
     * Formats an 11-digit clean CPF into 000.000.000-00.
     *
     * @param   string  $cpf  Raw digits or formatted string.
     * @return  string  Standard formatted CPF.
     */
    public static function format(string $cpf): string
    {
        $clean = preg_replace('/\D/', '', $cpf);

        if (strlen($clean) !== 11) {
            return $cpf;
        }

        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $clean);
    }

    /**
     * Masks the CPF string for LGPD data privacy compliance (e.g. ***.456.789-**).
     *
     * @param   string  $cpf  Raw or formatted CPF.
     * @return  string  LGPD-compliant masked CPF.
     */
    public static function maskLgpd(string $cpf): string
    {
        $formatted = self::format($cpf);

        if (strlen($formatted) === 14) {
            return '***.' . substr($formatted, 4, 7) . '-**';
        }

        return $cpf;
    }
}
