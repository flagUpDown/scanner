<?php
namespace FlagUpDown;

class ScanFile
{
    public static function findFilesPHP(string $rootPath, array $ignoreDirs = []) : array
    {
        array_walk($ignoreDirs, function (&$value) {
            $value = realpath($value);
        });

        return self::findFiles($rootPath, function ($file) use ($ignoreDirs) {
            if (is_dir($file) && in_array($file, $ignoreDirs)) {
                return false;
            }
            if (is_file($file) && pathinfo($file, PATHINFO_EXTENSION) !== 'php') {
                return false;
            }
            return true;
        });
    }

    public static function tokensEqual($a, $b)
    {
        if (is_string($a) && is_string($b)) {
            return $a === $b;
        } elseif (isset($a[0], $a[1], $b[0], $b[1])) {
            return $a[0] === $b[0] && $a[1] == $b[1];
        }
        return false;
    }

    private static function findFiles(string $rootPath, callable $filter) : array
    {
        $fileList = [];
        $rootPath = realpath($rootPath);
        if (!is_dir($rootPath)) {
            if (is_file($rootPath)) {
                return [$rootPath];
            }
            return [];
        }
        $handle = opendir($rootPath);
        if ($handle === false) {
            return [];
        }

        while (($filename = readdir($handle)) !== false) {
            if ($filename === '.' || $filename === '..') {
                continue;
            }
            $file = $rootPath . DIRECTORY_SEPARATOR . $filename;
            if ($filter($file)) {
                if (is_file($file)) {
                    $fileList[] = $file;
                } else {
                    $fileList = array_merge($fileList, self::findFiles($file, $filter));
                }
            }
        }
        closedir($handle);

        return $fileList;
    }
}
