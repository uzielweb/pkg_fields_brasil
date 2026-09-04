/**
 * Custom Field CPF - Dynamic Mask and Client-side Validator for Joomla 6
 * Compliant with Brazilian Law No. 14,534/2023 and Receita Federal Modulo 11 algorithm.
 */
(() => {
  'use strict';

  // Validates CPF using Modulo 11 check digits algorithm
  const isValidCpf = (value) => {
    if (!value) return true;
    const clean = value.replace(/\D/g, '');
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

  // Formats input value into standard 000.000.000-00 mask
  const maskCpf = (value) => {
    let clean = value.replace(/\D/g, '').substring(0, 11);
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
      document.formvalidator.setHandler('cpf', (value) => {
        return isValidCpf(value);
      });
    }
  };

  // Binds mask and validation events to CPF input fields
  const initCpfFields = () => {
    registerJoomlaValidator();

    const fields = document.querySelectorAll('input.joomla-field-cpf, input.validate-cpf');
    fields.forEach((input) => {
      if (input.dataset.cpfBound) return;
      input.dataset.cpfBound = '1';

      const shouldMask = input.dataset.applyMask !== '0';

      if (shouldMask) {
        if (input.value) {
          input.value = maskCpf(input.value);
        }

        input.addEventListener('input', () => {
          const cursor = input.selectionStart;
          const prevLen = input.value.length;
          input.value = maskCpf(input.value);
          const diff = input.value.length - prevLen;
          if (cursor !== null) {
            input.setSelectionRange(cursor + diff, cursor + diff);
          }
        });

        input.addEventListener('blur', () => {
          if (input.value) {
            input.value = maskCpf(input.value);
          }
        });
      }
    });
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCpfFields);
  } else {
    initCpfFields();
  }

  // Observe dynamic DOM nodes (e.g. subforms, modals, AJAX)
  if (window.MutationObserver) {
    const observer = new MutationObserver(() => initCpfFields());
    observer.observe(document.body, { childList: true, subtree: true });
  }
})();
