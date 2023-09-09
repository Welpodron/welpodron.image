<?

use Bitrix\Main\Loader;

CJSCore::RegisterExt('welpodron.image', [
    'js' => '/bitrix/js/welpodron.image/script.js',
    'skip_core' => true
]);

Loader::registerAutoLoadClasses(
    'welpodron.image',
    [
        'Welpodron\Image\Utils\Converter' => 'lib/utils/converter.php',
    ]
);
