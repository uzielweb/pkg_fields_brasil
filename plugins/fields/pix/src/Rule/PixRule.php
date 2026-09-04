<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Pix
 *
 * @copyright   (C) 2026 Uziel Almeida Oliveira <https://github.com/uzielweb>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Uziel\Plugin\Fields\Pix\Rule;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;
use Uziel\Plugin\Fields\Pix\Helper\PixHelper;
use Uziel\Plugin\Fields\Cpf\Rule\CpfRule;
use Uziel\Plugin\Fields\Cnpj\Rule\CnpjRule;

\defined('_JEXEC') or die;

/**
 * Form Rule class for Pix Key validation.
 */
class PixRule extends FormRule
{
    /**
     * Tests if the given value is a valid Pix key.
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
     * Validates whether the key matches one of the 4 official Brazilian Pix key types.
     *
     * @param   string  $key
     * @return  boolean
     */
    public static function validate(string $key): bool
    {
        $type = PixHelper::getKeyType($key);

        if (!$type) {
            return false;
        }

        switch ($type) {
            case 'cpf':
                return CpfRule::validate($key);

            case 'cnpj':
                return CnpjRule::validate($key, true);

            case 'email':
                return (bool) filter_var(trim($key), FILTER_VALIDATE_EMAIL);

            case 'phone':
            case 'evp':
                return true;

            default:
                return false;
        }
    }
}
