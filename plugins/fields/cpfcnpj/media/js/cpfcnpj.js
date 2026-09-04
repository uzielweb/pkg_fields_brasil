/**
 * Custom Field Hybrid CPF / CNPJ - Adaptive Mask and Client-side Validator for Joomla 6
 */
(() => {
  'use strict';

  // Validates CPF Modulo 11
  const isValidCpf = (clean) => {
    if (clean.length !== 11) return false;
    if (/^(\d)\1{10}$/.test(clean)) return false;

    let sum = 0;
    for (let i = 0; i < 9; i++) {
      sum += parseInt(clean.charAt(i), 10) * (10 - i);
    }
    let remainder = sum % 11;
    let dv1 = remainder < 2 ? 0 : 11 - remainder;
    if (parseInt(clean.charAt(9), 10) !== dv1) return false;

    sum = 0;
    for (let i = 0; i < 10; i++) {
      sum += parseInt(clean.charAt(i), 10) * (11 - i);
    }
    remainder = sum % 11;
    let dv2 = remainder < 2 ? 0 : 11 - remainder;
    return parseInt(clean.charAt(10), 10) === dv2;
  };

  // Validates CNPJ Modulo 11 (including Alphanumeric IN RFB 2,229/2024)
  const isValidCnpj = (clean) => {
    if (clean.length !== 14) return false;
    if (!/^\d{2}$/.test(clean.substring(12, 14))) return false;
    if (/^([0-9a-zA-Z])\1{13}$/.test(clean)) return false;

    const weights1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    let sum = 0;
    for (let i = 0; i < 12; i++) {
      sum += (clean.charCodeAt(i) - 48) * weights1[i];
    }
    let remainder = sum % 11;
    let dv1 = remainder < 2 ? 0 : 11 - remainder;
    if (parseInt(clean.charAt(12), 10) !== dv1) return false;

    const weights2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    sum = 0;
    for (let i = 0; i < 12; i++) {
      sum += (clean.charCodeAt(i) - 48) * weights2[i];
    }
    sum += dv1 * 2;
    remainder = sum % 11;
    let dv2 = remainder < 2 ? 0 : 11 - remainder;

    return parseInt(clean.charAt(13), 10) === dv2;
  };

  // Validates either CPF or CNPJ based on string length
  const isValidDocument = (value) => {
    if (!value) return true;
    const clean = value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
    if (clean.length <= 11) {
      return isValidCpf(clean);
    }
    return isValidCnpj(clean);
  };

  // Adaptive mask: formats as CPF up to 11 chars, and seamlessly switches to CNPJ beyond
  const maskDocument = (value) => {
    let clean = value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase().substring(0, 14);

    if (clean.length > 11) {
      // Format as CNPJ: XX.XXX.XXX/XXXX-99
      if (clean.length > 12) {
        return clean.replace(/^([A-Z0-9]{2})([A-Z0-9]{3})([A-Z0-9]{3})([A-Z0-9]{4})([0-9]{1,2})$/, '$1.$2.$3/$4-$5');
      }
      return clean.replace(/^([A-Z0-9]{2})([A-Z0-9]{3})([A-Z0-9]{3})([A-Z0-9]{1,4})$/, '$1.$2.$3/$4');
    }

    // Format as CPF: 000.000.000-00
    if (clean.length > 9) {
      return clean.replace(/(\d{3})(\d{3})(\d{3})(\d{1,2})/, '$1.$2.$3-$4');
    }
    if (clean.length > 6) {
      return clean.replace(/(\d{3})(\d{3})(\d{1,3})/, '$1.$2.$3');
    }
    if (clean.length > 3) {
      return clean.replace(/(\d{3})(\d{1,3})/, '$1.$2');
    }

    return clean;
  };

  // Registers the handler with Joomla's client-side FormValidator
  const registerJoomlaValidator = () => {
    if (document.formvalidator && typeof document.formvalidator.setHandler === 'function') {
      document.formvalidator.setHandler('cpfcnpj', (value) => {
        return isValidDocument(value);
      });
    }
  };

  // Binds adaptive mask and validation events
  const initCpfCnpjFields = () => {
    registerJoomlaValidator();

    const fields = document.querySelectorAll('input.joomla-field-cpfcnpj, input.validate-cpfcnpj');
    fields.forEach((input) => {
      if (input.dataset.cpfcnpjBound) return;
      input.dataset.cpfcnpjBound = '1';

      const shouldMask = input.dataset.applyMask !== '0';

      if (shouldMask) {
        if (input.value) {
          input.value = maskDocument(input.value);
        }

        input.addEventListener('input', () => {
          const cursor = input.selectionStart;
          const prevLen = input.value.length;
          input.value = maskDocument(input.value);
          const diff = input.value.length - prevLen;
          if (cursor !== null) {
            input.setSelectionRange(cursor + diff, cursor + diff);
          }
        });

        input.addEventListener('blur', () => {
          if (input.value) {
            input.value = maskDocument(input.value);
          }
        });
      }
    });
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCpfCnpjFields);
  } else {
    initCpfCnpjFields();
  }

  // Observe dynamic DOM nodes
  if (window.MutationObserver) {
    const observer = new MutationObserver(() => initCpfCnpjFields());
    observer.observe(document.body, { childList: true, subtree: true });
  }
})();
