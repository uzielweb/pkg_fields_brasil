<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Cnpj
 *
 * @copyright   (C) 2026 Uziel Almeida Oliveira <https://github.com/uzielweb>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Uziel\Plugin\Fields\Cnpj\Extension;

use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;
use Joomla\Component\Fields\Administrator\Plugin\FieldsPlugin;
use Joomla\Event\SubscriberInterface;

\defined('_JEXEC') or die;

/**
 * Fields CNPJ Plugin (Compliant with RFB IN 2,229/2024)
 *
 * @since 1.0.0
 */
final class Cnpj extends FieldsPlugin implements SubscriberInterface
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
        FormHelper::addRulePath(JPATH_PLUGINS . '/fields/cnpj/rules');
        FormHelper::addRulePrefix('Uziel\\Plugin\\Fields\\Cnpj\\Rule');

        // Configure validation and UX attributes
        $fieldNode->setAttribute('validate', 'cnpj');

        $currentClass = $fieldNode->getAttribute('class');
        $fieldNode->setAttribute('class', trim($currentClass . ' validate-cnpj joomla-field-cnpj'));

        $applyMask = $field->fieldparams ? $field->fieldparams->get('apply_mask', '1') : '1';
        $fieldNode->setAttribute('data-apply-mask', (string) $applyMask);

        $allowAlphanumeric = $field->fieldparams ? $field->fieldparams->get('allow_alphanumeric', '1') : '1';
        $fieldNode->setAttribute('data-allow-alphanumeric', (string) $allowAlphanumeric);
        $fieldNode->setAttribute('allow_alphanumeric', (string) $allowAlphanumeric);

        $fieldNode->setAttribute('maxlength', '18');

        if (!$fieldNode->getAttribute('hint')) {
            $fieldNode->setAttribute('hint', '00.000.000/0000-00');
        }

        // Safely load frontend/admin assets via Web Asset Manager
        $app = $this->getApplication();
        if ($app && $app->getDocument() instanceof HtmlDocument) {
            $wa = $app->getDocument()->getWebAssetManager();
            $wa->registerAndUseScript(
                'fields.cnpj',
                'plg_fields_cnpj/cnpj.js',
                ['version' => '1.0.0'],
                ['defer' => true],
                ['core', 'form.validate']
            );
        }

        return $fieldNode;
    }
}
