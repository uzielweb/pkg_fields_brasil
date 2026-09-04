<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Pix
 *
 * @copyright   (C) 2026 Uziel Almeida Oliveira <https://github.com/uzielweb>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Uziel\Plugin\Fields\Pix\Helper\PixHelper;

$rawValue = $field->value;

// Normalize raw value (string JSON, array, or legacy single string)
$decoded = null;
if (\is_string($rawValue)) {
    $trimmed = trim($rawValue);
    if ($trimmed === '') {
        return;
    }

    if (str_starts_with($trimmed, '{') || str_starts_with($trimmed, '[')) {
        $decoded = json_decode($trimmed, true);
    }

    if (!\is_array($decoded)) {
        // Legacy single key string fallback
        $decoded = [['pix_key' => $trimmed]];
    }
} elseif (\is_array($rawValue)) {
    $decoded = $rawValue;
}

if (empty($decoded) || !\is_array($decoded)) {
    return;
}

// Check if array is associative (single item) or list of rows
$items = [];
$firstKey = array_key_first($decoded);
if ($firstKey !== null && \is_array($decoded[$firstKey])) {
    // Repeatable rows (e.g. ['row0' => [...], 'row1' => [...]])
    $items = array_values($decoded);
} else {
    // Single row
    $items = [$decoded];
}

$defaultMerchantName = (string) $fieldParams->get('merchant_name', 'BENEFICIARIO');
$defaultMerchantCity = (string) $fieldParams->get('merchant_city', 'SAO PAULO');
$defaultAmountMode   = (string) $fieldParams->get('amount_mode', 'none');
$defaultAmount       = (float) $fieldParams->get('default_amount', 0.00);
$defaultTxid         = (string) $fieldParams->get('txid', '***');
$defaultLogoType     = (string) $fieldParams->get('qrcode_logo_type', 'pix');
$defaultCustomLogo   = (string) $fieldParams->get('qrcode_logo', '');
$showQrCode          = (bool) $fieldParams->get('show_qrcode', 1);
$showCopyBtn         = (bool) $fieldParams->get('show_copy_button', 1);

foreach ($items as $item) :
    if (!\is_array($item)) {
        continue;
    }

    $key = trim((string) ($item['pix_key'] ?? $item['key'] ?? ''));

    if ($key === '') {
        continue;
    }

    $merchantName = trim((string) ($item['merchant_name'] ?? '')) ?: $defaultMerchantName;
    $merchantCity = trim((string) ($item['merchant_city'] ?? '')) ?: $defaultMerchantCity;
    $amountMode   = (string) ($item['amount_mode'] ?? $defaultAmountMode);
    $itemAmount   = isset($item['amount']) && $item['amount'] !== '' ? (float) $item['amount'] : $defaultAmount;
    $txid         = trim((string) ($item['txid'] ?? '')) ?: $defaultTxid;
    $description  = trim((string) ($item['description'] ?? ''));

    // Resolve QR Code center logo (per item or field default fallback)
    $itemLogoType      = (string) ($item['qrcode_logo_type'] ?? 'inherit');
    $effectiveLogoType = ($itemLogoType === 'inherit' || $itemLogoType === '') ? $defaultLogoType : $itemLogoType;
    $logoUrl           = '';

    if ($effectiveLogoType === 'pix') {
        $logoUrl = Uri::root(true) . '/media/plg_fields_pix/images/pix-icon.svg';
    } elseif ($effectiveLogoType === 'custom') {
        $rawLogo = trim((string) ($item['qrcode_logo'] ?? ''));
        if ($rawLogo === '') {
            $rawLogo = $defaultCustomLogo;
        }

        if ($rawLogo !== '') {
            $clean = HTMLHelper::_('cleanImageURL', $rawLogo);
            $url   = $clean->url ?? '';
            if ($url) {
                $logoUrl = (str_starts_with($url, 'http://') || str_starts_with($url, 'https://'))
                    ? $url
                    : Uri::root(true) . '/' . ltrim($url, '/');
            }
        }
    }

    $initialAmount = null;
    if ($amountMode === 'fixed' && $itemAmount > 0) {
        $initialAmount = $itemAmount;
    } elseif ($amountMode === 'free' && $itemAmount > 0) {
        $initialAmount = $itemAmount;
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
     data-logo-url="<?php echo htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8'); ?>"
     data-payload="<?php echo htmlspecialchars($initialPayload, ENT_QUOTES, 'UTF-8'); ?>">

    <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="badge bg-primary text-uppercase"><?php echo htmlspecialchars($keyType, ENT_QUOTES, 'UTF-8'); ?></span>
        <span class="text-muted small"><?php echo htmlspecialchars($merchantName, ENT_QUOTES, 'UTF-8'); ?></span>
    </div>

    <?php if ($description !== '') : ?>
        <div class="pix-description text-muted small mb-2">
            <span class="fw-semibold"><?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
    <?php endif; ?>

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
                       value="<?php echo $itemAmount > 0 ? htmlspecialchars(number_format($itemAmount, 2, '.', ''), ENT_QUOTES, 'UTF-8') : ''; ?>"
                       placeholder="0.00" aria-label="<?php echo Text::_('PLG_FIELDS_PIX_AMOUNT_LABEL'); ?>">
            </div>
        </div>
    <?php elseif ($amountMode === 'fixed' && $itemAmount > 0) : ?>
        <div class="mb-2 pix-fixed-amount text-success fw-bold">
            <?php echo Text::_('PLG_FIELDS_PIX_AMOUNT_LABEL'); ?>: R$ <?php echo number_format($itemAmount, 2, ',', '.'); ?>
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
<?php endforeach; ?>
