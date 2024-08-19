<?php
// * import dependence
include "./file-layout.php";
include "./file-minify.php";
// * settings
$fileName = 'example';
$data_contributors_clear = false;
$sourceDir = "./github/";
$explanation = '/*
  * * Update (' . date('Y-m-d H:i:s') . ')
*/
';
$validToken = "YourSecretKey";
function isAccess($sourceDir, $fileName, $explanation, $data_contributors_clear, $validToken)
{
  // * Token validate
  function validateToken($token, $validToken)
  {
    // * Tokken check
    return $token === $validToken;
  }
  // * All get headers
  $headers = getallheaders();
  // * Get Authorization header
  $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : null;
  // * Check auth
  if ($authHeader) {
    // * Just get the token
    $token = str_replace('Bearer ', '', $authHeader);
    // * Verify token
    if (validateToken($token, $validToken)) {
      // * Token is valid
      startProcess($sourceDir, $fileName, $explanation, $data_contributors_clear);
    } else {
      // * Invalid token. Access denied.
      http_response_code(401);
    }
  } else {
    // * Authorization header is missing.
    http_response_code(400);
  }
}
// * started func
function startProcess($sourceDir, $fileName, $explanation, $data_contributors_clear)
{
  // * get file list
  $data = getList($sourceDir);
  // * process list 
  compressAndSaveFiles($data, $fileName, $explanation, $data_contributors_clear);
  // * create version list md
  getListVersions($fileName, $data, "./versions.md");
  // * create version list json
  getListVersionsJson($fileName, $data, "./versions.json");
}
// * started
isAccess($sourceDir, $fileName, $explanation, $data_contributors_clear, $validToken);
// * data test func
// print_r($data);
// foreach ($data as $items) {
//     foreach ($items as $item) {
//         print_r($item);
//         echo "<br>";
//     }
//     echo "<br>";
// }