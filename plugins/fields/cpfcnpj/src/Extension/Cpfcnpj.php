<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Cpfcnpj
 *
 * @copyright   (C) 2026 Uziel Almeida Oliveira <https://github.com/uzielweb>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Uziel\Plugin\Fields\Cpfcnpj\Extension;

use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;
use Joomla\Component\Fields\Administrator\Plugin\FieldsPlugin;
use Joomla\Event\SubscriberInterface;

\defined('_JEXEC') or die;

/**
 * Fields Hybrid CPF / CNPJ Plugin
 *
 * @since 1.0.0
 */
final class Cpfcnpj extends FieldsPlugin implements SubscriberInterface
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
        FormHelper::addRulePath(JPATH_PLUGINS . '/fields/cpfcnpj/rules');
        FormHelper::addRulePrefix('Uziel\\Plugin\\Fields\\Cpfcnpj\\Rule');

        // Configure validation and UX attributes
        $fieldNode->setAttribute('validate', 'cpfcnpj');

        $currentClass = $fieldNode->getAttribute('class');
        $fieldNode->setAttribute('class', trim($currentClass . ' validate-cpfcnpj joomla-field-cpfcnpj'));

        $applyMask = $field->fieldparams ? $field->fieldparams->get('apply_mask', '1') : '1';
        $fieldNode->setAttribute('data-apply-mask', (string) $applyMask);
        $fieldNode->setAttribute('maxlength', '18');

        if (!$fieldNode->getAttribute('hint')) {
            $fieldNode->setAttribute('hint', 'CPF ou CNPJ');
        }

        // Safely load frontend/admin assets via Web Asset Manager
        $app = $this->getApplication();
        if ($app && $app->getDocument() instanceof HtmlDocument) {
            $wa = $app->getDocument()->getWebAssetManager();
            $wa->registerAndUseScript(
                'fields.cpfcnpj',
                'plg_fields_cpfcnpj/cpfcnpj.js',
                ['version' => '1.0.0'],
                ['defer' => true],
                ['core', 'form.validate']
            );
        }

        return $fieldNode;
    }
}
