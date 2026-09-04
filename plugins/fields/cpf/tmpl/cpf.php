<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Cpf
 *
 * @copyright   (C) 2026 Uziel Almeida Oliveira <https://github.com/uzielweb>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

\defined('_JEXEC') or die;

use Uziel\Plugin\Fields\Cpf\Rule\CpfRule;

$value = (string) $field->value;

if ($value === '') {
    return;
}

$format = $fieldParams->get('display_format', 'formatted');

switch ($format) {
    case 'unformatted':
        $output = preg_replace('/\D/', '', $value);
        break;

    case 'lgpd':
        $output = CpfRule::maskLgpd($value);
        break;

    case 'formatted':
    default:
        $output = CpfRule::format($value);
        break;
}

echo '<span class="field-cpf" data-field-id="' . (int) $field->id . '">' . htmlspecialchars($output, ENT_QUOTES, 'UTF-8') . '</span>';
