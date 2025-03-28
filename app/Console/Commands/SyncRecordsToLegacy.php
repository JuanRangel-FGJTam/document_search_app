<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use App\Models\
{
    Misplacement,
    LostDocument,
    SyncedMisplacement
};
use App\Models\Legacy\
{
    Extravio,
    Objeto
};
use App\Helpers\ExtravioAdapter;
use App\Helpers\ExtravioObjectAdapter;

use Exception;
use Throwable;

class SyncRecordsToLegacy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-records-to-legacy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Send the local records of 'Encuesta', 'Extravios' and 'Objetos' to the legacy database";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // * get the pending local records that are not in the legacy database
        $misplacements = Misplacement::whereDoesntHave('syncedMisplacement')
            ->orWhereHas('syncedMisplacement', function ($query) {
                $query->where('failed', true);
            })
            ->get();

        Log::info("Starting the job to synchronize the misplacements, pending: " . count($misplacements));

        // * loop through the records
        foreach ($misplacements as $misplacement)
        {

            // * create the synced record
            $syncedMisplacement = SyncedMisplacement::firstOrNew([
                'misplacement_id' => $misplacement->id
            ]);
            $syncedMisplacement->save();

            DB::connection('sqlsrv')->beginTransaction();
            try
            {
                // * cast the local record to the legacy record
                $legacyExtravio = ExtravioAdapter::fromMisplacement($misplacement);
                if($legacyExtravio === null)
                {
                    throw new Exception('The legacy record could not be created');
                }

                $legacyIdExtravio = $legacyExtravio->ID_EXTRAVIO;

                // * check if the legacy record already exists
                $existingRecord = Extravio::where('ID_EXTRAVIO', $legacyIdExtravio)->first();
                if (Extravio::where('ID_EXTRAVIO', $legacyIdExtravio)->first())
                {
                    $existingRecord = Extravio::where('ID_EXTRAVIO', $legacyExtravio->ID_EXTRAVIO)->first();
                    $existingRecord->fill($legacyExtravio->toArray());
                    $existingRecord->save();
                }
                else
                {
                    $legacyExtravio->save();
                }


                // * get the local missing documents
                $lostDocuments = LostDocument::where('misplacement_id', $misplacement->id)->get();
                foreach ($lostDocuments as $lostDocument)
                {
                    // * get the legacy record
                    $legacyObjeto = ExtravioObjectAdapter::fromLostDocument($lostDocument);
                    $legacyObjeto->ID_EXTRAVIO = $legacyIdExtravio;
                    $legacyObjeto->save();
                }

                DB::connection('sqlsrv')->commit();
                Log::info("Misplacement record with id '{id}' synced to legacy", [
                    "id" => $misplacement->id,
                    'legacy_id' => $legacyIdExtravio

                ]);

                // * save the sync record
                $syncedMisplacement->legacy_id = $legacyIdExtravio;
                $syncedMisplacement->failed = false;
                $syncedMisplacement->message = null;
                $syncedMisplacement->save();

            }
            catch(Throwable $th)
            {
                DB::connection('sqlsrv')->rollBack();
                Log::error("Fail at creating the legacy record for the misplacement with id '{id}': {message}", [
                    "id" => $misplacement->id,
                    "message" => $th->getMessage()
                ]);

                // * save the sync record
                $syncedMisplacement->failed = true;
                $syncedMisplacement->message = $th->getMessage();
                $syncedMisplacement->save();
                continue;
            }
            finally
            {
                Log::info("---------------------------------------------");
            }
        }
        Log::info("Job finished");
    }

}
