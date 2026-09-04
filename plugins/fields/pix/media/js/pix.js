/**
 * Custom Field Pix - Dynamic EMVCo Generator, QR Code Renderer and Client-side Validator for Joomla 6
 */
(() => {
  'use strict';

  // Calculates CRC16-CCITT (0xFFFF, 0x1021) for EMVCo Pix payload
  const calculateCrc16 = (str) => {
    const payload = str + '6304';
    let crc = 0xFFFF;
    const polynomial = 0x1021;

    for (let i = 0; i < payload.length; i++) {
      crc ^= (payload.charCodeAt(i) << 8);
      for (let j = 0; j < 8; j++) {
        if ((crc & 0x8000) !== 0) {
          crc = ((crc << 1) ^ polynomial) & 0xFFFF;
        } else {
          crc = (crc << 1) & 0xFFFF;
        }
      }
    }

    return crc.toString(16).toUpperCase().padStart(4, '0');
  };

  // Formats Tag-Length-Value (TLV) element
  const formatTlv = (tag, value) => {
    const len = value.length.toString().padStart(2, '0');
    return `${tag}${len}${value}`;
  };

  // Normalizes Pix key based on its pattern
  const normalizeKey = (key) => {
    const trimmed = key.trim();
    if (trimmed.includes('@')) return trimmed; // Email
    if (/^[0-9a-fA-F-]{36}$/.test(trimmed)) return trimmed; // EVP UUID
    if (trimmed.startsWith('+55')) return trimmed;
    const digits = trimmed.replace(/\D/g, '');
    if (digits.length === 10 || digits.length === 11) {
      return `+55${digits}`;
    }
    return trimmed.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
  };

  // Generates Pix Copia e Cola EMVCo string on the client side
  const buildPixPayload = (key, name, city, amount, txid = '***', description = '') => {
    const normKey = normalizeKey(key);
    let descTlv = '';
    if (description && description.trim()) {
      const cleanDesc = description.trim().substring(0, 40);
      descTlv = formatTlv('02', cleanDesc);
    }
    const mai = formatTlv('26', formatTlv('00', 'br.gov.bcb.pix') + formatTlv('01', normKey) + descTlv);
    const cleanName = (name || 'BENEFICIARIO').substring(0, 25).toUpperCase();
    const cleanCity = (city || 'SAO PAULO').substring(0, 15).toUpperCase();
    const cleanTxid = (txid || '***').substring(0, 25);

    let payload = formatTlv('00', '01') +
      mai +
      formatTlv('52', '0000') +
      formatTlv('53', '986');

    if (amount && parseFloat(amount) > 0) {
      payload += formatTlv('54', parseFloat(amount).toFixed(2));
    }

    payload += formatTlv('58', 'BR') +
      formatTlv('59', cleanName) +
      formatTlv('60', cleanCity) +
      formatTlv('62', formatTlv('05', cleanTxid));

    const crc = calculateCrc16(payload);
    return `${payload}6304${crc}`;
  };

  /**
   * Loads qrcode.min.js dynamically if not already available in window
   */
  const ensureQrCodeLoaded = (callback, customScriptUrl = '') => {
    if (typeof QRCode !== 'undefined') {
      callback();
      return;
    }

    if (!document.getElementById('pix_qrcode_js')) {
      const script = document.createElement('script');
      script.id = 'pix_qrcode_js';
      if (customScriptUrl) {
        script.src = customScriptUrl;
      } else {
        const rootMeta = document.querySelector('meta[name="joomla:root-url"]');
        const rootUrl = rootMeta && rootMeta.content ? rootMeta.content.replace(/\/$/, '') : '';
        script.src = (rootUrl || '') + '/media/plg_fields_pix/js/qrcode.min.js';
      }
      script.onload = () => {
        document.dispatchEvent(new Event('pix_qrcode_loaded'));
      };
      document.head.appendChild(script);
    }
    document.addEventListener('pix_qrcode_loaded', callback, { once: true });
  };

  /**
   * Renders QR Code using the bundled rock-solid QRCode library (offline, no external APIs)
   */
  const renderQrCode = (container, text, size = 200) => {
    if (!container || !text) return;
    container.innerHTML = '';
    try {
      new QRCode(container, {
        text: text,
        width: size,
        height: size,
        correctLevel: QRCode.CorrectLevel.Q
      });
    } catch (e) {
      console.error('Error generating Pix QR Code:', e);
    }
  };

  // Validates Pix Key in Joomla FormValidator
  const isValidPixKey = (value) => {
    if (!value) return true;
    const trimmed = value.trim();
    if (trimmed.includes('@')) return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(trimmed);
    if (/^[0-9a-fA-F-]{36}$/.test(trimmed)) return true;
    const clean = trimmed.replace(/[^a-zA-Z0-9]/g, '');
    if (clean.length === 11 || clean.length === 14) return true;
    if (clean.length === 10 && /^\d+$/.test(clean)) return true;
    return false;
  };

  // Registers the handler with Joomla's client-side FormValidator
  const registerJoomlaValidator = () => {
    if (document.formvalidator && typeof document.formvalidator.setHandler === 'function') {
      document.formvalidator.setHandler('pix', (value) => {
        return isValidPixKey(value);
      });
    }
  };

  // Initializes frontend interactive Pix widgets
  const initPixCards = () => {
    registerJoomlaValidator();

    const cards = document.querySelectorAll('.field-pix-card');
    cards.forEach((card) => {
      if (card.dataset.pixBound) return;
      card.dataset.pixBound = '1';

      const key = card.dataset.pixKey;
      const name = card.dataset.merchantName;
      const city = card.dataset.merchantCity;
      const description = card.dataset.description || '';
      const txid = card.dataset.txid;
      const logoUrl = card.dataset.logoUrl || '';
      const qrcodeScript = card.dataset.qrcodeScript || '';
      const qrContainer = card.querySelector('.pix-qrcode-container');
      const payloadInput = card.querySelector('.pix-payload-input');
      const amountInput = card.querySelector('.pix-amount-input');
      const copyBtn = card.querySelector('.btn-copy-pix');
      const feedback = card.querySelector('.pix-copy-feedback');

      const updatePix = (amount) => {
        const payload = buildPixPayload(key, name, city, amount, txid, description);
        if (payloadInput) {
          payloadInput.value = payload;
        }
        if (qrContainer) {
          ensureQrCodeLoaded(() => renderQrCode(qrContainer, payload, 200), qrcodeScript);
        }
      };

      // Initial render of QR code
      const initialPayload = card.dataset.payload || (payloadInput ? payloadInput.value : '');
      if (qrContainer && initialPayload) {
        ensureQrCodeLoaded(() => renderQrCode(qrContainer, initialPayload, 200), qrcodeScript);
      }

      // Handle real-time amount changes (Free Amount mode)
      if (amountInput) {
        amountInput.addEventListener('input', () => {
          const val = amountInput.value;
          updatePix(val);
        });
      }

      // Handle Copy Pix Code button
      if (copyBtn && payloadInput) {
        copyBtn.addEventListener('click', () => {
          const textToCopy = payloadInput.value;
          if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(textToCopy).then(() => {
              if (feedback) {
                feedback.classList.remove('d-none');
                setTimeout(() => feedback.classList.add('d-none'), 2500);
              }
            });
          } else {
            payloadInput.select();
            document.execCommand('copy');
            if (feedback) {
              feedback.classList.remove('d-none');
              setTimeout(() => feedback.classList.add('d-none'), 2500);
            }
          }
        });
      }
    });
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPixCards);
  } else {
    initPixCards();
  }

  // Observe dynamic DOM nodes
  if (window.MutationObserver) {
    const observer = new MutationObserver(() => initPixCards());
    observer.observe(document.body, { childList: true, subtree: true });
  }
})();
