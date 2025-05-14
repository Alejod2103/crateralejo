<?php

namespace Crater\Http\Controllers\V1\Installation;

use Crater\Http\Controllers\Controller;
use Crater\Http\Requests\DatabaseEnvironmentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class DatabaseConfigurationController extends Controller
{
    /**
     * Simula guardar las variables del entorno sin escribir el archivo .env
     * para entornos como Railway que no permiten modificar archivos.
     */
    public function saveDatabaseEnvironment(DatabaseEnvironmentRequest $request)
    {
        // Simular configuración con éxito
        Artisan::call('config:clear');
        Artisan::call('cache:clear');

        // Ejecuta las migraciones y seeders directamente
        Artisan::call('key:generate --force');
        Artisan::call('optimize:clear');
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('storage:link');
        Artisan::call('migrate --seed --force');

        return response()->json([
            'success' => true,
        ]);
    }

    public function getDatabaseEnvironment(Request $request)
    {
        $databaseData = [];

        switch ($request->connection) {
            case 'sqlite':
                $databaseData = [
                    'database_connection' => 'sqlite',
                    'database_name' => database_path('database.sqlite'),
                ];
                break;

            case 'pgsql':
                $databaseData = [
                    'database_connection' => 'pgsql',
                    'database_host' => '127.0.0.1',
                    'database_port' => 5432,
                ];
                break;

            case 'mysql':
                $databaseData = [
                    'database_connection' => 'mysql',
                    'database_host' => '127.0.0.1',
                    'database_port' => 3306,
                ];
                break;
        }

        return response()->json([
            'config' => $databaseData,
            'success' => true,
        ]);
    }
}