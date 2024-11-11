<?php
// * folder create func
// function createFolder($folderName)
// {
//     if (!file_exists($folderName))
//         mkdir($folderName, 0777, true);
// }
// * file create func
function createFile($fileName, $fileContent)
{
    $openFile = fopen("./" . $fileName, "w");
    fwrite($openFile, $fileContent);
    fclose($openFile);
}
// * css compress func
function compressCSS($content, $data_contributors_clear)
{
    // * css min
    // * Remove CSS comments
    if ($data_contributors_clear) {
        // * Remove CSS comments
        $content = preg_replace('/\/\*.*?\*\//s', '', $content);
    } else {
        // * Remove CSS comments except the ones starting with "/* *"
        // $content = preg_replace('/\/\*(?!\s*\*)(.|\n)*?\*\//s', '', $content);
        // * Remove CSS comments except the ones starting with "/***"
        $content = preg_replace('/\/\*(?!\s*\*\*)(.|\n)*?\*\//s', '', $content);
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
    // $content = preg_replace('/([^\/])\*\s*\*\s*/', "$1\n * * ", $content); // **
    $content = preg_replace('/([^\/])\*\s\*\s(?!\*)/', "$1\n * * ", $content); // ***
    // * content min trim
    $contentMin = trim($content) . PHP_EOL;

    return $contentMin;
}
// * js compress func
function compressJS($content, $data_contributors_clear)
{
    // * js min
    // * Remove JS comments
    if ($data_contributors_clear) {
        // * Remove JS comments
        $content = preg_replace('/\/\*(.|[\r\n])*?\*\/|\/\/.*/', '', $content);
    } else {
        // * Remove CSS comments except the ones starting with "/* *"
        // $content = preg_replace('/\/\*(?!\s*\*)(.|\n)*?\*\/|\/\/(?!.*?\/\* *).*$/m', '', $content);
        // * Remove CSS comments except the ones starting with "/***"
        // $content = preg_replace('/\/\*(?!\s*\*\*)(.|\n)*?\*\/|\/\/(?!.*?\/\* *).*$/m', '', $content);
        // $content = preg_replace('/\/\*(?!\s*\*\*)(.|\n)*?\*\//s', '', $content);
        $content = preg_replace('/\/\*(?!\s*\*\*)(.|\n)*?\*\/|^\s*\/\/.*$/m', '', $content);
    }
    // * Replace horizontal whitespaces with a single space
    $content = preg_replace('/\h+/', ' ', $content);
    // * Remove vertical whitespaces
    $content = preg_replace('/\v+/', '', $content);
    // * Remove extra spaces around specific characters
    // $content = preg_replace('/\s*([{}:;,()=<>+\-*\/])\s*/', '$1', $content);
    $content = preg_replace_callback(
        '#/\*\*\*.*?\*/|(\s*([{}:;,()=<>+\-*\/])\s*)#s',
        function ($matches) {
            return isset($matches[2]) ? $matches[2] : $matches[0];
        },
        $content
    );
    // * comment edit
    // * comment line skip
    $content = preg_replace('/([^\/])\/\*/', "$1\n/*", $content);
    // * line skip
    $content = preg_replace('/\*\//', "*/\n", $content);
    // * license line skip
    // $content = preg_replace('/([^\/])\*\s*\*\s*/', "$1\n * * ", $content); // **
    $content = preg_replace('/([^\/])\*\s\*\s(?!\*)/', "$1\n * * ", $content); // ***
    // * content min trim
    $contentMin = trim($content) . PHP_EOL;
    return $contentMin;
}
// * main start compress func
function compressAndSaveFiles($data, $fileName, $explanation, $data_contributors_clear)
{
    // * all normal content
    $cssContent = '';
    $jsContent = '';
    // * all min content
    $cssContentMin = '';
    $jsContentMin = '';
    // * latest all versions
    $versions = [];
    foreach ($data as $items) {
        if (strpos($items["path"], '/olds/') == false)
            array_push($versions, $items["version"]);
    }
    // * create new versions
    $newVersion = averageVersion($versions);
    // * content check
    foreach ($data as $items) {
        $path = $items["path"];
        $file = $items["file"];
        $type = $items["extension"];
        $version = $items["version"];
        // * file content data
        $content = file_get_contents($path);
        // * check old version
        $isOld = strpos($path, '/olds/') == false;
        // * check type
        if ($type == "css") {
            // * css raw
            $fileContent = trim($content) . PHP_EOL;
            // * css compress
            $contentMin = compressCSS($content, $data_contributors_clear);
            // * create single css
            if (!empty($fileContent)) {
                $contentData = $explanation . $fileContent;
                $contentFileName = $file . "_" . $version . ".css";
                createFile($contentFileName, $contentData);
            }
            // * create single min css
            if (!empty($contentMin)) {
                $contentData = $explanation . $contentMin;
                $contentFileName = $file . "-min_" . $version . ".css";
                createFile($contentFileName, $contentData);
            }
            // * add all css
            if ($isOld) {
                $cssContent .= $fileContent;
                $cssContentMin .= $contentMin;
            }
        } else {
            // * js raw
            $fileContent = trim($content) . PHP_EOL;
            // * js compress
            $contentMin = compressJS($content, $data_contributors_clear);
            // * create single js
            if (!empty($fileContent)) {
                $contentData = $explanation . $fileContent;
                $contentFileName = $file . "_" . $version . ".js";
                createFile($contentFileName, $contentData);
            }
            // * create single min js
            if (!empty($contentMin)) {
                $contentData = $explanation . $contentMin;
                $contentFileName = $file . "-min_" . $version . ".js";
                createFile($contentFileName, $contentData);
            }
            // * add all js
            if ($isOld) {
                $jsContent .= $fileContent;
                $jsContentMin .= $contentMin;
            }
        }
    }
    // * all in versions
    // * css raw write
    if (!empty($cssContent)) {
        $contentData = $explanation . $cssContent;
        $contentFileName = $fileName . "-all_" . $newVersion . ".css";
        createFile($contentFileName, $contentData);
    }
    // * js raw write
    if (!empty($jsContent)) {
        $contentData = $explanation . $jsContent;
        $contentFileName = $fileName . "-all_" . $newVersion . ".js";
        createFile($contentFileName, $contentData);
    }
    // * css min write
    if (!empty($cssContentMin)) {
        $contentData = $explanation . $cssContentMin;
        $contentFileName = $fileName . "-all-min_" . $newVersion . ".css";
        createFile($contentFileName, $contentData);
    }
    // * js min write
    if (!empty($jsContentMin)) {
        $contentData = $explanation . $jsContentMin;
        $contentFileName = $fileName . "-all-min_" . $newVersion . ".js";
        createFile($contentFileName, $contentData);
    }
}
