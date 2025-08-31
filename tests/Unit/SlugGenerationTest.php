<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SlugGenerationTest extends TestCase
{
    /**
     * Test slug generation from store names
     */
    public function test_slug_generation_from_name_works_correctly()
    {
        $testCases = [
            'Mi Tienda Online' => 'mi-tienda-online',
            'Café & Restaurante' => 'cafe-restaurante',
            'Tienda 123' => 'tienda-123',
            'Ñoño\'s Store' => 'ono-s-store',
            'UPPERCASE STORE' => 'uppercase-store',
            '   Spaces   ' => 'spaces',
            'Multiple---Hyphens' => 'multiple-hyphens',
            'Acentós y Ñoñerías' => 'acentos-y-onerias',
            '' => '',
            'Special!@#$%Characters' => 'special-characters',
            'Tienda con números 123 y símbolos!' => 'tienda-con-numeros-123-y-simbolos'
        ];
        
        foreach ($testCases as $input => $expected) {
            $result = $this->generateSlugFromName($input);
            $this->assertEquals($expected, $result, "Failed for input: '{$input}'");
        }
    }

    /**
     * Helper method to test slug generation (mirrors JavaScript logic)
     */
    private function generateSlugFromName($name)
    {
        if (!$name) return '';
        
        $slug = strtolower(trim($name));
        
        // Replace accented characters
        $accents = [
            'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a', 'ā' => 'a', 'ã' => 'a',
            'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e', 'ē' => 'e',
            'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i', 'ī' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o', 'ō' => 'o', 'õ' => 'o',
            'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u', 'ū' => 'u',
            'ñ' => 'n', 'ç' => 'c'
        ];
        $slug = strtr($slug, $accents);
        
        // Replace spaces and special characters with hyphens
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        
        // Remove multiple consecutive hyphens
        $slug = preg_replace('/-+/', '-', $slug);
        
        // Remove leading and trailing hyphens
        $slug = trim($slug, '-');
        
        return $slug;
    }
}