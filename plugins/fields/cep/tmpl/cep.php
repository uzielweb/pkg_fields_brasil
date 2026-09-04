<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Cep
 *
 * @copyright   (C) 2026 Uziel Almeida Oliveira <https://github.com/uzielweb>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Uziel\Plugin\Fields\Cep\Rule\CepRule;

$value = (string) $field->value;

if ($value === '') {
    return;
}

$formatted = CepRule::format($value);
$showMap   = $fieldParams->get('show_map', 0);

if ($showMap) {
    $cleanDigits = preg_replace('/\D/', '', $value);
    $mapUrl      = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($cleanDigits . ', Brasil');

    echo sprintf(
        '<span class="field-cep" data-field-id="%d"><a href="%s" target="_blank" rel="noopener noreferrer" class="cep-map-link"><span class="icon-location" aria-hidden="true"></span> %s</a></span>',
        (int) $field->id,
        htmlspecialchars($mapUrl, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($formatted, ENT_QUOTES, 'UTF-8')
    );
} else {
    echo '<span class="field-cep" data-field-id="' . (int) $field->id . '">' . htmlspecialchars($formatted, ENT_QUOTES, 'UTF-8') . '</span>';
}
