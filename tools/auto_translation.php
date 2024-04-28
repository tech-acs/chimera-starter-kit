<?php

/*
Automatically translate strings in a source language file to a destination language file using Azure Translate API
  Usage: php auto_translation.php <api_key> <region> <source_language> <destination_language>
  Example: php auto_translation.php 12313123123 eastus en pt-pt

Reads strings from the source language file (e.g. en.json) and translates them to the destination language and writes
out a translations file for the destination language (e.g. pt-pt.json).

To use Azure Translate API, you need to sign up for an Azure account and get an API key. 
You can get a free trial account with a limited number of translations per month.
Here are the steps:

1. Go to the Azure portal (https://portal.azure.com/) and sign in or create a new account if you don't have one.
2. Once you're signed in, go to the Azure Marketplace and search for "Translator Text".
3. Select "Translator Text" from the search results and click on "Create".
4. Fill in the required details like the name, subscription, resource group, pricing tier, etc., and click on "Review + create".
5. After reviewing your details, click on "Create" to create the resource.
6. Once the resource is created, go to the resource page and click on "Keys and Endpoint" in the left sidebar.
7. Here, you'll find your API key which you can use to authenticate your requests to the Azure Translate API.
*/

function translateString($string, $sourceLanguage, $destinationLanguage, $subscriptionKey, $region)
{
    $endpoint = 'https://api.cognitive.microsofttranslator.com/';

    // The string may contain placeholders like :name, :email, etc which should not be translated
    // To avoid that we wrap those placeholders in <span> tags so that Azure Translate API ignores them
    $string = preg_replace('/(:\w+)/', '<mstrans:dictionary translation=$1>$1</mstrans:dictionary>', $string);

    // Prepare Azure Translate API request
    $options = [
        'http' => [
            'header' => "Content-Type: application/json\r\n" .
                        "Ocp-Apim-Subscription-Key: $subscriptionKey\r\n".
                        "Ocp-Apim-Subscription-Region: $region\r\n",
            'method' => 'POST',
            'content' => json_encode([[
                    'text' => $string
        ]]),
        ],
    ];

    // Call Azure Translate API to get translation
    $context = stream_context_create($options);
    $response = @file_get_contents($endpoint . "/translate?api-version=3.0&from=$sourceLanguage&to=$destinationLanguage", false, $context);

    // Handle error
    if ($response === false) {
        $error = error_get_last();
        echo "Error calling Azure Translate API: " . $error['message'] . "\n";    
        exit(1);
    }

    // Process response which will be something like "[{"translations":[{"text":"Réel","to":"fr"}]}]"
    $result = json_decode($response, true);
    return $result[0]['translations'][0]['text'];
}

function translateArray($sourceArray, $sourceLanguage, $destinationLanguage, $subscriptionKey, $region) {
    $translatedArray = [];

    foreach ($sourceArray as $key => $value) {
        if (is_array($value)) {
            $translatedArray[$key] = translateArray($value, $sourceLanguage, $destinationLanguage, $subscriptionKey, $region);
        } else {
            $translatedString = translateString($value, $sourceLanguage, $destinationLanguage, $subscriptionKey, $region);
            $translatedArray[$key] = $translatedString;
        }
    }

    return $translatedArray;
}

if ($argc < 4) {
    echo "Usage: php auto_translation.php <api_key> <region> <source_language> <destination_language>\n\tExample: php auto_translation.php 12313123123 eastus en es\n";
    exit(1);
}

$subscriptionKey = $argv[1];
$region = $argv[2];
$sourceLanguage = $argv[3];
$destinationLanguage = $argv[4];
/*
// Load source language file
$sourceFile = __DIR__ . "/../resources/lang/{$sourceLanguage}.json";
if (!file_exists($sourceFile)) {
    echo "Source language file not found: {$sourceFile}\n";
    exit(1);
}

// Read source language file
$sourceStrings = json_decode(file_get_contents($sourceFile), true);

$destinationStrings = [];

// Translate each string in the source language file
foreach ($sourceStrings as $key => $value) {
    $translatedString = translateString($value, $sourceLanguage, $destinationLanguage, $subscriptionKey, $region);
    $destinationStrings[$key] = $translatedString;
}

$destFile = __DIR__ . "/../resources/lang/{$destinationLanguage}.json";

// Write translated strings to destination language file
file_put_contents($destFile, json_encode($destinationStrings, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

echo "Translated strings written to: {$destFile}\n";
*/

// Glob to get all the php files in the resources/lang directory
$files = glob(__DIR__ . "/../resources/lang/{$sourceLanguage}/*.php");

// Copy the source language php files to the destination language php files

/* The source file is something like this:
<?php

return [
    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',

];
*/

// Make the directory resources/lang/$destinationLanguage if it doesn't already exist
$destinationDirectory = __DIR__ . "/../resources/lang/{$destinationLanguage}";
if (!is_dir($destinationDirectory)) {
    mkdir($destinationDirectory, 0777, true);
}

// Parse of each of the language files from the glob
foreach ($files as $file) {
    $sourcePhpStrings = include $file;

    $destinationPhpStrings = translateArray($sourcePhpStrings, $sourceLanguage, $destinationLanguage, $subscriptionKey, $region);

    $destFile = str_replace($sourceLanguage, $destinationLanguage, $file);

    // Write translated strings to destination language file
    file_put_contents($destFile, "<?php\n\nreturn " . var_export($destinationPhpStrings, true) . ";\n");

    echo "Translated strings written to: {$destFile}\n";
}
