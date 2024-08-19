<?php
// * create version nummber
function averageVersion($versions)
{
    // * Count the total number of versions provided
    $totalVersions = count($versions);
    // * Initialize sums for major, minor, and patch versions
    $majorSum = 0;
    $minorSum = 0;
    $patchSum = 0;
    // *Loop through each version string
    foreach ($versions as $version) {
        // * Split the version string into major, minor, and patch components
        list($major, $minor, $patch) = explode('.', $version);

        // * Convert each component to an integer and add to the respective sum
        $majorSum += (int) $major;
        $minorSum += (int) $minor;
        $patchSum += (int) $patch;
    }
    // * Calculate the average for each version component, rounding to the nearest integer
    $majorAvg = round($majorSum / $totalVersions);
    $minorAvg = round($minorSum / $totalVersions);
    $patchAvg = round($patchSum / $totalVersions);
    // * Combine the averages into a version string and return it
    return $majorAvg . '.' . $minorAvg . '.' . $patchAvg;
}
// * version list json output file
function getListVersionsJson($fileTitle, $data, $fileNameText)
{
    $jsonOutput = [];
    // * version list
    foreach ($data as $items) {
        $fileKey = $items["file"] . "." . $items["extension"];
        $jsonOutput[$fileKey][] = [
            $items["version"],
            "https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/" . $items["file"] . "_" . $items["version"] . "." . $items["extension"],
            "https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/" . $items["file"] . "-min_" . $items["version"] . "." . $items["extension"]
        ];
    }
    // * latest all versions
    $versions = [];
    foreach ($data as $items) {
        if (strpos($items["path"], '/olds/') == false)
            array_push($versions, $items["version"]);
    }
    // * create new versions
    $newVersion = averageVersion($versions);
    // * host path url
    $hostUrl = "https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/";
    // * all in css
    $jsonOutput[$fileTitle . ".css"][] = [
        $newVersion,
        $hostUrl . $fileTitle . "-all_" . $newVersion . ".css",
        $hostUrl . $fileTitle . "-all-min_" . $newVersion . ".css"
    ];
    // * all in js
    $jsonOutput[$fileTitle . ".js"][] = [
        $newVersion,
        $hostUrl . $fileTitle . "_" . $newVersion . ".js",
        $hostUrl . $fileTitle . "-all-min_" . $newVersion . ".js"
    ];
    // * JSON output
    $resultContent = json_encode($jsonOutput, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    file_put_contents($fileNameText, $resultContent);
}
// * version list
function getListVersions($title, $data, $fileName)
{
    $content = "# " . $title . " Versions\n\n";
    // * version list
    foreach ($data as $items) {
        $content .= "Name : " . $items["file"];
        $content .= "\n";
        $content .= "Type : " . $items["extension"];
        $content .= "\n";
        $content .= "Version : v" . $items["version"];
        $content .= "\n";
        $content .= "URL: https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/" . $items["file"] . "_" . $items["version"] . "." . $items["extension"];
        $content .= "\n";
        $content .= "URL: https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/" . $items["file"] . "-min_" . $items["version"] . "." . $items["extension"];
        $content .= "\n\n--------\n\n";
    }
    // * to UTF-8
    $utf8Content = mb_convert_encoding($content, 'UTF-8', 'auto');
    // * output file
    file_put_contents($fileName, $utf8Content);
}

// * get version in file
function getVersion($pathName)
{
    $handle = fopen($pathName, "r"); // * file read
    $version = "1.0.0"; // * default version
    // * is file
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            if (preg_match('/v(\d+\.\d+\.\d+)/', $line, $matches)) {
                $version = $matches[1]; // * find version
                break;
            }
        }
        fclose($handle);
    }
    return $version;
}
// * get file data list
function getList($sourceDir)
{
    // * get files
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourceDir));
    // * file list
    $results = [];
    // * file check and add list
    foreach ($files as $file) {
        if ($file->isFile() && in_array($file->getExtension(), ['css', 'js'])) {
            $pathName = $file->getPathname(); // * get path 
            $fileName = pathinfo($file->getFilename(), PATHINFO_FILENAME); // * file name remove file extension
            $fileExtension = $file->getExtension(); // * file extension
            $version = getVersion($pathName); // * get version
            // * data schema
            $result = array(
                "path" => $pathName,
                "file" => $fileName,
                "extension" => $fileExtension,
                "version" => $version,
            );
            // * add results
            array_push($results, $result);
        }
    }
    return (object) $results;
}
