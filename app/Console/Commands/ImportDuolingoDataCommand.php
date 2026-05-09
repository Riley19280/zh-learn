<?php

namespace App\Console\Commands;

use App\Library\DuolingoImporter;
use App\Library\VocabularyStats;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('duolingo:import')]
#[Description('Import all captured request files from database/data into the database')]
class ImportDuolingoDataCommand extends Command {
    public function handle(DuolingoImporter $importer, VocabularyStats $stats): int {
        $rawPath = database_path('data');

        if (!is_dir($rawPath)) {
            $this->error("Raw directory not found: {$rawPath}");

            return self::FAILURE;
        }

        $files = glob("{$rawPath}/*.json");

        if (empty($files)) {
            $this->warn('No JSON files found in database/data.');

            return self::SUCCESS;
        }

        $this->info('Found ' . count($files) . ' file(s). Importing…');
        $this->newLine();

        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), associative: true);

            if (!is_array($data)) {
                $this->warn('Skipping invalid JSON: ' . basename($file));

                continue;
            }

            // Support both array-of-requests format and single response body
            $objects = array_key_exists(0, $data)
                ? collect($data)->pluck('responseBody')->all()
                : [$data];

            $this->line('  Processing <fg=cyan>' . basename($file) . '</> (' . count($objects) . ' object(s))');

            foreach ($objects as $object) {
                $importer->processResponseBody($object);
            }
        }

        $summary = $stats->summary();

        $this->newLine();
        $this->table(
            ['Metric', 'Value'],
            [
                ['Unique words', $summary['uniqueWords']],
                ['Unique characters', $summary['uniqueCharacters']],
            ]
        );

        return self::SUCCESS;
    }
}
