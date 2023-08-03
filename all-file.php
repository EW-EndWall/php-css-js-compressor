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
function compressAndSaveFiles($sourceDir, $outputDir, $explanation)
{
    $cssContent = '';
    $jsContent = '';

    if (!file_exists($outputDir)) {
        mkdir($outputDir, 0777, true);
    }

    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourceDir));
    foreach ($files as $file) {
        if ($file->isFile() && in_array($file->getExtension(), ['css', 'js'])) {
            $content = file_get_contents($file->getPathname());

            // * Remove comments and unnecessary whitespaces from CSS
            if ($file->getExtension() === 'css') {
                // * Remove CSS comments
                $content = preg_replace('!/\*.*?\*/!s', '', $content);
                // * Replace multiple whitespaces with a single space
                $content = preg_replace('/\s+/', ' ', $content);
                // * Remove unnecessary whitespaces
                $content = str_replace([': ', ', ', '; ', ' {', '{ ', '} '], [':', ',', ';', '{', '{', '}'], $content);
                $cssContent .= trim($content) . PHP_EOL;
            }

            // * Remove comments and unnecessary whitespaces from JS
            if ($file->getExtension() === 'js') {
                // * Remove JS comments
                $content = preg_replace('/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/', '', $content);
                // * Replace horizontal whitespaces with a single space
                $content = preg_replace('/\h+/', ' ', $content);
                // * Remove vertical whitespaces
                $content = preg_replace('/\v+/', '', $content);
                $jsContent .= trim($content) . PHP_EOL;
            }
        }
    }

    // * css compress
    // $cssContent = compressCSS($cssContent);

    if (!empty($cssContent)) {
        $cssFile = fopen($outputDir . '/bClass-all-min.css', 'w');
        fwrite($cssFile, $explanation . $cssContent);
        fclose($cssFile);
    }

    if (!empty($jsContent)) {
        $jsFile = fopen($outputDir . '/bClass-all-min.js', 'w');
        fwrite($jsFile, $explanation . $jsContent);
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
$sourceDirectory = './all-min';
$outputDirectory = './all';

compressAndSaveFiles($sourceDirectory, $outputDirectory, $explanation);