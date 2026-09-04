<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Cep
 *
 * @copyright   (C) 2026 Uziel Almeida Oliveira <https://github.com/uzielweb>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Uziel\Plugin\Fields\Cep\Extension;

use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;
use Joomla\Component\Fields\Administrator\Plugin\FieldsPlugin;
use Joomla\Event\SubscriberInterface;

\defined('_JEXEC') or die;

/**
 * Fields CEP Plugin
 *
 * @since 1.0.0
 */
final class Cep extends FieldsPlugin implements SubscriberInterface
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
        FormHelper::addRulePath(JPATH_PLUGINS . '/fields/cep/rules');
        FormHelper::addRulePrefix('Uziel\\Plugin\\Fields\\Cep\\Rule');

        // Configure validation and UX attributes
        $fieldNode->setAttribute('validate', 'cep');

        $currentClass = $fieldNode->getAttribute('class');
        $fieldNode->setAttribute('class', trim($currentClass . ' validate-cep joomla-field-cep'));

        $applyMask = $field->fieldparams ? $field->fieldparams->get('apply_mask', '1') : '1';
        $fieldNode->setAttribute('data-apply-mask', (string) $applyMask);

        $enableLookup = $field->fieldparams ? $field->fieldparams->get('enable_lookup', '1') : '1';
        $fieldNode->setAttribute('data-enable-lookup', (string) $enableLookup);

        // Target field mappings for auto-fill
        if ($field->fieldparams) {
            $fieldNode->setAttribute('data-target-street', (string) $field->fieldparams->get('target_street', ''));
            $fieldNode->setAttribute('data-target-neighborhood', (string) $field->fieldparams->get('target_neighborhood', ''));
            $fieldNode->setAttribute('data-target-city', (string) $field->fieldparams->get('target_city', ''));
            $fieldNode->setAttribute('data-target-state', (string) $field->fieldparams->get('target_state', ''));
            $fieldNode->setAttribute('data-target-complement', (string) $field->fieldparams->get('target_complement', ''));
        }

        $fieldNode->setAttribute('maxlength', '9');

        if (!$fieldNode->getAttribute('hint')) {
            $fieldNode->setAttribute('hint', '00000-000');
        }

        // Safely load frontend/admin assets via Web Asset Manager
        $app = $this->getApplication();
        if ($app && $app->getDocument() instanceof HtmlDocument) {
            $wa = $app->getDocument()->getWebAssetManager();
            $wa->registerAndUseScript(
                'fields.cep',
                'plg_fields_cep/cep.js',
                ['version' => '1.0.0'],
                ['defer' => true],
                ['core', 'form.validate']
            );
        }

        return $fieldNode;
    }
}
