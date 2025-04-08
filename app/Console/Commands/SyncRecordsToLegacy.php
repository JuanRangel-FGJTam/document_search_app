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
    DomicilioCP,
    HechosCP
};
use App\Helpers\ExtravioAdapter;
use App\Helpers\ExtravioObjectAdapter;
use App\Services\AuthApiService;
use Exception;
use Throwable;
use DateTime;

class SyncRecordsToLegacy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-records-to-legacy {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Send the local records of 'Encuesta', 'Extravios' and 'Objetos' to the legacy database";

    protected AuthApiService $apiService;

    /**
     * Execute the console command.
     * pass date as parameter
     */
    public function handle()
    {
        $today = new DateTime();
        $date = $this->argument('date');
        
        if($date) {
            $yesterday = new DateTime($date);
        } else {
            $yesterday = $today->modify('-1 day');
        }

        Log::info("Starting the job to synchronize records for date: " . $yesterday->format('Y-m-d'));

        // * initilize the api service
        $this->apiService = new AuthApiService();

        $misplacements = Misplacement::whereDate('created_at', $yesterday->format('Y-m-d'))
            ->orWhereDate('updated_at', $yesterday->format('Y-m-d'))
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
                $legacyExtravio = ExtravioAdapter::fromMisplacement($misplacement, $this->apiService);
                if($legacyExtravio === null)
                {
                    throw new Exception('The legacy record could not be created');
                }

                // * save the ID for use after
                $legacyIdExtravio = $legacyExtravio->ID_EXTRAVIO;

                $existingRecord = Extravio::where('ID_EXTRAVIO', $legacyIdExtravio)->first();
                if($existingRecord)
                {
                    Log::info("Updating existing Extravio record with ID: {$legacyIdExtravio}");
                    $existingRecord->fill($legacyExtravio->toArray());
                    $existingRecord->save();
                    continue;
                }

                $legacyExtravio->save();

                print("Continue create a new Extravio record with id '{$misplacement->id}'\n");

                // * get the local missing documents
                $lostDocuments = LostDocument::where('misplacement_id', $misplacement->id)->get();
                $this->processLostDocuments($lostDocuments, $legacyIdExtravio);

                // * save event description and place events
                $placeEvent = PlaceEvent::where('misplacement_id', $misplacement->id)->firstOrFail();
                $this->processPlaceEvent($placeEvent, $misplacement, $legacyIdExtravio);

                // Save DomicilioCP record
                $existingDomicilioCP = DomicilioCP::where('ID_EXTRAVIO', $legacyIdExtravio)->first();
                if(!$existingDomicilioCP)
                {
                    $people_api_data = $this->apiService->getPersonById($misplacement->people_id);
                    if ($people_api_data) {
                        $people_address = $people_api_data['address'];
                        if ($people_address) {
                            $domicilioCP = new DomicilioCP();
                            $domicilioCP->ID_EXTRAVIO = $legacyIdExtravio;
                            $domicilioCP->CPCodigo = $people_address['zipCode'];
                            $domicilioCP->CPmunicipio = $people_address['municipalityName'];
                            $domicilioCP->CPcolonia = $people_address['colonyName'];
                            $domicilioCP->CPcalle = $people_address['street'];
                            $domicilioCP->FECHA_REGISTRO = $misplacement->registration_date . " 00:00:00.000";
                            $domicilioCP->save();
                        }
                    } else {
                        Log::info("People API data not found for people_id: " . $misplacement->people_id);
                    }
                } else {
                    Log::info("DomicilioCP record already exists for Extravio ID: " . $legacyIdExtravio);
                }

                // * get the identification from the API
                try
                {
                    $identification = $this->apiService->getDocumentById(
                        $misplacement->people_id,
                        $misplacement->misplacementIdentifications->identification_type_id
                    );

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

                $legacyExtravio->save();

                // * save the sync record
                $syncedMisplacement->legacy_id = $legacyIdExtravio;
                $syncedMisplacement->failed = false;
                $syncedMisplacement->message = null;
                $syncedMisplacement->save();

                print("Misplacement record with id '{$misplacement->id}' synced to legacy\n");
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

        print("Job finished");
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
            if($existingObjectRecord)
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
        if(!$hechosCP)
        {
            $municipality = $this->apiService->getMunicipalityById($placeEvent->municipality_api_id);
            $colony = $this->apiService->getColonyById($placeEvent->colony_api_id);
            $municipalityName = null;
            $colonyName = null;

            if (isset($municipality) && !empty($municipality)) {
                $municipalityName = $municipality['name'] ?? null;
            }

            if (isset($colony) && !empty($colony)) {
                $colonyName = $colony['name'] ?? null;
            }

            $hechos = HechosCP::create([
                "ID_EXTRAVIO" => $legacyIdExtravio,
                "CPcodigo" => Trim($placeEvent->zipcode),
                "CPmunicipio" => $municipalityName,
                "CPcolonia" => $colonyName,
                "CPcalle" => Trim($placeEvent->street),
                "FECHA_REGISTRO" => $misplacement->registration_date
            ]);
        }
    }
}
