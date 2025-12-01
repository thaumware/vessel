<?php

namespace App\Uom\Tests\Application;

use App\Uom\Application\Services\UomTranslator;
use App\Uom\Tests\UomTestCase;

class UomTranslatorTest extends UomTestCase
{
    public function test_translates_measure_in_spanish(): void
    {
        $translator = new UomTranslator('es');

        $kg = $translator->measure('kg');

        $this->assertEquals('Kilogramo', $kg['name']);
        $this->assertStringContainsString('masa', strtolower($kg['description']));
    }

    public function test_translates_measure_in_english(): void
    {
        $translator = new UomTranslator('en');

        $kg = $translator->measure('kg');

        $this->assertEquals('Kilogram', $kg['name']);
        $this->assertStringContainsString('mass', strtolower($kg['description']));
    }

    public function test_measure_name_shortcut(): void
    {
        $translator = new UomTranslator('es');

        $this->assertEquals('Litro', $translator->measureName('l'));
        $this->assertEquals('Metro', $translator->measureName('m'));
    }

    public function test_translates_category_in_spanish(): void
    {
        $translator = new UomTranslator('es');

        $mass = $translator->category('mass');

        $this->assertEquals('Masa', $mass['name']);
    }

    public function test_translates_category_in_english(): void
    {
        $translator = new UomTranslator('en');

        $mass = $translator->category('mass');

        $this->assertEquals('Mass', $mass['name']);
    }

    public function test_category_name_shortcut(): void
    {
        $translator = new UomTranslator('es');

        $this->assertEquals('Volumen', $translator->categoryName('volume'));
        $this->assertEquals('Longitud', $translator->categoryName('length'));
    }

    public function test_returns_code_for_unknown_measure(): void
    {
        $translator = new UomTranslator('es');

        $unknown = $translator->measure('unknown_code');

        $this->assertEquals('unknown_code', $unknown['name']);
    }

    public function test_returns_code_for_unknown_category(): void
    {
        $translator = new UomTranslator('es');

        $this->assertEquals('unknown', $translator->categoryName('unknown'));
    }

    public function test_can_change_locale(): void
    {
        $translator = new UomTranslator('es');
        $this->assertEquals('Kilogramo', $translator->measureName('kg'));

        $translator->setLocale('en');
        $this->assertEquals('Kilogram', $translator->measureName('kg'));
    }

    public function test_get_all_measures(): void
    {
        $translator = new UomTranslator('es');

        $all = $translator->allMeasures();

        $this->assertIsArray($all);
        $this->assertArrayHasKey('kg', $all);
        $this->assertArrayHasKey('m', $all);
        $this->assertArrayHasKey('l', $all);
    }

    public function test_get_all_categories(): void
    {
        $translator = new UomTranslator('es');

        $all = $translator->allCategories();

        $this->assertIsArray($all);
        $this->assertArrayHasKey('mass', $all);
        $this->assertArrayHasKey('volume', $all);
        $this->assertArrayHasKey('length', $all);
    }

    public function test_available_locales(): void
    {
        $locales = UomTranslator::availableLocales();

        $this->assertContains('es', $locales);
        $this->assertContains('en', $locales);
    }

    public function test_fallback_to_english_for_unknown_locale(): void
    {
        $translator = new UomTranslator('fr'); // No existe francés

        // Debe usar fallback a inglés
        $kg = $translator->measure('kg');
        $this->assertEquals('Kilogram', $kg['name']);
    }
}
