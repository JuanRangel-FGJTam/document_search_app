<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
class DeleteDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-documents';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete Lost Documents for people';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        // Ruta de la carpeta dentro de storage/public
        $directory = 'public';

        $directories = Storage::allDirectories($directory); // Obtiene todas las subcarpetas

        // Eliminar carpetas (recursivamente)
        if (count($directories) > 0) {
            foreach ($directories as $dir) {
                Storage::deleteDirectory($dir); // Elimina cada carpeta y su contenido
                $this->info("Carpeta eliminada: $dir");
            }
        } else {
            $this->info('No hay carpetas para eliminar en la carpeta.');
        }
    }
}
