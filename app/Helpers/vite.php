<?php

use Illuminate\Support\HtmlString;

if (!function_exists('vite_assets')) {
    /**
     * @return HtmlString
     * @throws Exception
     */
    function vite_assets(): HtmlString
    {
        $devServerIsRunning = false;

        if (app()->environment('local')) {
            $devServerIsRunning = file_get_contents(public_path('hot')) == 'dev';
        }


        if ($devServerIsRunning) {
            return new HtmlString(<<<HTML
            <script type="module" src="http://localhost:3002/@vite/client"></script>
            <script type="module" src="http://localhost:3002/resources/js/app.js"></script>
        HTML
            );
        }
        $manifest = json_decode(file_get_contents(
            public_path('dist/manifest.json')
        ), true);
        return new HtmlString(<<<HTML
        <script type="module" src="/dist/{$manifest['resources/js/app.js']['file']}"></script>
        <link rel="stylesheet" href="/dist/{$manifest['resources/js/app.js']['css'][0]}">
    HTML
        );
    }
}
