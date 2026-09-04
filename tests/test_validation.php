<?php

/**
 * Automated Unit Test Suite for Brazilian Custom Fields Validation Logic
 *
 * @copyright (C) 2026 Uziel Almeida Oliveira <https://github.com/uzielweb>
 * @license   GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Form {
    if (!defined('_JEXEC')) {
        define('_JEXEC', 1);
    }
    if (!class_exists('FormRule')) {
        abstract class FormRule {
            abstract public function test(\SimpleXMLElement $element, $value, $group = null, ?\Joomla\Registry\Registry $input = null, ?Form $form = null);
        }
    }
}

namespace Joomla\Registry {
    if (!class_exists('Registry')) {
        class Registry {}
    }
}

namespace {
    require_once __DIR__ . '/../plugins/fields/cpf/src/Rule/CpfRule.php';
    require_once __DIR__ . '/../plugins/fields/cnpj/src/Rule/CnpjRule.php';
    require_once __DIR__ . '/../plugins/fields/cep/src/Rule/CepRule.php';
    require_once __DIR__ . '/../plugins/fields/telefone/src/Rule/TelefoneRule.php';
    require_once __DIR__ . '/../plugins/fields/cpfcnpj/src/Rule/CpfcnpjRule.php';
    require_once __DIR__ . '/../plugins/fields/pix/src/Helper/PixHelper.php';
    require_once __DIR__ . '/../plugins/fields/pix/src/Rule/PixRule.php';

    use Uziel\Plugin\Fields\Cpf\Rule\CpfRule;
    use Uziel\Plugin\Fields\Cnpj\Rule\CnpjRule;
    use Uziel\Plugin\Fields\Cep\Rule\CepRule;
    use Uziel\Plugin\Fields\Telefone\Rule\TelefoneRule;
    use Uziel\Plugin\Fields\Cpfcnpj\Rule\CpfcnpjRule;
    use Uziel\Plugin\Fields\Pix\Helper\PixHelper;
    use Uziel\Plugin\Fields\Pix\Rule\PixRule;

    $passed = 0;
    $failed = 0;

    function assertCondition(bool $condition, string $description): void {
        global $passed, $failed;
        if ($condition) {
            echo " [PASS] $description\n";
            $passed++;
        } else {
            echo " [FAIL] $description\n";
            $failed++;
        }
    }

    echo "=== Starting Test Suite for pkg_fields_brasil ===\n\n";

    // 1. CPF Tests
    echo "--- Testing CPF Validation ---\n";
    assertCondition(CpfRule::validate('52998224725'), 'Valid clean CPF (52998224725)');
    assertCondition(CpfRule::validate('529.982.247-25'), 'Valid formatted CPF (529.982.247-25)');
    assertCondition(!CpfRule::validate('52998224726'), 'Invalid check digit CPF should fail');
    assertCondition(!CpfRule::validate('11111111111'), 'Repeated digits CPF (11111111111) should fail');
    assertCondition(!CpfRule::validate('00000000000'), 'Repeated zeroes CPF should fail');
    assertCondition(!CpfRule::validate('1234567890'), 'Short CPF should fail');
    assertCondition(CpfRule::format('52998224725') === '529.982.247-25', 'CPF format function output');
    assertCondition(CpfRule::maskLgpd('52998224725') === '***.982.247-**', 'CPF LGPD mask output');

    // 2. CNPJ Tests
    echo "\n--- Testing CNPJ Validation (Numeric & IN RFB 2,229/2024 Alphanumeric) ---\n";
    assertCondition(CnpjRule::validate('11222333000181'), 'Valid clean traditional numeric CNPJ');
    assertCondition(CnpjRule::validate('11.222.333/0001-81'), 'Valid formatted traditional numeric CNPJ');
    assertCondition(!CnpjRule::validate('11222333000182'), 'Invalid check digit numeric CNPJ should fail');
    assertCondition(!CnpjRule::validate('00000000000000'), 'Repeated digits CNPJ should fail');

    // Calculate a valid Alphanumeric CNPJ under IN 2,229/2024 rule:
    $alnumBase = '12ABC34501DE';
    $w1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    $s1 = 0;
    for ($i = 0; $i < 12; $i++) {
        $s1 += (ord($alnumBase[$i]) - 48) * $w1[$i];
    }
    $rem1 = $s1 % 11;
    $dv1 = ($rem1 < 2) ? 0 : 11 - $rem1;

    $w2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    $s2 = 0;
    for ($i = 0; $i < 12; $i++) {
        $s2 += (ord($alnumBase[$i]) - 48) * $w2[$i];
    }
    $s2 += $dv1 * 2;
    $rem2 = $s2 % 11;
    $dv2 = ($rem2 < 2) ? 0 : 11 - $rem2;
    $validAlnumCnpj = $alnumBase . $dv1 . $dv2;

    assertCondition(CnpjRule::validate($validAlnumCnpj), "Valid Alphanumeric CNPJ ($validAlnumCnpj) passes");
    assertCondition(CnpjRule::validate(CnpjRule::format($validAlnumCnpj)), 'Formatted Alphanumeric CNPJ passes');
    assertCondition(!CnpjRule::validate($alnumBase . '99'), 'Alphanumeric CNPJ with invalid DVs fails');

    // 3. CEP Tests
    echo "\n--- Testing CEP Validation ---\n";
    assertCondition(CepRule::validate('01310100'), 'Valid 8-digit clean CEP');
    assertCondition(CepRule::validate('01310-100'), 'Valid formatted CEP');
    assertCondition(!CepRule::validate('00000000'), 'Dummy all-zero CEP should fail');
    assertCondition(!CepRule::validate('1234567'), '7-digit CEP should fail');
    assertCondition(CepRule::format('01310100') === '01310-100', 'CEP format function output');

    // 4. Telefone Tests
    echo "\n--- Testing Telefone / WhatsApp Validation ---\n";
    assertCondition(TelefoneRule::validate('11987654321'), 'Valid 11-digit mobile with DDD 11');
    assertCondition(TelefoneRule::validate('(11) 98765-4321'), 'Valid formatted 11-digit mobile');
    assertCondition(TelefoneRule::validate('1134567890'), 'Valid 10-digit landline with DDD 11');
    assertCondition(TelefoneRule::validate('(11) 3456-7890'), 'Valid formatted 10-digit landline');
    assertCondition(!TelefoneRule::validate('00987654321'), 'Invalid DDD 00 should fail');
    assertCondition(!TelefoneRule::validate('20987654321'), 'Invalid DDD 20 should fail');
    assertCondition(!TelefoneRule::validate('11887654321'), '11-digit mobile not starting with 9 should fail');

    // 5. CPF/CNPJ Hybrid Tests
    echo "\n--- Testing Hybrid CPF/CNPJ Validation ---\n";
    assertCondition(CpfcnpjRule::validate('52998224725'), 'Hybrid recognises and validates valid CPF');
    assertCondition(CpfcnpjRule::validate('11222333000181'), 'Hybrid recognises and validates numeric CNPJ');
    assertCondition(CpfcnpjRule::validate($validAlnumCnpj), 'Hybrid recognises and validates alphanumeric CNPJ');
    assertCondition(!CpfcnpjRule::validate('123456'), 'Incomplete document in hybrid field fails');

    // 6. Pix Tests
    echo "\n--- Testing Pix Key & Payload Generation ---\n";
    assertCondition(PixHelper::getKeyType('contato@exemplo.com.br') === 'email', 'Pix detects email key');
    assertCondition(PixHelper::getKeyType('11999999999') === 'phone', 'Pix detects phone key');
    assertCondition(PixHelper::getKeyType('52998224725') === 'cpf', 'Pix detects CPF key');
    assertCondition(PixHelper::getKeyType('11222333000181') === 'cnpj', 'Pix detects CNPJ key');
    assertCondition(PixHelper::getKeyType('123e4567-e89b-12d3-a456-426614174000') === 'evp', 'Pix detects EVP UUIDv4 key');

    $payload = PixHelper::generatePayload('contato@exemplo.com.br', 'UZIEL WEB', 'SAO PAULO', 15.50, 'PEDIDO123');
    assertCondition(str_starts_with($payload, '000201'), 'Payload starts with EMVCo indicator 000201');
    assertCondition(str_contains($payload, '540515.50'), 'Payload contains amount tag 54 with 15.50');
    assertCondition(str_contains($payload, '5802BR'), 'Payload contains country BR');
    assertCondition(strlen($payload) > 50, 'Payload length is valid');

    // Verify CRC16 of the generated payload
    $body = substr($payload, 0, -4);
    $crcExpected = substr($payload, -4);
    $crcCalculated = PixHelper::calculateCrc16(substr($body, 0, -4));
    assertCondition($crcCalculated === $crcExpected, "CRC16 checksum verified ($crcExpected === $crcCalculated)");

    // Subform Multicampo parsing & payload generation
    $singleSubformJson = json_encode([
        'pix_key'       => '52998224725',
        'merchant_name' => 'LOJA VIRTUAL',
        'merchant_city' => 'RIO DE JANEIRO',
        'amount_mode'   => 'fixed',
        'amount'        => '199.90',
        'txid'          => 'CURSO2026'
    ]);
    $parsedSingle = json_decode($singleSubformJson, true);
    assertCondition(PixRule::validate($parsedSingle['pix_key']), 'Subform pix_key passes PixRule validation');
    $subformPayload = PixHelper::generatePayload(
        $parsedSingle['pix_key'],
        $parsedSingle['merchant_name'],
        $parsedSingle['merchant_city'],
        (float) $parsedSingle['amount'],
        $parsedSingle['txid']
    );
    assertCondition(str_contains($subformPayload, '5406199.90'), 'Subform payload contains custom amount 199.90');
    assertCondition(str_contains($subformPayload, '5912LOJA VIRTUAL'), 'Subform payload contains custom merchant name');

    // Multiple repeatable rows test
    $repeatableJson = json_encode([
        'row0' => ['pix_key' => 'contato@empresa.com', 'amount_mode' => 'none'],
        'row1' => ['pix_key' => '11988887777', 'amount_mode' => 'free', 'amount' => '50.00']
    ]);
    $parsedRepeatable = json_decode($repeatableJson, true);
    $rows = array_values($parsedRepeatable);
    assertCondition(count($rows) === 2, 'Repeatable subform produces 2 separate Pix items');
    assertCondition(PixRule::validate($rows[0]['pix_key']) && PixRule::validate($rows[1]['pix_key']), 'Both repeatable Pix keys are valid');


    echo "\n============================================\n";
    echo "Results: $passed Passed, $failed Failed\n";
    echo "============================================\n";

    if ($failed > 0) {
        exit(1);
    }
}
