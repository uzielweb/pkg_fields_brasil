/**
 * Custom Field Telefone - Dynamic 8/9-digit Mask and Client-side Validator for Joomla 6
 */
(() => {
  'use strict';

  // Valid Brazilian area codes (DDDs)
  const validDdds = new Set([
    11, 12, 13, 14, 15, 16, 17, 18, 19,
    21, 22, 24, 27, 28,
    31, 32, 33, 34, 35, 37, 38,
    41, 42, 43, 44, 45, 46, 47, 48, 49,
    51, 53, 54, 55,
    61, 62, 63, 64, 65, 66, 67, 68, 69,
    71, 73, 74, 75, 77, 79,
    81, 82, 83, 84, 85, 86, 87, 88, 89,
    91, 92, 93, 94, 95, 96, 97, 98, 99
  ]);

  // Validates phone number length and DDD
  const isValidPhone = (value) => {
    if (!value) return true;
    const clean = value.replace(/\D/g, '');
    const len = clean.length;

    if (len !== 10 && len !== 11) return false;
    if (/^(\d)\1+$/.test(clean)) return false;

    const ddd = parseInt(clean.substring(0, 2), 10);
    if (!validDdds.has(ddd)) return false;

    if (len === 11 && clean.charAt(2) !== '9') return false;
    if (len === 10 && !['2', '3', '4', '5'].includes(clean.charAt(2))) return false;

    return true;
  };

  // Formats phone into dynamic (00) 0000-0000 or (00) 00000-0000 mask
  const maskPhone = (value) => {
    let clean = value.replace(/\D/g, '').substring(0, 11);

    if (clean.length > 10) {
      // 11 digits: mobile (00) 00000-0000
      return clean.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
    }
    if (clean.length > 6) {
      // 10 digits or in-progress typing
      return clean.replace(/^(\d{2})(\d{4})(\d{0,4})$/, '($1) $2-$3');
    }
    if (clean.length > 2) {
      return clean.replace(/^(\d{2})(\d{0,5})$/, '($1) $2');
    }
    if (clean.length > 0) {
      return clean.replace(/^(\d{0,2})$/, '($1');
    }
    return clean;
  };

  // Registers the handler with Joomla's client-side FormValidator
  const registerJoomlaValidator = () => {
    if (document.formvalidator && typeof document.formvalidator.setHandler === 'function') {
      document.formvalidator.setHandler('telefone', (value) => {
        return isValidPhone(value);
      });
    }
  };

  // Binds mask and validation events to Telefone input fields
  const initTelefoneFields = () => {
    registerJoomlaValidator();

    const fields = document.querySelectorAll('input.joomla-field-telefone, input.validate-telefone');
    fields.forEach((input) => {
      if (input.dataset.telefoneBound) return;
      input.dataset.telefoneBound = '1';

      const shouldMask = input.dataset.applyMask !== '0';

      if (shouldMask) {
        if (input.value) {
          input.value = maskPhone(input.value);
        }

        input.addEventListener('input', () => {
          const cursor = input.selectionStart;
          const prevLen = input.value.length;
          input.value = maskPhone(input.value);
          const diff = input.value.length - prevLen;
          if (cursor !== null) {
            input.setSelectionRange(cursor + diff, cursor + diff);
          }
        });

        input.addEventListener('blur', () => {
          if (input.value) {
            input.value = maskPhone(input.value);
          }
        });
      }
    });
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTelefoneFields);
  } else {
    initTelefoneFields();
  }

  // Observe dynamic DOM nodes
  if (window.MutationObserver) {
    const observer = new MutationObserver(() => initTelefoneFields());
    observer.observe(document.body, { childList: true, subtree: true });
  }
})();
