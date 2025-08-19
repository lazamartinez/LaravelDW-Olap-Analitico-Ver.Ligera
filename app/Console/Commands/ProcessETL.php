<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\ETLController;

class ProcessETL extends Command
{
    protected $signature = 'etl:process';
    protected $description = 'Procesa el ETL para actualizar el data warehouse';

    public function handle()
    {
        $this->info('Iniciando proceso ETL...');
        
        $etlController = new ETLController();
        $response = $etlController->procesarETL();
        
        $this->info('ETL completado exitosamente');
        $this->line($response->getContent());
        
        return 0;
    }
}