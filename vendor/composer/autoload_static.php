<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit9ca4c912090119ac4bbe302eef5ab226
{
    public static $prefixLengthsPsr4 = array (
        'D' => 
        array (
            'Danarwidi\\AdjustedCosineCollaborativeFilter\\' => 44,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Danarwidi\\AdjustedCosineCollaborativeFilter\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit9ca4c912090119ac4bbe302eef5ab226::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit9ca4c912090119ac4bbe302eef5ab226::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit9ca4c912090119ac4bbe302eef5ab226::$classMap;

        }, null, ClassLoader::class);
    }
}
