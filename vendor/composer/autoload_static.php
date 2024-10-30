<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitc3b7835ec029259e9d898f71ac804220
{
    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'Abraham\\TwitterOAuth\\' => 21,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Abraham\\TwitterOAuth\\' => 
        array (
            0 => __DIR__ . '/..' . '/abraham/twitteroauth/src',
        ),
    );

    public static $classMap = array (
        'TwitterAPIExchange' => __DIR__ . '/..' . '/j7mbo/twitter-api-php/TwitterAPIExchange.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitc3b7835ec029259e9d898f71ac804220::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitc3b7835ec029259e9d898f71ac804220::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitc3b7835ec029259e9d898f71ac804220::$classMap;

        }, null, ClassLoader::class);
    }
}
