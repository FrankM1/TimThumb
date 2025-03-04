<?php
/**
 * TimThumb Security Test Script
 * 
 * This script tests the security enhancements made to the TimThumb script.
 * It performs various attack vectors and validates that they are properly mitigated.
 * 
 * Usage: Run this script in a web browser to test the security of your TimThumb installation.
 */

// Test configuration
$timthumb_path = './timthumb.php';  // Path to the timthumb.php file
$test_image = 'http://example.com/image.jpg';  // A valid external image for testing
$local_image = './test_image.jpg';  // A local test image

// Create a local test image if it doesn't exist
if (!file_exists($local_image)) {
    $im = imagecreatetruecolor(100, 100);
    $text_color = imagecolorallocate($im, 233, 14, 91);
    imagestring($im, 1, 5, 5, 'Test Image', $text_color);
    imagejpeg($im, $local_image);
    imagedestroy($im);
}

// Initialize test results
$results = array();

// Test helper functions
function runTest($name, $description, $url, $expectSuccess = false) {
    global $results;
    
    echo "<h3>Test: $name</h3>";
    echo "<p>$description</p>";
    echo "<p>Testing URL: <code>" . htmlspecialchars($url) . "</code></p>";
    
    $start_time = microtime(true);
    $response = @file_get_contents($url);
    $end_time = microtime(true);
    
    $success = false;
    if ($expectSuccess) {
        $success = ($response !== false);
    } else {
        $success = ($response === false);
    }
    
    echo "<p>Result: " . ($success ? "<span style='color:green'>PASS</span>" : "<span style='color:red'>FAIL</span>") . "</p>";
    echo "<p>Time: " . round(($end_time - $start_time) * 1000, 2) . " ms</p>";
    
    $results[$name] = $success;
    
    return $success;
}

function verifyTimthumbInstallation() {
    global $timthumb_path;
    
    if (!file_exists($timthumb_path)) {
        die("TimThumb script not found at: $timthumb_path");
    }
    
    echo "<h2>Testing TimThumb version " . getTimthumbVersion($timthumb_path) . "</h2>";
}

function getTimthumbVersion($path) {
    $contents = file_get_contents($path);
    if (preg_match("/define\s*\(\s*'VERSION'\s*,\s*'([^']+)'/", $contents, $matches)) {
        return $matches[1];
    }
    return "Unknown";
}

