<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit898a78978526e8870be19e88a4212f6a
{
    public static $files = array (
        '74ed299072414d276bb7568fe71d5b0c' => __DIR__ . '/..' . '/tinify/tinify/lib/Tinify.php',
        '9635627915aaea7a98d6d14d04ca5b56' => __DIR__ . '/..' . '/tinify/tinify/lib/Tinify/Exception.php',
    );

    public static $prefixLengthsPsr4 = array (
        'T' => 
        array (
            'Tinify\\' => 7,
        ),
        'D' => 
        array (
            'Delight\\Http\\' => 13,
            'Delight\\Db\\' => 11,
            'Delight\\Cookie\\' => 15,
            'Delight\\Base64\\' => 15,
            'Delight\\Auth\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Tinify\\' => 
        array (
            0 => __DIR__ . '/..' . '/tinify/tinify/lib/Tinify',
        ),
        'Delight\\Http\\' => 
        array (
            0 => __DIR__ . '/..' . '/delight-im/http/src',
        ),
        'Delight\\Db\\' => 
        array (
            0 => __DIR__ . '/..' . '/delight-im/db/src',
        ),
        'Delight\\Cookie\\' => 
        array (
            0 => __DIR__ . '/..' . '/delight-im/cookie/src',
        ),
        'Delight\\Base64\\' => 
        array (
            0 => __DIR__ . '/..' . '/delight-im/base64/src',
        ),
        'Delight\\Auth\\' => 
        array (
            0 => __DIR__ . '/..' . '/delight-im/auth/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit898a78978526e8870be19e88a4212f6a::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit898a78978526e8870be19e88a4212f6a::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
