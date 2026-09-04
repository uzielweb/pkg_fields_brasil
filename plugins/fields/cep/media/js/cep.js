/**
 * Custom Field CEP - Dynamic Mask, Client-side Validator and ViaCEP Auto-lookup for Joomla 6
 */
(() => {
  'use strict';

  // Validates Brazilian 8-digit Postal Code
  const isValidCep = (value) => {
    if (!value) return true;
    const clean = value.replace(/\D/g, '');
    return clean.length === 8 && clean !== '00000000';
  };

  // Formats input value into standard 00000-000 mask
  const maskCep = (value) => {
    let clean = value.replace(/\D/g, '').substring(0, 8);
    if (clean.length > 5) {
      return clean.replace(/^(\d{5})(\d{1,3})$/, '$1-$2');
    }
    return clean;
  };

  // Finds a target input element in the form by name, id or custom attribute
  const findFormTarget = (form, targetIdentifier) => {
    if (!form || !targetIdentifier) return null;
    const trimmed = targetIdentifier.trim();
    return (
      form.querySelector(`[name="${trimmed}"]`) ||
      form.querySelector(`[name*="[${trimmed}]"]`) ||
      form.querySelector(`[name$="[${trimmed}]"]`) ||
      form.querySelector(`#${trimmed}`) ||
      form.querySelector(`[data-field-name="${trimmed}"]`)
    );
  };

  // Queries ViaCEP API and populates mapped address fields
  const performViaCepLookup = (cepDigits, input) => {
    if (cepDigits.length !== 8) return;

    const form = input.form || input.closest('form');
    if (!form) return;

    const url = `https://viacep.com.br/ws/${encodeURIComponent(cepDigits)}/json/`;

    fetch(url)
      .then((res) => {
        if (!res.ok) throw new Error('ViaCEP response error');
        return res.json();
      })
      .then((data) => {
        if (data.erro) {
          input.classList.add('is-invalid');
          return;
        }

        // Dispatch custom event with full address details
        const event = new CustomEvent('joomla:cep-found', {
          bubbles: true,
          detail: { ...data, inputElement: input }
        });
        input.dispatchEvent(event);
        document.dispatchEvent(event);

        // Auto-fill configured target fields if present
        const map = [
          { prop: 'logradouro', target: input.dataset.targetStreet },
          { prop: 'bairro', target: input.dataset.targetNeighborhood },
          { prop: 'localidade', target: input.dataset.targetCity },
          { prop: 'uf', target: input.dataset.targetState },
          { prop: 'complemento', target: input.dataset.targetComplement }
        ];

        map.forEach(({ prop, target }) => {
          if (target && data[prop]) {
            const targetEl = findFormTarget(form, target);
            if (targetEl) {
              targetEl.value = data[prop];
              targetEl.dispatchEvent(new Event('change', { bubbles: true }));
              targetEl.dispatchEvent(new Event('input', { bubbles: true }));
            }
          }
        });
      })
      .catch((err) => {
        console.warn('ViaCEP auto-lookup failed:', err);
      });
  };

  // Registers the handler with Joomla's client-side FormValidator
  const registerJoomlaValidator = () => {
    if (document.formvalidator && typeof document.formvalidator.setHandler === 'function') {
      document.formvalidator.setHandler('cep', (value) => {
        return isValidCep(value);
      });
    }
  };

  // Binds mask, auto-lookup and validation events to CEP input fields
  const initCepFields = () => {
    registerJoomlaValidator();

    const fields = document.querySelectorAll('input.joomla-field-cep, input.validate-cep');
    fields.forEach((input) => {
      if (input.dataset.cepBound) return;
      input.dataset.cepBound = '1';

      const shouldMask = input.dataset.applyMask !== '0';
      const enableLookup = input.dataset.enableLookup !== '0';

      if (shouldMask) {
        if (input.value) {
          input.value = maskCep(input.value);
        }

        input.addEventListener('input', () => {
          const cursor = input.selectionStart;
          const prevLen = input.value.length;
          input.value = maskCep(input.value);
          const diff = input.value.length - prevLen;
          if (cursor !== null) {
            input.setSelectionRange(cursor + diff, cursor + diff);
          }

          const clean = input.value.replace(/\D/g, '');
          if (clean.length === 8 && enableLookup) {
            performViaCepLookup(clean, input);
          }
        });

        input.addEventListener('blur', () => {
          if (input.value) {
            input.value = maskCep(input.value);
            const clean = input.value.replace(/\D/g, '');
            if (clean.length === 8 && enableLookup) {
              performViaCepLookup(clean, input);
            }
          }
        });
      }
    });
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCepFields);
  } else {
    initCepFields();
  }

  // Observe dynamic DOM nodes
  if (window.MutationObserver) {
    const observer = new MutationObserver(() => initCepFields());
    observer.observe(document.body, { childList: true, subtree: true });
  }
})();
