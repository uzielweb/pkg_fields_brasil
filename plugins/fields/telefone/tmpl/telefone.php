<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Telefone
 *
 * @copyright   (C) 2026 Uziel Almeida Oliveira <https://github.com/uzielweb>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Uziel\Plugin\Fields\Telefone\Rule\TelefoneRule;

$value = (string) $field->value;

if ($value === '') {
    return;
}

$clean = preg_replace('/\D/', '', $value);
$formatted = TelefoneRule::format($value);
$displayType = $fieldParams->get('display_type', 'whatsapp_btn');

switch ($displayType) {
    case 'tel_link':
        echo sprintf(
            '<span class="field-telefone" data-field-id="%d"><a href="tel:+55%s" class="telefone-link"><span class="icon-phone" aria-hidden="true"></span> %s</a></span>',
            (int) $field->id,
            htmlspecialchars($clean, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($formatted, ENT_QUOTES, 'UTF-8')
        );
        break;

    case 'whatsapp_btn':
        $btnClass = $fieldParams->get('whatsapp_btn_class', 'btn btn-success btn-sm');
        $msg = (string) $fieldParams->get('whatsapp_message', '');
        $waUrl = 'https://wa.me/55' . $clean;

        if ($msg !== '') {
            $waUrl .= '?text=' . rawurlencode($msg);
        }

        echo sprintf(
            '<span class="field-telefone-whatsapp" data-field-id="%d"><a href="%s" target="_blank" rel="noopener noreferrer" class="%s"><span class="icon-comment" aria-hidden="true"></span> %s</a></span>',
            (int) $field->id,
            htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($btnClass, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($formatted, ENT_QUOTES, 'UTF-8')
        );
        break;

    case 'formatted':
    default:
        echo '<span class="field-telefone" data-field-id="' . (int) $field->id . '">' . htmlspecialchars($formatted, ENT_QUOTES, 'UTF-8') . '</span>';
        break;
}
