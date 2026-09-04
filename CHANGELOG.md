# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0-alpha2] - 2026-09-04

### Added
- **Pix QR Code Center Logo Integration**:
  - Center logo customization on the dynamic QR code: choose between official Pix SVG icon, no logo, or custom image file.
  - Global default configurable in plugin/field parameters (`params/pix.xml` and `pix.xml`).
  - Item-level override or inheritance in the article subform (`forms/pix_subform.xml`).
  - Bundled official Brazilian Central Bank (BACEN) vector Pix SVG icon (`#32BCAD`) at `media/images/pix-icon.svg`.
  - QR Code error correction set to `Q` (25% error recovery) with canvas badge composition for high scanning reliability.
  - Full bilingual localization (`pt-BR` and `en-GB`).

## [1.0.0-alpha1] - 2026-09-04

### Added
- **CPF Custom Field (`plg_fields_cpf`)**:
  - Modulo 11 check digits validation (Lei Federal nº 14.534/2023).
  - Dynamic `000.000.000-00` input mask and LGPD masking display option.
- **CNPJ Custom Field (`plg_fields_cnpj`)**:
  - Universal ASCII Modulo 11 validation supporting both traditional numeric and new alphanumeric format (IN RFB nº 2.229/2024).
  - Dynamic `00.000.000/0000-00` input mask and LGPD masking.
- **CEP Custom Field (`plg_fields_cep`)**:
  - 8-digit postal code validation with `00000-000` mask.
  - Automated asynchronous ViaCEP address lookup (Street, Neighborhood, City, State, Complement).
  - Custom JavaScript event `joomla:cep-found`.
- **Telefone / WhatsApp Custom Field (`plg_fields_telefone`)**:
  - Dynamic 8- and 9-digit mask (`(00) 0000-0000` / `(00) 00000-0000`).
  - Anatel area codes (DDD) validation.
  - Direct WhatsApp Click-to-Chat button with configurable message and CSS classes.
- **Hybrid CPF / CNPJ Custom Field (`plg_fields_cpfcnpj`)**:
  - Adaptive real-time input mask switching seamlessly between CPF and CNPJ.
  - Dual validator accepting valid CPF, numeric CNPJ, and alphanumeric CNPJ.
- **Pix Multicampo Subform Custom Field (`plg_fields_pix`)**:
  - Subform multicampo architecture with per-article fields: Pix Key, Merchant Name, City, Amount Mode (None, Fixed, Free), Amount, TxID, and Description.
  - Optional repeatable mode (`repeat="1"`) allowing multiple Pix keys on a single article.
  - Official EMVCo TLV "Pix Copia e Cola" payload generator with CRC16-CCITT.
  - Pure vector SVG QR Code generation with instant 1-click clipboard copy.
- **Installer & Lifecycle Automation**:
  - Package `postflight` script automatically enabling newly installed plugins (`enabled = 1`) on initial installation while preserving unpublished/disabled status on updates.
  - Individual plugin installer scripts with prior state detection.
- **Joomla Native Update Server**:
  - Integrated `updates.xml` and `<updateservers>` in package and all 6 plugin manifests.
- **Unit Tests**:
  - 46 automated unit tests in `tests/test_validation.php`.
