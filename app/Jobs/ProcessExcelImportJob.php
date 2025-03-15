<?php

namespace App\Jobs;

use App\Events\RowCreated;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\Exception\ReaderNotOpenedException;
use DateTime;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class ProcessExcelImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Path to the Excel file
     *
     * @var string
     */
    protected string $filePath;

    /**
     * Unique Redis Key for Storage of Progress
     *
     * @var string
     */
    protected string $redisKey;

    /**
     * Batch Size
     *
     * @var int
     */
    protected int $batchSize = 100;

    public function __construct($filePath, $redisKey)
    {
        $this->filePath = $filePath;
        $this->redisKey = $redisKey;
    }

    /**
     * Process the Excel file
     *
     * @throws ReaderNotOpenedException
     * @throws IOException
     */
    public function handle(): void
    {
        $reader = ReaderEntityFactory::createXLSXReader();
        $reader->open($this->filePath);

        $errorMessages = [];
        $processedCount = 0;
        $batch = [];
        $rowCount = 0;

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rowCount++;
                if ($rowCount === 1) {
                    continue;
                }

                $cells = $row->getCells();
                $externalId = $cells[0]->getValue();
                $name = $cells[1]->getValue();
                $dateRaw = $cells[2]->getValue();

                $errors = [];
                if (!is_numeric($externalId)) {
                    $errors[] = 'external_id должен быть числом';
                }

                if (empty($name)) {
                    $errors[] = 'name обязателен';
                }

                $dateObj = DateTime::createFromFormat('d.m.Y', $dateRaw);
                if (!$dateObj) {
                    $errors[] = 'date не соответствует формату d.m.Y';
                }

                if (!empty($errors)) {
                    $errorMessages[] = $rowCount . ' - ' . implode(', ', $errors);
                    continue;
                }

                $formattedDate = $dateObj->format('Y-m-d');
                $batch[] = [
                    'external_id' => (int)$externalId,
                    'name'        => $name,
                    'date'        => $formattedDate,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];

                if (count($batch) >= $this->batchSize) {
                    DB::table('excel_rows')->insert($batch);
                    foreach ($batch as $rowData) {
                        event(new RowCreated($rowData));
                    }
                    $processedCount += count($batch);
                    Redis::set($this->redisKey, $processedCount);
                    $batch = [];
                }
            }
        }

        if (!empty($batch)) {
            DB::table('excel_rows')->insert($batch);
            foreach ($batch as $rowData) {
                event(new RowCreated($rowData));
            }
            $processedCount += count($batch);
            Redis::set($this->redisKey, $processedCount);
        }

        $reader->close();

        if (!empty($errorMessages)) {
            $errorReport = implode(PHP_EOL, $errorMessages);
            Storage::disk('local')->put('result.txt', $errorReport);
        }
    }
}
