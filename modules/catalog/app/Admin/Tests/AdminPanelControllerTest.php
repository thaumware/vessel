<?php

namespace App\Admin\Tests;

use App\Admin\Infrastructure\In\Http\Controllers\AdminPanelController;

class AdminPanelControllerTest extends AdminTestCase
{
    private AdminPanelController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new AdminPanelController();
    }

    public function test_parse_test_output_extracts_success_results(): void
    {
        $output = "PHPUnit 11.5.43 by Sebastian Bergmann...\n\n...\n\nOK (29 tests, 75 assertions)";
        
        $method = new \ReflectionMethod($this->controller, 'parseTestOutput');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->controller, $output);

        $this->assertEquals(29, $result['tests']);
        $this->assertEquals(75, $result['assertions']);
        $this->assertTrue($result['passed']);
        $this->assertEquals(0, $result['failures']);
    }

    public function test_parse_test_output_extracts_failure_results(): void
    {
        $output = "PHPUnit...\n\nTests: 50, Assertions: 100, Failures: 3, Errors: 1";
        
        $method = new \ReflectionMethod($this->controller, 'parseTestOutput');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->controller, $output);

        $this->assertEquals(50, $result['tests']);
        $this->assertEquals(100, $result['assertions']);
        $this->assertEquals(3, $result['failures']);
        $this->assertEquals(1, $result['errors']);
        $this->assertFalse($result['passed']);
    }

    public function test_parse_test_output_handles_single_test(): void
    {
        $output = "OK (1 test, 5 assertions)";
        
        $method = new \ReflectionMethod($this->controller, 'parseTestOutput');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->controller, $output);

        $this->assertEquals(1, $result['tests']);
        $this->assertEquals(5, $result['assertions']);
        $this->assertTrue($result['passed']);
    }

    public function test_get_test_suites_returns_all_suites(): void
    {
        $method = new \ReflectionMethod($this->controller, 'getTestSuites');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->controller);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        
        $suiteNames = array_column($result, 'name');
        $this->assertContains('all', $suiteNames);
        $this->assertContains('Items', $suiteNames);
        $this->assertContains('Uom', $suiteNames);
    }

    public function test_test_suite_has_required_fields(): void
    {
        $method = new \ReflectionMethod($this->controller, 'getTestSuites');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->controller);

        foreach ($result as $suite) {
            $this->assertArrayHasKey('name', $suite);
            $this->assertArrayHasKey('label', $suite);
        }
    }
}
