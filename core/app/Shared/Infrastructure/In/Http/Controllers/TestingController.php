<?php

namespace App\Shared\Infrastructure\In\Http\Controllers;

use App\Shared\Application\ConfigureTestingDatabase;
use App\Shared\Application\RunTests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TestingController extends Controller
{
    public function __construct(
        private ConfigureTestingDatabase $configureDb,
        private RunTests $runTests
    ) {}

    /**
     * Muestra configuración actual de BD de testing
     */
    public function showConfig()
    {
        return response()->json([
            'current_config' => $this->configureDb->getCurrentConfig(),
            'test_suites' => $this->runTests->listTestSuites(),
        ]);
    }

    /**
     * Actualiza configuración de BD de testing en .env
     */
    public function updateConfig(Request $request)
    {
        $validated = $request->validate([
            'connection' => 'required|string|in:mysql,pgsql,sqlite',
            'host' => 'required|string',
            'port' => 'required|integer',
            'database' => 'required|string',
            'username' => 'required|string',
            'password' => 'nullable|string',
        ]);

        $result = $this->configureDb->execute($validated);

        return response()->json($result);
    }

    /**
     * Testea conexión sin guardar
     */
    public function testConnection(Request $request)
    {
        $validated = $request->validate([
            'connection' => 'required|string|in:mysql,pgsql,sqlite',
            'host' => 'required|string',
            'port' => 'required|integer',
            'database' => 'required|string',
            'username' => 'required|string',
            'password' => 'nullable|string',
        ]);

        $result = $this->configureDb->testConnection($validated);

        return response()->json($result);
    }

    /**
     * Ejecuta tests
     */
    public function runTests(Request $request)
    {
        $validated = $request->validate([
            'testsuite' => 'nullable|string',
            'filter' => 'nullable|string',
        ]);

        $result = $this->runTests->execute($validated);

        return response()->json($result);
    }

    /**
     * Lista archivos de test de un suite
     */
    public function listTestFiles(Request $request)
    {
        $directory = $request->get('directory');
        
        if (!$directory) {
            return response()->json(['files' => []]);
        }

        $files = $this->runTests->getTestFiles($directory);

        return response()->json(['files' => $files]);
    }
}
