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
use Joomla\Registry\Registry;

\defined('_JEXEC') or die;

/**
 * Fields Pix Plugin (QR Code & Copia e Cola - Multicampo Subform)
 *
 * @since 1.0.0
 */
final class Pix extends FieldsPlugin implements SubscriberInterface
{
    /**
     * Returns the configured params for a given field merged with plugin defaults.
     *
     * @param   object  $field  The field object.
     *
     * @return  Registry
     */
    public function getParamsFromField($field): Registry
    {
        $params = (isset($this->params) && $this->params instanceof Registry)
            ? clone $this->params
            : new Registry($this->params ?? null);

        if (isset($field->fieldparams)) {
            if ($field->fieldparams instanceof Registry) {
                $params->merge($field->fieldparams);
            } elseif (\is_string($field->fieldparams) || \is_array($field->fieldparams)) {
                $params->merge(new Registry($field->fieldparams));
            }
        } elseif (isset($field->params)) {
            if ($field->params instanceof Registry) {
                $params->merge($field->params);
            } elseif (\is_string($field->params) || \is_array($field->params)) {
                $params->merge(new Registry($field->params));
            }
        }

        return $params;
    }

    /**
     * Transforms the field into a Subform DOM element with structured Pix fields.
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

        $fieldParams  = $this->getParamsFromField($field);
        $isRepeatable = (bool) $fieldParams->get('repeat', 0);

        // Configure as a Subform (composite multicampo)
        $fieldNode->setAttribute('type', 'subform');
        $fieldNode->setAttribute('formsource', 'plugins/fields/pix/forms/pix_subform.xml');
        $fieldNode->setAttribute('multiple', $isRepeatable ? 'true' : 'false');
        $fieldNode->setAttribute(
            'layout',
            $isRepeatable ? 'joomla.form.field.subform.repeatable-table' : 'joomla.form.field.subform.default'
        );

        if ($field->required) {
            $fieldNode->setAttribute('min', '1');
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