// Start the test suite
echo "<!DOCTYPE html>
<html>
<head>
    <title>TimThumb Security Test Suite</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.5; }
        h1, h2 { color: #333; }
        h3 { margin-top: 30px; border-top: 1px solid #eee; padding-top: 10px; }
        code { background: #f6f8fa; padding: 2px 5px; border-radius: 3px; }
        .summary { margin: 20px 0; padding: 10px; background: #f9f9f9; border-left: 4px solid #ddd; }
        .pass { color: green; }
        .fail { color: red; }
    </style>
</head>
<body>
    <h1>TimThumb Security Test Suite</h1>";

// Verify TimThumb is available
verifyTimthumbInstallation();

// 1. Basic functionality test
$validUrl = "$timthumb_path?src=$test_image&w=100&h=100";
runTest(
    "Basic Functionality", 
    "Tests basic image resizing functionality with a valid URL",
    $validUrl,
    true
);

// 2. Local file access
$localFileUrl = "$timthumb_path?src=$local_image&w=100&h=100";
runTest(
    "Local File Access", 
    "Tests accessing a local file",
    $localFileUrl,
    true
);

// Security Tests

// 3. Directory Traversal Attack
$traversalAttack = "$timthumb_path?src=../../../etc/passwd&w=100&h=100";
runTest(
    "Directory Traversal", 
    "Tests protection against directory traversal attacks",
    $traversalAttack,
    false
);

// 4. Null Byte Attack
$nullByteAttack = "$timthumb_path?src=" . urlencode($test_image . "\0" . "malicious") . "&w=100&h=100";
runTest(
    "Null Byte Injection", 
    "Tests protection against null byte injection attacks",
    $nullByteAttack,
    false
);

// 5. WebShot Command Injection
$commandInjection = "$timthumb_path?src=http://example.com&webshot=1&w=100&h=100";
runTest(
    "WebShot Basic Functionality", 
    "Tests WebShot feature with a valid URL",
    $commandInjection,
    defined('WEBSHOT_ENABLED') && WEBSHOT_ENABLED
);

// 6. WebShot Command Injection with dangerous characters
$dangerousChars = array('$', '`', '\\', '|', '>', '<', ';', '&');
foreach ($dangerousChars as $char) {
    $maliciousUrl = "$timthumb_path?src=http://example.com" . urlencode($char) . "malicious&webshot=1&w=100&h=100";
    runTest(
        "WebShot Command Injection ($char)", 
        "Tests protection against command injection in WebShot feature",
        $maliciousUrl,
        false
    );
}

// 7. Invalid URL scheme
$invalidScheme = "$timthumb_path?src=ftp://example.com/image.jpg&w=100&h=100";
runTest(
    "Invalid URL Scheme", 
    "Tests protection against non-HTTP URL schemes",
    $invalidScheme,
    false
);

// 8. File size limit
$oversizeUrl = "$timthumb_path?src=$test_image&w=10000&h=10000";
runTest(
    "Maximum Size Limit", 
    "Tests enforcement of maximum width/height limits",
    $oversizeUrl,
    false
);

// 9. External image without proper permissions
if (!defined('ALLOW_EXTERNAL') || !ALLOW_EXTERNAL) {
    $externalUrl = "$timthumb_path?src=http://example.com/nonallowed.jpg&w=100&h=100";
    runTest(
        "External Image Blocking", 
        "Tests blocking of external images when ALLOW_EXTERNAL is disabled",
        $externalUrl,
        false
    );
}

// 10. Test error handling with malformed URL
$malformedUrl = "$timthumb_path?src=not_a_valid_url&w=100&h=100";
runTest(
    "Malformed URL", 
    "Tests error handling with malformed URL",
    $malformedUrl,
    false
);

// 11. Test with invalid image type
$invalidType = "$timthumb_path?src=" . urlencode("data:text/html,<script>alert('XSS')</script>") . "&w=100&h=100";
runTest(
    "Invalid Image Type", 
    "Tests protection against non-image files",
    $invalidType,
    false
);

// 12. Memory limit test
$memoryTest = "$timthumb_path?src=$test_image&w=1000&h=1000";
runTest(
    "Memory Limit", 
    "Tests memory limit handling with larger image",
    $memoryTest,
    true
);

// 13. WebShot URL Validation
$webShotInvalidUrl = "$timthumb_path?src=httpmalformed://example.com&webshot=1&w=100&h=100";
runTest(
    "WebShot URL Validation", 
    "Tests validation of URLs in WebShot feature",
    $webShotInvalidUrl,
    false
);

// 14. Test cache cleaning
$cacheTest = "$timthumb_path?src=$test_image&w=50&h=50&debug=true";
runTest(
    "Cache Functionality", 
    "Tests the image caching functionality",
    $cacheTest,
    true
);

// Print summary
echo "<div class='summary'>";
echo "<h2>Test Summary</h2>";
echo "<p>Total Tests: " . count($results) . "</p>";
echo "<p class='pass'>Passed: " . count(array_filter($results)) . "</p>";
echo "<p class='fail'>Failed: " . (count($results) - count(array_filter($results))) . "</p>";

if (count($results) - count(array_filter($results)) > 0) {
    echo "<h3>Failed Tests:</h3><ul>";
    foreach ($results as $name => $success) {
        if (!$success) {
            echo "<li>$name</li>";
        }
    }
    echo "</ul>";
}
echo "</div>";

echo "</body></html>";
