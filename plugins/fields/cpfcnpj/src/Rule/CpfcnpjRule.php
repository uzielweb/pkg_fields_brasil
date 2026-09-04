<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Cpfcnpj
 *
 * @copyright   (C) 2026 Uziel Almeida Oliveira <https://github.com/uzielweb>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Uziel\Plugin\Fields\Cpfcnpj\Rule;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;
use Uziel\Plugin\Fields\Cpf\Rule\CpfRule;
use Uziel\Plugin\Fields\Cnpj\Rule\CnpjRule;

\defined('_JEXEC') or die;

/**
 * Form Rule class for Hybrid CPF / CNPJ validation.
 */
class CpfcnpjRule extends FormRule
{
    /**
     * Tests if the given value is a valid CPF or CNPJ.
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
     * Contextual validation: detects CPF or CNPJ and executes appropriate Modulo 11 verification.
     *
     * @param   string  $value
     * @return  boolean
     */
    public static function validate(string $value): bool
    {
        $clean = strtoupper(trim(preg_replace('/[^a-zA-Z0-9]/', '', $value)));
        $len   = strlen($clean);

        if ($len <= 11) {
            return CpfRule::validate($value);
        }

        if ($len === 14) {
            return CnpjRule::validate($value, true);
        }

        return false;
    }

    /**
     * Formats either CPF or CNPJ based on length.
     *
     * @param   string  $value
     * @return  string
     */
    public static function format(string $value): string
    {
        $clean = strtoupper(trim(preg_replace('/[^a-zA-Z0-9]/', '', $value)));
        $len   = strlen($clean);

        if ($len <= 11) {
            return CpfRule::format($value);
        }

        return CnpjRule::format($value);
    }

    /**
     * Masks either CPF or CNPJ for LGPD privacy.
     *
     * @param   string  $value
     * @return  string
     */
    public static function maskLgpd(string $value): string
    {
        $clean = strtoupper(trim(preg_replace('/[^a-zA-Z0-9]/', '', $value)));
        $len   = strlen($clean);

        if ($len <= 11) {
            return CpfRule::maskLgpd($value);
        }

        return CnpjRule::maskLgpd($value);
    }
}
