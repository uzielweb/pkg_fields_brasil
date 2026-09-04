<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Cpfcnpj
 *
 * @copyright   (C) 2026 Uziel Almeida Oliveira <https://github.com/uzielweb>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

\defined('_JEXEC') or die;

use Uziel\Plugin\Fields\Cpfcnpj\Rule\CpfcnpjRule;

$value = (string) $field->value;

if ($value === '') {
    return;
}

$format = $fieldParams->get('display_format', 'formatted');

switch ($format) {
    case 'unformatted':
        $output = strtoupper(trim(preg_replace('/[^a-zA-Z0-9]/', '', $value)));
        break;

    case 'lgpd':
        $output = CpfcnpjRule::maskLgpd($value);
        break;

    case 'formatted':
    default:
        $output = CpfcnpjRule::format($value);
        break;
}

echo '<span class="field-cpfcnpj" data-field-id="' . (int) $field->id . '">' . htmlspecialchars($output, ENT_QUOTES, 'UTF-8') . '</span>';
