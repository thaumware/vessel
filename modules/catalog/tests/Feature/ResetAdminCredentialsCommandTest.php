<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ResetAdminCredentialsCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        putenv('APP_INSTALLED=true');
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
    }

    public function test_command_creates_table_and_sets_credentials(): void
    {
        $this->assertFalse(Schema::hasTable('shared_config'));

        Artisan::call('admin:reset', [
            '--user' => 'nuevo',
            '--password' => 'clave',
        ]);

        $this->assertTrue(Schema::hasTable('shared_config'));

        $entries = DB::table('shared_config')->pluck('value', 'key')->all();
        $this->assertEquals('nuevo', $entries['admin.root']);
        $this->assertEquals('clave', $entries['admin.root_password']);
        $this->assertEquals('1', $entries['app.installed']);
    }
}
