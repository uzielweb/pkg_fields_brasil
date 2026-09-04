<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Pix
 *
 * @copyright   (C) 2026 Uziel Almeida Oliveira <https://github.com/uzielweb>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Uziel\Plugin\Fields\Pix\Extension;

use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;
use Joomla\Component\Fields\Administrator\Plugin\FieldsPlugin;
use Joomla\Event\SubscriberInterface;

\defined('_JEXEC') or die;

/**
 * Fields Pix Plugin (QR Code & Copia e Cola)
 *
 * @since 1.0.0
 */
final class Pix extends FieldsPlugin implements SubscriberInterface
{
    /**
     * Transforms the field into a DOM XML element and appends it as a child on the given parent.
     *
     * @param   \stdClass    $field   The field.
     * @param   \DOMElement  $parent  The field node parent.
     * @param   Form         $form    The form.
     *
     * @return  ?\DOMElement
     */
    public function onCustomFieldsPrepareDom($field, \DOMElement $parent, Form $form)
    {
        $fieldNode = parent::onCustomFieldsPrepareDom($field, $parent, $form);

        if (!$fieldNode) {
            return $fieldNode;
        }

        // Register custom validation rules in the form
        FormHelper::addRulePath(JPATH_PLUGINS . '/fields/pix/rules');
        FormHelper::addRulePrefix('Uziel\\Plugin\\Fields\\Pix\\Rule');

        // Configure validation and UX attributes
        $fieldNode->setAttribute('validate', 'pix');

        $currentClass = $fieldNode->getAttribute('class');
        $fieldNode->setAttribute('class', trim($currentClass . ' validate-pix joomla-field-pix'));

        if (!$fieldNode->getAttribute('hint')) {
            $fieldNode->setAttribute('hint', 'CPF, CNPJ, E-mail, Celular ou Chave Aleatória');
        }

        // Safely load frontend/admin assets via Web Asset Manager
        $app = $this->getApplication();
        if ($app && $app->getDocument() instanceof HtmlDocument) {
            $wa = $app->getDocument()->getWebAssetManager();
            $wa->registerAndUseScript(
                'fields.pix',
                'plg_fields_pix/pix.js',
                ['version' => '1.0.0'],
                ['defer' => true],
                ['core', 'form.validate']
            );
        }

        return $fieldNode;
    }
}
