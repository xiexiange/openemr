<?php
/**
 * Script to copy entire interface directory to interface/mobile
 * This creates a complete mirror of the interface directory for mobile use
 */

$sourceDir = __DIR__ . '/interface';
$targetDir = __DIR__ . '/interface/mobile';

// Create target directory if it doesn't exist
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

function copyDirectory($src, $dst) {
    $dir = opendir($src);
    if (!is_dir($dst)) {
        mkdir($dst, 0755, true);
    }
    while (($file = readdir($dir)) !== false) {
        if ($file != '.' && $file != '..') {
            $srcFile = $src . '/' . $file;
            $dstFile = $dst . '/' . $file;
            if (is_dir($srcFile)) {
                copyDirectory($srcFile, $dstFile);
            } else {
                copy($srcFile, $dstFile);
            }
        }
    }
    closedir($dir);
}

echo "Starting copy process...\n";
echo "Source: $sourceDir\n";
echo "Target: $targetDir\n\n";

// Exclude mobile directory itself to avoid recursion
$excludeDirs = ['mobile'];

function shouldExclude($path, $excludeDirs) {
    foreach ($excludeDirs as $exclude) {
        if (strpos($path, '/' . $exclude . '/') !== false || strpos($path, '\\' . $exclude . '\\') !== false) {
            return true;
        }
    }
    return false;
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$count = 0;
foreach ($iterator as $item) {
    $sourcePath = $item->getPathname();
    $relativePath = str_replace($sourceDir . DIRECTORY_SEPARATOR, '', $sourcePath);
    
    // Skip mobile directory
    if (shouldExclude($relativePath, $excludeDirs)) {
        continue;
    }
    
    $targetPath = $targetDir . DIRECTORY_SEPARATOR . $relativePath;
    
    if ($item->isDir()) {
        if (!is_dir($targetPath)) {
            mkdir($targetPath, 0755, true);
        }
    } else {
        // Copy file
        $targetDirPath = dirname($targetPath);
        if (!is_dir($targetDirPath)) {
            mkdir($targetDirPath, 0755, true);
        }
        copy($sourcePath, $targetPath);
        $count++;
        if ($count % 100 == 0) {
            echo "Copied $count files...\n";
        }
    }
}

echo "\nCopy completed! Total files copied: $count\n";
echo "Mobile interface is now available at: $targetDir\n";
