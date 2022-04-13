<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInit9ca4c912090119ac4bbe302eef5ab226
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInit9ca4c912090119ac4bbe302eef5ab226', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInit9ca4c912090119ac4bbe302eef5ab226', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        \Composer\Autoload\ComposerStaticInit9ca4c912090119ac4bbe302eef5ab226::getInitializer($loader)();

        $loader->register(true);

        return $loader;
    }
}
