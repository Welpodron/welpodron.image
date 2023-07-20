<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$arComponentParameters = [
    'GROUPS' => [
        'LAZY' => [
            'NAME' => 'Ленивая загрузка',
        ],
        'RESIZE' => [
            'NAME' => 'Ресайз изображения',
        ],
        'CONVERT' => [
            'NAME' => 'Конвертация изображения',
        ]
    ],
    'PARAMETERS' => [
        'FILE' => [
            "PARENT" => "BASE",
            "NAME" => 'Выберите файл:',
            "TYPE" => "FILE",
            "FD_TARGET" => "F",
            "FD_EXT" => 'jpg,jpeg,png',
            "FD_UPLOAD" => false,
            "FD_USE_MEDIALIB" => true,
            "FD_MEDIALIB_TYPES" => ['image'],
            'REFRESH' => 'Y',
        ],
        'CACHE_TIME' => ['DEFAULT' => 36000],
        'CACHE_GROUPS' => [
            'PARENT' => 'CACHE_SETTINGS',
            'NAME' => 'Учитывать права доступа',
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'Y'
        ]
    ]
];

if ($arCurrentValues['FILE']) {
    $arComponentParameters['PARAMETERS']['IMG_ID'] = [
        'PARENT' => 'BASE',
        'NAME' => 'ID изображения',
        'TYPE' => 'STRING',
        'DEFAULT' => ''
    ];

    $arComponentParameters['PARAMETERS']['IMG_CLASS'] = [
        'PARENT' => 'BASE',
        'NAME' => 'CSS Класс изображения',
        'TYPE' => 'STRING',
        'DEFAULT' => ''
    ];

    $arComponentParameters['PARAMETERS']['IMG_ALT'] = [
        'PARENT' => 'BASE',
        'NAME' => 'Альтернативный текст',
        'TYPE' => 'STRING',
        'DEFAULT' => ''
    ];
    $arComponentParameters['PARAMETERS']['RESIZE_USE'] = [
        'PARENT' => 'RESIZE',
        'NAME' => 'Ресайз изображения',
        'TYPE' => 'CHECKBOX',
        'REFRESH' => 'Y',
        'DEFAULT' => 'N'
    ];

    $arComponentParameters['PARAMETERS']['LAZY_USE'] = [
        'PARENT' => 'LAZY',
        'NAME' => 'Использовать ленивую загрузку',
        'TYPE' => 'CHECKBOX',
        'DEFAULT' => 'Y'
    ];

    $arComponentParameters['PARAMETERS']['WEBP_USE'] = [
        'PARENT' => 'CONVERT',
        'NAME' => 'Использовать webp',
        'TYPE' => 'CHECKBOX',
        'REFRESH' => 'Y',
        'DEFAULT' => 'Y'
    ];

    $arComponentParameters['PARAMETERS']['AVIF_USE'] = [
        'PARENT' => 'CONVERT',
        'NAME' => 'Использовать avif',
        'TYPE' => 'CHECKBOX',
        'REFRESH' => 'Y',
        'DEFAULT' => 'Y'
    ];

    if ($arCurrentValues['RESIZE_USE'] == 'Y') {
        $arComponentParameters['PARAMETERS']['RESIZE_TYPE'] = [
            'PARENT' => 'RESIZE',
            'NAME' => 'Тип ресайза (внимание данная функция доступна только для файлов из медиабиблиотеки и для инфоблоков)',
            'TYPE' => 'LIST',
            'VALUES' => [
                'BX_RESIZE_IMAGE_EXACT' => 'BX_RESIZE_IMAGE_EXACT',
                'BX_RESIZE_IMAGE_PROPORTIONAL' => 'BX_RESIZE_IMAGE_PROPORTIONAL',
                'BX_RESIZE_IMAGE_PROPORTIONAL_ALT' => 'BX_RESIZE_IMAGE_PROPORTIONAL_ALT',
            ]
        ];
        $arComponentParameters['PARAMETERS']['RESIZE_WIDTH'] = [
            'PARENT' => 'RESIZE',
            'NAME' => 'Ширина нового изображения',
            'TYPE' => 'STRING',
            'DEFAULT' => ''
        ];
        $arComponentParameters['PARAMETERS']['RESIZE_HEIGHT'] = [
            'PARENT' => 'RESIZE',
            'NAME' => 'Высота нового изображения',
            'TYPE' => 'STRING',
            'DEFAULT' => ''
        ];
    }

    if ($arCurrentValues['WEBP_USE'] == 'Y') {
        $arComponentParameters['PARAMETERS']['WEBP_QUALITY'] = [
            'PARENT' => 'CONVERT',
            'NAME' => 'Качество webp',
            'TYPE' => 'HIDDEN',
            'DEFAULT' => '85'
        ];
    }

    if ($arCurrentValues['AVIF_USE'] == 'Y') {
        $arComponentParameters['PARAMETERS']['AVIF_QUALITY'] = [
            'PARENT' => 'CONVERT',
            'NAME' => 'Качество avif',
            'TYPE' => 'STRING',
            'DEFAULT' => '55'
        ];
    }
}
