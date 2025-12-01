<?php

namespace App\Uom\Application\Services;

/**
 * Servicio de traducción para UoM.
 * 
 * Uso:
 *   $translator = new UomTranslator('es');
 *   $translator->measure('kg'); // ['name' => 'Kilogramo', 'description' => '...']
 *   $translator->category('mass'); // ['name' => 'Masa', 'description' => '...']
 */
class UomTranslator
{
    private string $locale;
    private array $measures = [];
    private array $categories = [];
    private string $fallbackLocale = 'en';

    public function __construct(?string $locale = null)
    {
        $this->locale = $locale ?? app()->getLocale() ?? 'es';
        $this->loadTranslations();
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        $this->loadTranslations();
        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Obtener traducción de una medida.
     */
    public function measure(string $code, ?string $field = null): array|string|null
    {
        $translation = $this->measures[$code] ?? null;

        if ($translation === null) {
            return $field ? null : ['name' => $code, 'description' => null];
        }

        if ($field) {
            return $translation[$field] ?? null;
        }

        return $translation;
    }

    /**
     * Obtener nombre traducido de una medida.
     */
    public function measureName(string $code): string
    {
        return $this->measure($code, 'name') ?? $code;
    }

    /**
     * Obtener traducción de una categoría.
     */
    public function category(string $code, ?string $field = null): array|string|null
    {
        $translation = $this->categories[$code] ?? null;

        if ($translation === null) {
            return $field ? null : ['name' => $code, 'description' => null];
        }

        if ($field) {
            return $translation[$field] ?? null;
        }

        return $translation;
    }

    /**
     * Obtener nombre traducido de una categoría.
     */
    public function categoryName(string $code): string
    {
        return $this->category($code, 'name') ?? $code;
    }

    /**
     * Obtener todas las traducciones de medidas.
     */
    public function allMeasures(): array
    {
        return $this->measures;
    }

    /**
     * Obtener todas las traducciones de categorías.
     */
    public function allCategories(): array
    {
        return $this->categories;
    }

    /**
     * Obtener locales disponibles.
     */
    public static function availableLocales(): array
    {
        $langPath = __DIR__ . '/../../Infrastructure/Out/Data/lang';
        $dirs = glob($langPath . '/*', GLOB_ONLYDIR);
        
        return array_map(fn($dir) => basename($dir), $dirs);
    }

    private function loadTranslations(): void
    {
        $this->measures = $this->loadFile('measures');
        $this->categories = $this->loadFile('categories');
    }

    private function loadFile(string $type): array
    {
        $basePath = __DIR__ . '/../../Infrastructure/Out/Data/lang';
        
        // Intentar cargar locale principal
        $file = "{$basePath}/{$this->locale}/{$type}.php";
        if (file_exists($file)) {
            return require $file;
        }

        // Fallback
        $fallbackFile = "{$basePath}/{$this->fallbackLocale}/{$type}.php";
        if (file_exists($fallbackFile)) {
            return require $fallbackFile;
        }

        return [];
    }
}
