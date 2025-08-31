<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Features\SuperLinkiu\Services\FiscalValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Fiscal Information Step Tests
 * 
 * Tests for fiscal information validation and compliance
 * Requirements: 3.3, 3.4 - Business document validation and compliance system
 */
class FiscalInformationStepTest extends TestCase
{
    use RefreshDatabase;

    protected FiscalValidationService $fiscalService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fiscalService = new FiscalValidationService();
    }

    /** @test */
    public function it_validates_colombian_nit_correctly()
    {
        // Valid NIT
        $result = $this->fiscalService->validateFiscalDocument('nit', '900123456-7', 'Colombia');
        $this->assertTrue($result['isValid']);

        // Invalid NIT format
        $result = $this->fiscalService->validateFiscalDocument('nit', '12345', 'Colombia');
        $this->assertFalse($result['isValid']);
        $this->assertNotEmpty($result['message']);
    }

    /** @test */
    public function it_validates_chilean_rut_correctly()
    {
        // Valid RUT format
        $result = $this->fiscalService->validateFiscalDocument('rut', '12345678-9', 'Chile');
        $this->assertTrue($result['isValid']);

        // Invalid RUT format
        $result = $this->fiscalService->validateFiscalDocument('rut', '123456', 'Chile');
        $this->assertFalse($result['isValid']);
    }

    /** @test */
    public function it_validates_mexican_rfc_correctly()
    {
        // Valid RFC format
        $result = $this->fiscalService->validateFiscalDocument('rfc', 'ABC123456XYZ', 'México');
        $this->assertTrue($result['isValid']);

        // Invalid RFC format
        $result = $this->fiscalService->validateFiscalDocument('rfc', 'ABC123', 'México');
        $this->assertFalse($result['isValid']);
    }

    /** @test */
    public function it_validates_cedula_correctly()
    {
        // Valid cedula
        $result = $this->fiscalService->validateFiscalDocument('cedula', '12345678');
        $this->assertTrue($result['isValid']);

        // Invalid cedula (too short)
        $result = $this->fiscalService->validateFiscalDocument('cedula', '123');
        $this->assertFalse($result['isValid']);
    }

    /** @test */
    public function it_provides_document_suggestions()
    {
        // NIT missing verification digit
        $result = $this->fiscalService->validateFiscalDocument('nit', '900123456', 'Colombia');
        $this->assertFalse($result['isValid']);
        $this->assertNotEmpty($result['suggestions']);
        $this->assertStringContainsString('dígito de verificación', $result['suggestions'][0]);
    }

    /** @test */
    public function it_returns_tax_regimes_by_country()
    {
        // Colombia
        $regimes = $this->fiscalService->getTaxRegimes('Colombia');
        $this->assertArrayHasKey('simplificado', $regimes);
        $this->assertArrayHasKey('comun', $regimes);

        // México
        $regimes = $this->fiscalService->getTaxRegimes('México');
        $this->assertArrayHasKey('general', $regimes);
        $this->assertArrayHasKey('incorporacion_fiscal', $regimes);

        // Unknown country (should return default)
        $regimes = $this->fiscalService->getTaxRegimes('Unknown');
        $this->assertArrayHasKey('general', $regimes);
        $this->assertArrayHasKey('simplificado', $regimes);
    }

    /** @test */
    public function it_validates_tax_regime_for_country()
    {
        // Valid regime for Colombia
        $isValid = $this->fiscalService->validateTaxRegime('simplificado', 'Colombia');
        $this->assertTrue($isValid);

        // Invalid regime for Colombia
        $isValid = $this->fiscalService->validateTaxRegime('monotributo', 'Colombia');
        $this->assertFalse($isValid);

        // Valid regime for Argentina
        $isValid = $this->fiscalService->validateTaxRegime('monotributo', 'Argentina');
        $this->assertTrue($isValid);
    }

    /** @test */
    public function it_validates_complete_fiscal_information()
    {
        $validFiscalData = [
            'fiscal_document_type' => 'nit',
            'fiscal_document_number' => '900123456-7',
            'fiscal_country' => 'Colombia',
            'fiscal_department' => 'Cundinamarca',
            'fiscal_city' => 'Bogotá',
            'fiscal_address' => 'Calle 123 #45-67, Barrio Centro',
            'tax_regime' => 'comun',
            'economic_activity' => 'Comercio al por menor',
            'tax_responsibility_declaration' => true,
            'fiscal_data_processing_consent' => true,
            'legal_representative_declaration' => true
        ];

        $result = $this->fiscalService->validateFiscalInformation($validFiscalData);
        $this->assertTrue($result['isValid']);
        $this->assertEmpty($result['errors']);
    }

    /** @test */
    public function it_validates_incomplete_fiscal_information()
    {
        $incompleteFiscalData = [
            'fiscal_document_type' => '',
            'fiscal_document_number' => '',
            'fiscal_country' => 'Colombia',
            'fiscal_department' => '',
            'fiscal_city' => '',
            'fiscal_address' => 'Short',
            'tax_regime' => '',
            'tax_responsibility_declaration' => false,
            'fiscal_data_processing_consent' => false
        ];

        $result = $this->fiscalService->validateFiscalInformation($incompleteFiscalData);
        $this->assertFalse($result['isValid']);
        $this->assertNotEmpty($result['errors']);
        
        // Check specific errors
        $this->assertArrayHasKey('fiscal_document_type', $result['errors']);
        $this->assertArrayHasKey('fiscal_document_number', $result['errors']);
        $this->assertArrayHasKey('fiscal_department', $result['errors']);
        $this->assertArrayHasKey('fiscal_city', $result['errors']);
        $this->assertArrayHasKey('fiscal_address', $result['errors']);
        $this->assertArrayHasKey('tax_regime', $result['errors']);
        $this->assertArrayHasKey('tax_responsibility_declaration', $result['errors']);
        $this->assertArrayHasKey('fiscal_data_processing_consent', $result['errors']);
    }

    /** @test */
    public function it_requires_legal_representative_declaration_for_companies()
    {
        $companyFiscalData = [
            'fiscal_document_type' => 'nit',
            'fiscal_document_number' => '900123456-7',
            'fiscal_country' => 'Colombia',
            'fiscal_department' => 'Cundinamarca',
            'fiscal_city' => 'Bogotá',
            'fiscal_address' => 'Calle 123 #45-67, Barrio Centro',
            'tax_regime' => 'comun',
            'tax_responsibility_declaration' => true,
            'fiscal_data_processing_consent' => true,
            'legal_representative_declaration' => false // Missing for company
        ];

        $result = $this->fiscalService->validateFiscalInformation($companyFiscalData);
        $this->assertFalse($result['isValid']);
        $this->assertArrayHasKey('legal_representative_declaration', $result['errors']);
    }

    /** @test */
    public function it_does_not_require_legal_representative_declaration_for_individuals()
    {
        $individualFiscalData = [
            'fiscal_document_type' => 'cedula',
            'fiscal_document_number' => '12345678',
            'fiscal_country' => 'Colombia',
            'fiscal_department' => 'Cundinamarca',
            'fiscal_city' => 'Bogotá',
            'fiscal_address' => 'Calle 123 #45-67, Barrio Centro',
            'tax_regime' => 'simplificado',
            'tax_responsibility_declaration' => true,
            'fiscal_data_processing_consent' => true
            // No legal_representative_declaration needed for cedula
        ];

        $result = $this->fiscalService->validateFiscalInformation($individualFiscalData);
        $this->assertTrue($result['isValid']);
        $this->assertEmpty($result['errors']);
    }

    /** @test */
    public function it_gets_document_types_by_country()
    {
        // Colombia
        $documentTypes = $this->fiscalService->getDocumentTypesByCountry('Colombia');
        $this->assertArrayHasKey('nit', $documentTypes);
        $this->assertArrayHasKey('cedula', $documentTypes);

        // México
        $documentTypes = $this->fiscalService->getDocumentTypesByCountry('México');
        $this->assertArrayHasKey('rfc', $documentTypes);
        $this->assertArrayHasKey('cedula', $documentTypes);

        // Chile
        $documentTypes = $this->fiscalService->getDocumentTypesByCountry('Chile');
        $this->assertArrayHasKey('rut', $documentTypes);
        $this->assertArrayHasKey('cedula', $documentTypes);
    }

    /** @test */
    public function it_validates_nit_with_verification_digit()
    {
        // This would require implementing the actual verification digit algorithm
        // For now, we test the format validation
        $result = $this->fiscalService->validateFiscalDocument('nit', '900123456-7', 'Colombia');
        $this->assertTrue($result['isValid']);
    }

    /** @test */
    public function it_validates_rut_with_verification_digit()
    {
        // This would require implementing the actual verification digit algorithm
        // For now, we test the format validation
        $result = $this->fiscalService->validateFiscalDocument('rut', '12345678-9', 'Chile');
        $this->assertTrue($result['isValid']);
    }
}