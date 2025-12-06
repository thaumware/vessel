<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure;

final class ModuleRegistry
{
    /** @var array<string, array{enabled: bool, ws_enabled: bool|null}> */
    private array $modules;
    private ConfigStore $store;

    /**
     * @param array<string, array<string, mixed>> $modules
     */
    public function __construct(array $modules, ConfigStore $store)
    {
        $this->modules = $modules;
        $this->store = $store;
    }

    public function enabled(string $module): bool
    {
        $override = $this->store->get("modules.{$module}.enabled");
        if ($override !== null) {
            return filter_var($override, FILTER_VALIDATE_BOOLEAN);
        }

        return (bool)($this->modules[$module]['enabled'] ?? false);
    }

    public function wsEnabled(string $module): bool
    {
        $override = $this->store->get("modules.{$module}.ws_enabled");
        if ($override !== null) {
            return filter_var($override, FILTER_VALIDATE_BOOLEAN);
        }

        return (bool)($this->modules[$module]['ws_enabled'] ?? false);
    }

    public function setEnabled(string $module, bool $enabled): void
    {
        if (!isset($this->modules[$module])) {
            return;
        }

        $this->modules[$module]['enabled'] = $enabled;
        $this->store->set("modules.{$module}.enabled", $enabled);
    }

    public function setWsEnabled(string $module, bool $enabled): void
    {
        if (!isset($this->modules[$module])) {
            return;
        }

        $this->modules[$module]['ws_enabled'] = $enabled;
        $this->store->set("modules.{$module}.ws_enabled", $enabled);
    }
}
