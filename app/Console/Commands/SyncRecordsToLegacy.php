<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Console\Command;
use App\Models\
{
    Misplacement,
    LostDocument,
    SyncedMisplacement,
    PlaceEvent
};
use App\Models\Legacy\
{
    Extravio,
    Hechos,
    Objeto,
    HechosCP
};
use App\Helpers\ExtravioAdapter;
use App\Helpers\ExtravioObjectAdapter;
use App\Services\AuthApiService;
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

    protected AuthApiService $apiService;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // * initilize the api service
        $this->apiService = new AuthApiService();

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

                // * save the ID for use after
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
                $this->processLostDocuments($lostDocuments, $legacyIdExtravio);


                // * save event description and place events
                $placeEvent = PlaceEvent::where('misplacement_id', $misplacement->id)->firstOrFail();
                $this->processPlaceEvent($placeEvent, $misplacement, $legacyIdExtravio);


                // * get the identification from the API
                try
                {
                    $identification = $this->apiService->getDocumentById($misplacement->people_id, $misplacement->misplacementIdentifications->identification_type_id);
                    if(isset($identification) && !empty($identification))
                    {
                        $legacyExtravio->NUMERO_DOCUMENTO = $identification["folio"] ?? "" ;
                    }
                }
                catch (\Throwable $th)
                {
                    Log::error("Failed to fetch identification for people_id '{people_id}': {message}", [
                        "people_id" => $misplacement->people_id,
                        "message" => $th->getMessage()
                    ]);
                }


                DB::connection('sqlsrv')->commit();
                Log::info("Misplacement record with id '{id}' synced to legacy", [
                    "id" => $misplacement->id,
                    'legacy_id' => $legacyIdExtravio
                ]);

                // * save the sync record
                $syncedMisplacement->legacy_id = $legacyIdExtravio;
                $syncedMisplacement->failed = true;
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


    /**
     * processLostDocuments
     *
     * @param  Collection<LostDocument> $lostDocuments
     * @param  int|string $legacyIdExtravio
     * @return void
     */
    private function processLostDocuments($lostDocuments, $legacyIdExtravio)
    {
        foreach ($lostDocuments as $lostDocument)
        {
            // * get the legacy record
            $legacyObjeto = ExtravioObjectAdapter::fromLostDocument($lostDocument);
            $legacyObjeto->ID_EXTRAVIO = $legacyIdExtravio;

            // * check if the object is already stored by comparing the document number and owner
            $existingObjectRecord = Objeto::where([
                "ID_EXTRAVIO" => $legacyIdExtravio,
                "NUMERO_DOCUMENTO" => $lostDocument->document_number,
                "TITULAR_DOCUMENTO" => $lostDocument->document_owner,
            ])->first();

            // * skipt the record if exist
            if(isset($existingObjectRecord))
            {
                continue;
            }

            $legacyObjeto->save();
        }
    }


    /**
     * processPlaceEvent
     *
     * @param  PlaceEvent $placeEvent
     * @param  Misplacement $misplacement
     * @param  int|string $legacyIdExtravio
     * @return void
     */
    private function processPlaceEvent($placeEvent, $misplacement, $legacyIdExtravio)
    {
        // * save event description (Hechos)
        $hechos = Hechos::where("ID_EXTRAVIO", $legacyIdExtravio)->first();
        if(isset($hechos))
        {
            if($hechos->DESCRIPCION != Trim($placeEvent->description))
            {
                $hechos->DESCRIPCION = Trim($placeEvent->description);
                $hechos->save();
            }
        }
        else
        {
            $hechos = Hechos::create([
                "ID_EXTRAVIO" => $legacyIdExtravio,
                "ID_MUNICIPIO" => 0,
                "ID_LOCALIDAD" => 0,
                "ID_COLONIA" => 0,
                "ID_CALLE" => 0,
                "DESCRIPCION" => Trim($placeEvent->description),
                "FECHA" => $misplacement->registration_date
            ]);
        }


        // * save the place event
        $hechosCP = HechosCP::where("ID_EXTRAVIO", $legacyIdExtravio)->first();
        if(!isset($hechosCP))
        {
            $hechos = HechosCP::create([
                "ID_EXTRAVIO" => $legacyIdExtravio,
                "CPcodigo" => Trim($placeEvent->zipcode),
                "CPcalle" => Trim($placeEvent->street),
                "FECHA_REGISTRO" => $misplacement->registration_date
            ]);

            // * get the place info from the api
            try
            {
                $zipCodeInfo = $this->apiService->getZipCode(Trim($placeEvent->zipcode));
                if(isset($zipCodeInfo) && !empty($zipCodeInfo))
                {
                    $hechos->CPmunicipio = collect($zipCodeInfo->municipalities)->firstWhere('id', $placeEvent->municipality_api_id)?->name;
                    $hechos->CPcolonia = collect($zipCodeInfo->colonies)->firstWhere('id', $placeEvent->colony_api_id)?->name;
                }
                $hechos->save();
            }
            catch (\Throwable $th)
            {
                Log::error("Failed to fetch zip code info for '{$placeEvent->zipcode}' from the ApiService: {$th->getMessage()}");
            }
        }
    }

}
