<?php

declare(strict_types=1);

namespace App\Utils;

use function array_filter;
use function array_keys;
use function array_values;
use function basename;
use function class_exists;
use function dirname;
use function glob;
use function in_array;
use function is_dir;
use function is_null;
use function ltrim;
use function rtrim;
use function str_contains;
use function str_replace;
use function str_starts_with;
use function strtoupper;
use function substr;
use function trim;

final class ClassHelper
{
    private static mixed $composer = null;
    private static array $classes = [];

    public function __construct()
    {
        self::$composer = null;
        self::$classes = [];
        self::$composer = require __DIR__ . '/../../vendor/autoload.php';

        if (! is_null(self::$composer)) {
            self::$classes = array_keys(self::$composer->getClassMap());
        }
    }

    public function getClassesByNamespace($namespace): array
    {
        if (! str_starts_with($namespace, '\\')) {
            $namespace = '\\' . $namespace;
        }

        $termUpper = strtoupper($namespace);

        $matched = array_filter($this->getClasses(), static function ($class) use ($termUpper) {
            $className = strtoupper($class);
            if (str_starts_with($className, $termUpper) &&
                ! str_contains($className, strtoupper('Abstract')) &&
                ! str_contains($className, strtoupper('Interface'))
            ) {
                return $class;
            }
            return false;
        });

        // 兜底扫描：若 Composer classmap 未更新，尝试从 PSR-4 目录动态扫描匹配类
        if (str_starts_with($termUpper, '\\APP\\')) {
            $subPath = str_replace('\\', '/', substr($namespace, strlen('\\App\\')));
            $dir = dirname(__DIR__) . '/' . trim($subPath, '/');
            if (is_dir($dir)) {
                $files = glob($dir . '/*.php');
                if ($files !== false) {
                    foreach ($files as $file) {
                        $baseName = basename($file, '.php');
                        $fullClass = '\\' . rtrim(ltrim($namespace, '\\'), '\\') . '\\' . $baseName;
                        if (! in_array($fullClass, $matched, true) &&
                            ! str_contains(strtoupper($baseName), 'ABSTRACT') &&
                            ! str_contains(strtoupper($baseName), 'INTERFACE') &&
                            class_exists($fullClass)
                        ) {
                            $matched[] = $fullClass;
                        }
                    }
                }
            }
        }

        return array_values($matched);
    }

    public function getClasses(): array
    {
        $allClasses = [];

        if (! is_null(self::$classes)) {
            foreach (self::$classes as $class) {
                $allClasses[] = '\\' . $class;
            }
        }

        return $allClasses;
    }
}

