<?

use Bitrix\Main\Loader;

Loader::includeModule("welpodron.core");

CJSCore::RegisterExt('welpodron.image.lz', [
    'js' => '/local/packages/welpodron.image/iife/lz/index.js',
    'skip_core' => true
]);

Loader::registerAutoLoadClasses(
    'welpodron.image',
    [
        'Welpodron\Image\Utils\Converter' => 'lib/utils/converter.php',
    ]
);
