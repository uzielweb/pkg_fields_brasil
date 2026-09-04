/**
 * Custom Field CNPJ - Dynamic Mask and Client-side Validator for Joomla 6
 * Fully compliant with Brazilian Normative Instruction RFB No. 2,229/2024 (Numeric and Alphanumeric CNPJ).
 */
(() => {
  'use strict';

  // Universal CNPJ validator supporting numeric and alphanumeric formats (RFB IN 2,229/2024)
  const isValidCnpj = (value, allowAlphanumeric = true) => {
    if (!value) return true;
    const clean = value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
    if (clean.length !== 14) return false;

    if (!allowAlphanumeric && !/^\d{14}$/.test(clean)) {
      return false;
    }

    // The check digits (positions 13 and 14) MUST be numeric (0-9)
    if (!/^\d{2}$/.test(clean.substring(12, 14))) {
      return false;
    }

    // Reject uniform repeating characters
    if (/^([0-9a-zA-Z])\1{13}$/.test(clean)) {
      return false;
    }

    // Weights for 1st check digit
    const weights1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    let sum = 0;
    for (let i = 0; i < 12; i++) {
      const val = clean.charCodeAt(i) - 48;
      sum += val * weights1[i];
    }
    let remainder = sum % 11;
    let dv1 = remainder < 2 ? 0 : 11 - remainder;
    if (parseInt(clean.charAt(12), 10) !== dv1) {
      return false;
    }

    // Weights for 2nd check digit (including calculated 1st DV)
    const weights2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    sum = 0;
    for (let i = 0; i < 12; i++) {
      const val = clean.charCodeAt(i) - 48;
      sum += val * weights2[i];
    }
    sum += dv1 * 2;
    remainder = sum % 11;
    let dv2 = remainder < 2 ? 0 : 11 - remainder;

    return parseInt(clean.charAt(13), 10) === dv2;
  };

  // Formats input value into standard XX.XXX.XXX/XXXX-99 mask
  const maskCnpj = (value) => {
    let clean = value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase().substring(0, 14);
    if (clean.length > 12) {
      return clean.replace(/^([A-Z0-9]{2})([A-Z0-9]{3})([A-Z0-9]{3})([A-Z0-9]{4})([0-9]{1,2})$/, '$1.$2.$3/$4-$5');
    }
    if (clean.length > 8) {
      return clean.replace(/^([A-Z0-9]{2})([A-Z0-9]{3})([A-Z0-9]{3})([A-Z0-9]{1,4})$/, '$1.$2.$3/$4');
    }
    if (clean.length > 5) {
      return clean.replace(/^([A-Z0-9]{2})([A-Z0-9]{3})([A-Z0-9]{1,3})$/, '$1.$2.$3');
    }
    if (clean.length > 2) {
      return clean.replace(/^([A-Z0-9]{2})([A-Z0-9]{1,3})$/, '$1.$2');
    }
    return clean;
  };

  // Registers the handler with Joomla's client-side FormValidator
  const registerJoomlaValidator = () => {
    if (document.formvalidator && typeof document.formvalidator.setHandler === 'function') {
      document.formvalidator.setHandler('cnpj', (value, element) => {
        const allowAlphanumeric = !element || element.dataset.allowAlphanumeric !== '0';
        return isValidCnpj(value, allowAlphanumeric);
      });
    }
  };

  // Binds mask, auto-uppercase and validation events to CNPJ input fields
  const initCnpjFields = () => {
    registerJoomlaValidator();

    const fields = document.querySelectorAll('input.joomla-field-cnpj, input.validate-cnpj');
    fields.forEach((input) => {
      if (input.dataset.cnpjBound) return;
      input.dataset.cnpjBound = '1';

      const shouldMask = input.dataset.applyMask !== '0';

      if (shouldMask) {
        if (input.value) {
          input.value = maskCnpj(input.value);
        }

        input.addEventListener('input', () => {
          const cursor = input.selectionStart;
          const prevLen = input.value.length;
          input.value = maskCnpj(input.value);
          const diff = input.value.length - prevLen;
          if (cursor !== null) {
            input.setSelectionRange(cursor + diff, cursor + diff);
          }
        });

        input.addEventListener('blur', () => {
          if (input.value) {
            input.value = maskCnpj(input.value);
          }
        });
      } else {
        input.addEventListener('input', () => {
          input.value = input.value.toUpperCase();
        });
      }
    });
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCnpjFields);
  } else {
    initCnpjFields();
  }

  // Observe dynamic DOM nodes
  if (window.MutationObserver) {
    const observer = new MutationObserver(() => initCnpjFields());
    observer.observe(document.body, { childList: true, subtree: true });
  }
})();
