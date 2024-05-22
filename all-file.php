<?php
function compressCSS($cssContent)
{
    $postData = array(
        'css_text' => $cssContent,
        'template' => '3',
        'optimise_shorthands' => '1',
        'compress_c' => '1',
        'compress_fw' => '1',
        'rbs' => '1',
        'remove_last_sem' => '1',
        'post' => 'true'
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://csscompressor.com/compress.php');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    $response = curl_exec($ch);
    curl_close($ch);

    $json = json_decode($response, true);
    if (isset($json['status']) && $json['status'] === '200') {
        return $json['mini'];
    } else {
        return ''; //* An empty string may be returned or error handling may occur when compression fails.
    }
}
function compressAndSaveFiles($sourceDir, $outputDir, $fileName, $explanation, $data_contributors_clear)
{
    $cssContent = '';
    $cssContentMin = '';
    $jsContent = '';
    $jsContentMin = '';

    if (!file_exists($outputDir)) {
        mkdir($outputDir, 0777, true);
    }

    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourceDir));
    foreach ($files as $file) {
        if ($file->isFile() && in_array($file->getExtension(), ['css', 'js'])) {
            $content = file_get_contents($file->getPathname());

            // * Remove comments and unnecessary whitespaces from CSS
            if ($file->getExtension() === 'css') {
                // * css raw
                $cssContent .= trim($content) . PHP_EOL;

                // * css min
                // * Remove CSS comments
                if ($data_contributors_clear) {
                    // $content = preg_replace('!/\*.*?\*/!s', '', $content);
                    $content = preg_replace('/\/\*.*?\*\//s', '', $content);
                } else {
                    // * Remove CSS comments except the ones starting with "/* *"
                    $content = preg_replace('/\/\*(?!\s*\*)(.|\n)*?\*\//s', '', $content);
                }
                // * Replace multiple whitespaces with a single space
                $content = preg_replace('/\s+/', ' ', $content);
                // * Remove unnecessary whitespaces
                $content = str_replace([': ', ', ', '; ', ' {', '{ ', '} '], [':', ',', ';', '{', '{', '}'], $content);

                // * comment edit
                // * comment line skip
                $content = preg_replace('/([^\/])\/\*/', "$1\n/*", $content);

                // * line skip
                $content = preg_replace('/\*\//', "*/\n", $content);

                // * license line skip
                $content = preg_replace('/([^\/])\*\s*\*\s*/', "$1\n * * ", $content);

                $cssContentMin .= trim($content) . PHP_EOL;
            }

            // * Remove comments and unnecessary whitespaces from JS
            if ($file->getExtension() === 'js') {
                // * js raw
                $jsContent .= trim($content) . PHP_EOL;

                // * js min
                // * Remove JS comments
                if ($data_contributors_clear) {
                    // $content = preg_replace('/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/', '', $content);
                    $content = preg_replace('/\/\*(.|[\r\n])*?\*\/|\/\/.*/', '', $content);
                } else {
                    $content = preg_replace('/\/\*(?!\s*\*)(.|\n)*?\*\/|\/\/(?!.*?\/\* *).*$/m', '', $content);
                }
                // * Replace horizontal whitespaces with a single space
                $content = preg_replace('/\h+/', ' ', $content);
                // * Remove vertical whitespaces
                $content = preg_replace('/\v+/', '', $content);
                // * Remove extra spaces around specific characters
                $content = preg_replace('/\s*([{}:;,()=<>+\-*\/])\s*/', '$1', $content);

                // * comment edit
                // * comment line skip
                $content = preg_replace('/([^\/])\/\*/', "$1\n/*", $content);

                // * line skip
                $content = preg_replace('/\*\//', "*/\n", $content);

                // * license line skip
                $content = preg_replace('/([^\/])\*\s*\*\s*/', "$1\n * * ", $content);

                $jsContentMin .= trim($content) . PHP_EOL;
            }
        }
    }

    // * css compress
    // $cssContentMin = compressCSS($cssContentMin);

    // * css raw write
    if (!empty($cssContent)) {
        $cssFile = fopen($outputDir . '/' . $fileName . '-all.css', 'w');
        fwrite($cssFile, $explanation . $cssContent);
        fclose($cssFile);
    }
    // * js raw write
    if (!empty($jsContent)) {
        $jsFile = fopen($outputDir . '/' . $fileName . '-all.js', 'w');
        fwrite($jsFile, $explanation . $jsContent);
        fclose($jsFile);
    }
    // * css min write
    if (!empty($cssContentMin)) {
        $cssFile = fopen($outputDir . '/' . $fileName . '-all-min.css', 'w');
        fwrite($cssFile, $explanation . $cssContentMin);
        fclose($cssFile);
    }
    // * js min write
    if (!empty($jsContentMin)) {
        $jsFile = fopen($outputDir . '/' . $fileName . '-all-min.js', 'w');
        fwrite($jsFile, $explanation . $jsContentMin);
        fclose($jsFile);
    }
}

// * settings
$explanation = '/*  * * example v1.0.0 (--)
    * * Copyright 2021-2023 The example Authors
    * * Licensed (--)
    * * Update (' . date('Y-m-d H:i:s') . ')
*/
';
$data_contributors_clear = true;
$sourceDirectory = './all-min';
$outputDirectory = './all';
$fileName = 'example';

compressAndSaveFiles($sourceDirectory, $outputDirectory, $fileName, $explanation, $data_contributors_clear);