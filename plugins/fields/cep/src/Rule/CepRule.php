<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Cep
 *
 * @copyright   (C) 2026 Uziel Almeida Oliveira <https://github.com/uzielweb>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Uziel\Plugin\Fields\Cep\Rule;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;

\defined('_JEXEC') or die;

/**
 * Form Rule class for Brazilian Postal Code (CEP) validation.
 */
class CepRule extends FormRule
{
    /**
     * Tests if the given value is a valid CEP.
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
     * Validates Brazilian CEP (must have exactly 8 numeric digits).
     *
     * @param   string  $cep  Raw or formatted CEP string.
     * @return  boolean True if valid, false otherwise.
     */
    public static function validate(string $cep): bool
    {
        $clean = preg_replace('/\D/', '', $cep);

        if (strlen($clean) !== 8) {
            return false;
        }

        // Check if it is not a dummy repetitive pattern
        if ($clean === '00000000') {
            return false;
        }

        return true;
    }

    /**
     * Formats 8 digits clean CEP into 00000-000.
     *
     * @param   string  $cep  Raw or formatted CEP.
     * @return  string  Standard formatted CEP.
     */
    public static function format(string $cep): string
    {
        $clean = preg_replace('/\D/', '', $cep);

        if (strlen($clean) !== 8) {
            return $cep;
        }

        return preg_replace('/(\d{5})(\d{3})/', '$1-$2', $clean);
    }
}
