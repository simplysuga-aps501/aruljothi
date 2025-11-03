<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DistancePincodesImport;

class ImportDistancePincodes extends Command
{
    protected $signature = 'import:distance-pincodes {file}';
    protected $description = 'Import distance pincodes from an Excel/CSV file';

    public function handle()
    {
        $file = $this->argument('file');

        $this->info("Importing from: $file");

        Excel::import(new DistancePincodesImport, $file);

        $this->info("✅ Import completed successfully!");
    }
}
