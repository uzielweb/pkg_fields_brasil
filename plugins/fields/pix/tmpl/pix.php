<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Pix
 *
 * @copyright   (C) 2026 Uziel Almeida Oliveira <https://github.com/uzielweb>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Uziel\Plugin\Fields\Pix\Helper\PixHelper;

$key = trim((string) $field->value);

if ($key === '') {
    return;
}

$merchantName = (string) $fieldParams->get('merchant_name', 'BENEFICIARIO');
$merchantCity = (string) $fieldParams->get('merchant_city', 'SAO PAULO');
$amountMode   = (string) $fieldParams->get('amount_mode', 'free');
$defaultAmount = (float) $fieldParams->get('default_amount', 0.00);
$txid         = (string) $fieldParams->get('txid', '***');
$showQrCode   = (bool) $fieldParams->get('show_qrcode', 1);
$showCopyBtn  = (bool) $fieldParams->get('show_copy_button', 1);

$initialAmount = null;
if ($amountMode === 'fixed' && $defaultAmount > 0) {
    $initialAmount = $defaultAmount;
} elseif ($amountMode === 'free' && $defaultAmount > 0) {
    $initialAmount = $defaultAmount;
}

$initialPayload = PixHelper::generatePayload($key, $merchantName, $merchantCity, $initialAmount, $txid);
$keyType        = PixHelper::getKeyType($key) ?: 'pix';
?>
<div class="field-pix-card card p-3 shadow-sm my-2" data-field-id="<?php echo (int) $field->id; ?>"
     data-pix-key="<?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>"
     data-merchant-name="<?php echo htmlspecialchars($merchantName, ENT_QUOTES, 'UTF-8'); ?>"
     data-merchant-city="<?php echo htmlspecialchars($merchantCity, ENT_QUOTES, 'UTF-8'); ?>"
     data-txid="<?php echo htmlspecialchars($txid, ENT_QUOTES, 'UTF-8'); ?>"
     data-amount-mode="<?php echo htmlspecialchars($amountMode, ENT_QUOTES, 'UTF-8'); ?>"
     data-payload="<?php echo htmlspecialchars($initialPayload, ENT_QUOTES, 'UTF-8'); ?>">

    <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="badge bg-primary text-uppercase"><?php echo htmlspecialchars($keyType, ENT_QUOTES, 'UTF-8'); ?></span>
        <span class="text-muted small"><?php echo htmlspecialchars($merchantName, ENT_QUOTES, 'UTF-8'); ?></span>
    </div>

    <div class="pix-key-display mb-3">
        <span class="fw-bold"><?php echo Text::_('PLG_FIELDS_PIX_KEY_LABEL'); ?>:</span>
        <code><?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?></code>
    </div>

    <?php if ($amountMode === 'free') : ?>
        <div class="mb-3 pix-amount-box">
            <label class="form-label small fw-semibold"><?php echo Text::_('PLG_FIELDS_PIX_ENTER_AMOUNT_LABEL'); ?></label>
            <div class="input-group input-group-sm">
                <span class="input-group-text">R$</span>
                <input type="number" class="form-control pix-amount-input" step="0.01" min="0"
                       value="<?php echo $defaultAmount > 0 ? htmlspecialchars(number_format($defaultAmount, 2, '.', ''), ENT_QUOTES, 'UTF-8') : ''; ?>"
                       placeholder="0.00" aria-label="<?php echo Text::_('PLG_FIELDS_PIX_AMOUNT_LABEL'); ?>">
            </div>
        </div>
    <?php elseif ($amountMode === 'fixed' && $defaultAmount > 0) : ?>
        <div class="mb-2 pix-fixed-amount text-success fw-bold">
            <?php echo Text::_('PLG_FIELDS_PIX_AMOUNT_LABEL'); ?>: R$ <?php echo number_format($defaultAmount, 2, ',', '.'); ?>
        </div>
    <?php endif; ?>

    <?php if ($showQrCode) : ?>
        <div class="pix-qrcode-wrapper text-center my-2 p-2 bg-light rounded">
            <div class="pix-qrcode-container" style="min-height: 180px; display: flex; align-items: center; justify-content: center;"></div>
            <small class="text-muted d-block mt-1"><?php echo Text::_('PLG_FIELDS_PIX_SCAN_INSTRUCTION'); ?></small>
        </div>
    <?php endif; ?>

    <?php if ($showCopyBtn) : ?>
        <div class="pix-copy-section mt-2">
            <div class="input-group input-group-sm mb-2">
                <input type="text" class="form-control pix-payload-input" readonly
                       value="<?php echo htmlspecialchars($initialPayload, ENT_QUOTES, 'UTF-8'); ?>">
                <button type="button" class="btn btn-outline-primary btn-copy-pix"
                        title="<?php echo Text::_('PLG_FIELDS_PIX_COPY_BUTTON'); ?>">
                    <span class="icon-copy" aria-hidden="true"></span> <?php echo Text::_('PLG_FIELDS_PIX_COPY_BUTTON'); ?>
                </button>
            </div>
            <span class="pix-copy-feedback text-success small d-none"><?php echo Text::_('PLG_FIELDS_PIX_COPIED_FEEDBACK'); ?></span>
        </div>
    <?php endif; ?>
</div>
