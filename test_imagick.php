<?php
// Test ImageMagick
echo "<h2>ImageMagick Test</h2>";

if (extension_loaded('imagick')) {
    echo "<p style='color: green;'>✅ ImageMagick extension is loaded!</p>";
    
    $imagick = new Imagick();
    $version = $imagick->getVersion();
    echo "<p><strong>ImageMagick Version:</strong> " . $version['versionString'] . "</p>";
    
    echo "<h3>Supported Formats:</h3>";
    $formats = Imagick::queryFormats();
    echo "<p>Total formats supported: " . count($formats) . "</p>";
    echo "<details><summary>View all formats</summary><pre>";
    print_r(array_slice($formats, 0, 50)); // Mostrar solo los primeros 50
    echo "</pre></details>";
    
} else {
    echo "<p style='color: red;'>❌ ImageMagick extension is NOT loaded!</p>";
}

echo "<hr>";
echo "<h3>All Loaded Extensions:</h3>";
echo "<pre>";
print_r(get_loaded_extensions());
echo "</pre>";
?>
