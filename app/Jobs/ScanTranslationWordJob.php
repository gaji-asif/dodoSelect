<?php

namespace App\Jobs;

use App\Models\Translation;
use DateTime;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ScanTranslationWordJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $directories = [
            base_path('app'),
            base_path('resources/views')
        ];

        $translationWords = [];
        foreach ($directories as $directory) {
            $filesInControllers = $this->scanDirectory($directory);
            $pattern = '/__\((\'|\")translation.([^\'\"]*|\%)(\'|\")\)/';

            foreach ($filesInControllers as $file) {
                $openFile = fopen($file, 'r');
                if ($openFile) {
                    while (($codeInCurrentLine = fgets($openFile)) !== false) {
                        $trimmedCode = trim($codeInCurrentLine);
                        $translationMethod = preg_match_all($pattern, $trimmedCode, $matches);

                        if ($translationMethod) {
                            for ($i=0; $i < count($matches[2]); $i++) {
                                array_push($translationWords, $matches[2][$i]);
                            }
                        }
                    }

                    fclose($openFile);
                }
            }
        }


        $translationDataToStore = [];

        $translationWords = array_unique($translationWords);
        foreach ($translationWords as $keyword) {
            array_push($translationDataToStore, [
                'keyword' => $keyword,
                'lang_en' => $this->getActualLanguageFromKey($keyword),
                'created_at' => new DateTime()
            ]);
        }

        Translation::insert($translationDataToStore);

        /**
         * Remove duplicated keyword entries from translation table
         */
        $translations = Translation::orderBy('id', 'asc')->get();
        $uniqueKeyword = $translations->unique('keyword');
        $uniqueIDs = $uniqueKeyword->values()->pluck('id')->toArray();

        Translation::whereNotIn('id', $uniqueIDs)->delete();

        /**
         * Forget translations cache
         */
        Cache::forget('translations');
    }


    private function scanDirectory($directory, &$results = [])
    {
        $files = scandir($directory);

        foreach ($files as $file) {
            $path = realpath($directory . DIRECTORY_SEPARATOR . $file);

            if (is_file($path)) {
                $results[] = $path;
            } else if ($file != '.' && $file != '..') {
                $this->scanDirectory($path, $results);
            }
        }

        return $results;
    }


    private function getActualLanguageFromKey(string $keyword)
    {
        $explodedKeyword = explode('.', $keyword);

        return ucfirst(Str::replace('_', ' ', end($explodedKeyword)));
    }
}
