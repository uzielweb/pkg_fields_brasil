# Brazilian Custom Fields Package for Joomla 6 / Pacote de Campos Customizados para Brasil (`pkg_fields_brasil`)

[![Joomla 6 Compatible](https://img.shields.io/badge/Joomla!-6.x%20%7C%205.x-5091CD?style=flat-square&logo=joomla)](https://www.joomla.org)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D%208.2-777BB4?style=flat-square&logo=php)](https://www.php.net)
[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg?style=flat-square)](LICENSE)
[![Release](https://img.shields.io/badge/Release-v1.0.0--alpha1-orange.svg?style=flat-square)](https://github.com/uzielweb/pkg_fields_brasil/releases/tag/v1.0.0-alpha1)

---

> **Language / Idioma**: [English](#english) | [Português (Brasil)](#português-brasil)

---

<a name="english"></a>
## English

### Overview
A comprehensive suite of **6 Custom Field plugins** (group `fields`) for **Joomla 6** and **Joomla 5**, engineered to meet modern Joomla core architectural standards (Dependency Injection, Service Providers, PSR-4 Namespaces, Web Asset Manager, and native FormRules). 

Built specifically for Brazilian web applications, in full compliance with current federal legislation and regulatory instructions.

### Included Plugins

| Plugin | Field Type | Description | Compliance / Standard |
| :--- | :--- | :--- | :--- |
| **`plg_fields_cpf`** | `cpf` | Official Modulo 11 check digits, dynamic input mask, and LGPD privacy options. | **Federal Law No. 14,534/2023** (Universal National ID) |
| **`plg_fields_cnpj`** | `cnpj` | Universal Modulo 11 ASCII validation for **traditional numeric** and **new alphanumeric CNPJ**. | **RFB Normative Instruction No. 2,229/2024** |
| **`plg_fields_cep`** | `cep` | 8-digit postal code, dynamic mask, and automated asynchronous ViaCEP address lookup. | **ViaCEP API** / Correios |
| **`plg_fields_telefone`** | `telefone` | Dynamic 8/9-digit phone mask, Anatel area code (DDD) validation, and WhatsApp Click-to-Chat. | **Anatel / WhatsApp API** |
| **`plg_fields_cpfcnpj`** | `cpfcnpj` | Adaptive hybrid field with seamless real-time mask transition between CPF and CNPJ. | **Receita Federal (PF / PJ)** |
| **`plg_fields_pix`** | `pix` | Multifield Subform (key, holder, city, fixed/free amount, txid, description), EMVCo payload, and QR Code. | **Central Bank of Brazil (BACEN)** |

---

### Legal Compliance & Algorithms

#### 1. New Alphanumeric CNPJ (RFB IN No. 2,229/2024)
Effective **July 31, 2026**, the Brazilian Federal Revenue Service (Receita Federal do Brasil) adopted an alphanumeric format for new CNPJ registrations:
* **14-Character Structure**:
  * Characters 1 to 8: Alphanumeric Root (`[A-Z0-9]{8}`)
  * Characters 9 to 12: Alphanumeric Branch Order (`[A-Z0-9]{4}`)
  * Characters 13 and 14: Strictly numeric Check Digits (`[0-9]{2}`)
* **ASCII Value Calculation**: Each character $c$ is converted to a numeric value by $val = \text{ord}(c) - 48$:
  * Digits `0`–`9`: values 0 to 9.
  * Letters `A`–`Z`: values 17 to 42.
* **Modulo 11 Check Digits**:
  * **1st DV**: Weights `[5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]` applied to characters 1–12. Remainder modulo 11: if $< 2$, $DV_1 = 0$; otherwise $11 - \text{remainder}$.
  * **2nd DV**: Weights `[6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]` applied to characters 1–12 + $DV_1$. Remainder modulo 11: if $< 2$, $DV_2 = 0$; otherwise $11 - \text{remainder}$.
* This algorithm seamlessly validates both **100% of traditional numeric CNPJs** and **all new alphanumeric CNPJs**.

#### 2. CPF and Federal Law No. 14,534/2023
Federal Law No. 14,534/2023 established the CPF as the single and universal civil registry number across all government databases and official documents. Validated strictly with Modulo 11 (weights 10–2 for DV1 and 11–2 for DV2) and rejection of repetitive dummy sequences.

#### 3. EMVCo Pix Standard (Central Bank of Brazil)
The Pix plugin generates valid EMVCo TLV payloads:
* Compliant with BACEN Pix Initiation Manual (Tags 00, 26, 52, 53, 54, 58, 59, 60, 62).
* Hardware-level **CRC16-CCITT** calculation (polynomial `0x1021`, initial `0xFFFF`) on Tag 63.
* **Fixed or Free Amount**: when set to Free Amount, visitors can type their desired amount directly on the website, and the QR Code and copy code dynamically update in real time.

---

### Key Highlights

* **Automated Address Lookup (CEP)**: Auto-fills mapped fields (Street, Neighborhood, City, State, Complement) upon typing 8 digits, and dispatches custom event `joomla:cep-found`.
* **WhatsApp Click-to-Chat**: Landline/mobile dynamic mask with instant link or button to start conversations (`https://wa.me/55...`) with pre-configured greetings.
* **LGPD Data Privacy**: Option to mask sensitive documents for display (e.g. `***.456.789-**` or `**.***.***/****-XX`).

---

### Installation & Updates in Joomla 6

1. In the Joomla Administrator panel, navigate to **System** > **Install** > **Extensions**.
2. Under the **Upload Package File** tab, upload **`pkg_fields_brasil_v1.0.0-alpha1.zip`**.
3. **Auto-Enabled**: All 6 plugins are automatically enabled upon installation via the installer script.
4. **Update Server**: The package includes native Joomla Update Server integration (`updates.xml`). Future releases will appear directly in Joomla's **System** > **Update** > **Extensions**.
5. Go to **Content** > **Fields** (or **Users** > **Fields**), click **New**, and select the desired field type.

---

<hr style="margin: 40px 0;">

<a name="português-brasil"></a>
## Português (Brasil)

### Visão Geral
Suíte completa de **6 plugins de Campos Customizados (Custom Fields)** do grupo `fields` para **Joomla 6** e **Joomla 5**, desenvolvida estritamente de acordo com os padrões arquiteturais do Joomla core (Injeção de Dependência, Service Providers, Namespaces PSR-4, Web Asset Manager e validação nativa via FormRules).

Projetada especificamente para aplicações web brasileiras, em total conformidade com a legislação federal e normas regulamentares vigentes.

### Plugins Incluídos

| Plugin | Tipo de Campo | Descrição Principal | Legislação / Padrão |
| :--- | :--- | :--- | :--- |
| **`plg_fields_cpf`** | `cpf` | Validação oficial Módulo 11, máscara dinâmica e suporte a mascaramento LGPD. | **Lei nº 14.534/2023** (Identificador Único Nacional) |
| **`plg_fields_cnpj`** | `cnpj` | Validação universal Módulo 11 (ASCII) para **CNPJ Tradicional** e novo **CNPJ Alfanumérico**. | **IN RFB nº 2.229/2024** (Novo Padrão Alfanumérico) |
| **`plg_fields_cep`** | `cep` | Validação de 8 dígitos, máscara e auto-preenchimento assíncrono de endereço. | **ViaCEP API** / Correios |
| **`plg_fields_telefone`** | `telefone` | Máscara dinâmica 8/9 dígitos, validação de DDD da Anatel e botão para WhatsApp. | **Anatel / WhatsApp API** |
| **`plg_fields_cpfcnpj`** | `cpfcnpj` | Campo híbrido adaptativo com transição fluida de máscara e validação contextual. | **Receita Federal (PF / PJ)** |
| **`plg_fields_pix`** | `pix` | Multicampo Subform (chave, titular, cidade, valor fixo/livre, txid, descrição), gerador EMVCo e QR Code. | **Banco Central do Brasil (EMVCo / CRC16)** |

---

### Conformidade Legal e Algoritmos

#### 1. Novo CNPJ Alfanumérico (Instrução Normativa RFB nº 2.229/2024)
A partir de **31 de julho de 2026**, a Receita Federal passou a emitir CNPJs em formato alfanumérico para novas inscrições:
* **Estrutura de 14 posições**:
  * Posições 1 a 8: Raiz alfanumérica (`[A-Z0-9]{8}`)
  * Posições 9 a 12: Ordem do estabelecimento (`[A-Z0-9]{4}`)
  * Posições 13 e 14: Dígitos Verificadores estritamente numéricos (`[0-9]{2}`)
* **Conversão ASCII**: Cada caractere $c$ é convertido via $val = \text{ord}(c) - 48$:
  * Algarismos `0` a `9`: valores de $0$ a $9$.
  * Letras `A` a `Z`: valores de $17$ a $42$.
* **Cálculo dos DVs**:
  * **1º DV**: Pesos `[5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]`. Resto por 11: se menor que 2, $DV_1 = 0$; caso contrário, $11 - \text{resto}$.
  * **2º DV**: Pesos `[6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]` aplicados aos 12 caracteres + $DV_1$. Resto por 11: se menor que 2, $DV_2 = 0$; caso contrário, $11 - \text{resto}$.
* O algoritmo valida tanto **100% dos CNPJs numéricos tradicionais** quanto os **novos CNPJs alfanuméricos**.

#### 2. CPF e a Lei nº 14.534/2023
A Lei nº 14.534/2023 consolidou o CPF como número único de identificação em todos os serviços e documentos públicos do Brasil. O plugin valida os 11 dígitos pelo cálculo estrito de Módulo 11 com rejeição de sequências com dígitos repetidos.

#### 3. Padrão EMVCo Pix (Banco Central do Brasil)
O plugin de Chave PIX gera o payload oficial estipulado no Manual de Iniciação do Pix do BACEN:
* Estrutura TLV padronizada com checksum **CRC16-CCITT** (polinômio `0x1021`, inicial `0xFFFF`).
* Suporte a **Valor Fixo** ou **Valor Livre** (com atualização do QR Code e código Copia e Cola em tempo real diretamente no navegador).

---

### Funcionalidades em Destaque

* **Auto-preenchimento via CEP**: Preenche automaticamente Logradouro, Bairro, Cidade, UF e Complemento mapeados e dispara o evento JavaScript `joomla:cep-found`:
```javascript
document.addEventListener('joomla:cep-found', (e) => {
    console.log('Endereço encontrado:', e.detail);
});
```
* **Telefone com WhatsApp**: Alternância automática entre 8 e 9 dígitos e botão de conversa direta no WhatsApp (`https://wa.me/55...`) com mensagem pré-definida.
* **Privacidade LGPD**: Opção de mascarar documentos sensíveis no frontend (ex: `***.456.789-**` ou `**.***.***/****-XX`).

---

### Instalação e Atualizações no Joomla 6

1. No painel de administração do Joomla 6, acesse **Sistema** > **Instalar** > **Extensões**.
2. Na aba **Enviar Arquivo Pacote**, envie o arquivo **`pkg_fields_brasil_v1.0.0-alpha1.zip`**.
3. **Habilitação Automática**: Todos os 6 plugins são automaticamente habilitados no banco de dados durante a instalação através do script de pós-instalação (`postflight`).
4. **Update Server Nativo**: O pacote já vem configurado com servidor de atualização do Joomla (`updates.xml`). Novas versões serão notificadas diretamente em **Sistema** > **Atualizar** > **Extensões**.
5. Acesse **Conteúdo** > **Campos** (ou **Usuários** > **Campos**), clique em **Novo** e selecione o tipo de campo desejado.

---

### Estrutura do Repositório

```
pkg_fields_brasil/
├── pkg_fields_brasil.xml             # Manifesto mestre do pacote Joomla
├── script.php                        # Script de instalação do pacote (habilita os 6 plugins)
├── updates.xml                       # Servidor de atualização nativo do Joomla (Update Server)
├── build.php                         # Automação CLI de empacotamento
├── tests/
│   └── test_validation.php           # Suíte de testes unitários automatizados
├── language/
│   ├── pt-BR/pkg_fields_brasil.sys.ini
│   └── en-GB/pkg_fields_brasil.sys.ini
└── plugins/fields/
    ├── cpf/                          # Plugin CPF
    ├── cnpj/                         # Plugin CNPJ (Tradicional + Alfanumérico)
    ├── cep/                          # Plugin CEP (com ViaCEP)
    ├── telefone/                     # Plugin Telefone / WhatsApp
    ├── cpfcnpj/                      # Plugin Híbrido CPF / CNPJ
    └── pix/                          # Plugin Chave PIX (QR Code & Copia e Cola)
```

---

### Comandos de Teste e Build

* **Executar testes unitários**:
  ```bash
  php tests/test_validation.php
  ```
* **Gerar pacote instalável**:
  ```bash
  php build.php
  ```

---

### Licença e Autor

* **Autor**: Uziel Almeida Oliveira ([@uzielweb](https://github.com/uzielweb))
* **Licença**: GNU General Public License v2 ou posterior ([GPL-2.0-or-later](LICENSE)).
