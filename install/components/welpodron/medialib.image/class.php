<?

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\IO\File;
use Bitrix\Main\Application;
use Bitrix\Main\FileTable;
use Bitrix\Main\IO\Path;
use Bitrix\Main\Loader;
use Welpodron\Image\Utils\Converter;

class WelpodronMedialibImage extends CBitrixComponent
{
    const SUPPORTED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/jpg'];
    const SUPPORTED_RESIZE_TYPES = [BX_RESIZE_IMAGE_EXACT, BX_RESIZE_IMAGE_PROPORTIONAL, BX_RESIZE_IMAGE_PROPORTIONAL_ALT];

    public function executeComponent()
    {
        // Подключаем модуль игнорируя при этом кэш
        Loader::includeModule('welpodron.image');

        $this->arResult = $this->getImage();
        $this->includeComponentTemplate();

        return $this->arResult;
    }

    public function onPrepareComponentParams($arParams)
    {
        $arParams['FILE'] = isset($arParams['FILE']) ? $arParams['FILE'] : null;

        if (!is_string($arParams['FILE'])) {
            // Быстрый выход, если не указан путь к файлу
            return $arParams;
        }

        $arParams['IMG_ID'] = isset($arParams['IMG_ID']) ? $arParams['IMG_ID'] : null;
        $arParams['IMG_CLASS'] = isset($arParams['IMG_CLASS']) ? $arParams['IMG_CLASS'] : null;
        $arParams['IMG_ALT'] = isset($arParams['IMG_ALT']) ? $arParams['IMG_ALT'] : null;

        $arParams['LAZY_USE'] = $arParams['LAZY_USE'] == "Y" ? true : false;

        $arParams['RESIZE_USE'] = $arParams['RESIZE_USE'] == "Y" ? true : false;

        if (isset($arParams['RESIZE_TYPE'])) {
            if ($arParams['RESIZE_TYPE'] == 'BX_RESIZE_IMAGE_EXACT') {
                $arParams['RESIZE_TYPE'] = BX_RESIZE_IMAGE_EXACT;
            } else if ($arParams['RESIZE_TYPE'] == 'BX_RESIZE_IMAGE_PROPORTIONAL') {
                $arParams['RESIZE_TYPE'] = BX_RESIZE_IMAGE_PROPORTIONAL;
            } else if ($arParams['RESIZE_TYPE'] == 'BX_RESIZE_IMAGE_PROPORTIONAL_ALT') {
                $arParams['RESIZE_TYPE'] = BX_RESIZE_IMAGE_PROPORTIONAL_ALT;
            } else {
                $arParams['RESIZE_TYPE'] = null;
            }
        } else {
            $arParams['RESIZE_TYPE'] = null;
        }

        $arParams['RESIZE_WIDTH'] = isset($arParams['RESIZE_WIDTH']) ? intval($arParams['RESIZE_WIDTH']) : null;
        $arParams['RESIZE_HEIGHT'] = isset($arParams['RESIZE_HEIGHT']) ? intval($arParams['RESIZE_HEIGHT']) : null;

        $arParams['WEBP_USE'] = $arParams['WEBP_USE'] == "Y" ? true : false;
        $arParams['WEBP_QUALITY'] = isset($arParams['WEBP_QUALITY']) ? intval($arParams['WEBP_QUALITY']) : 85;

        $arParams['AVIF_USE'] = $arParams['AVIF_USE'] == "Y" ? true : false;
        $arParams['AVIF_QUALITY'] = isset($arParams['AVIF_QUALITY']) ? intval($arParams['AVIF_QUALITY']) : 55;

        return $arParams;
    }

    public function getImage()
    {
        try {
            if (!Loader::includeModule('welpodron.image')) {
                return;
            }

            if (strpos($this->arParams['FILE'], 'iblock') !== false || strpos($this->arParams['FILE'], 'medialibrary') !== false) {
                // Вероятнее всего файл был загружен через инфоблок или медиабиблиотеку
                $file = FileTable::getList([
                    'filter' => [
                        'FILE_NAME' => Path::getName($this->arParams['FILE']),
                        'CONTENT_TYPE' => self::SUPPORTED_IMAGE_TYPES,
                    ],
                    'select' => [
                        'ID',
                        'WIDTH',
                        'HEIGHT',
                        'SUBDIR',
                        'FILE_NAME',
                    ],
                    'limit' => 1
                ])->fetch();

                if ($file) {
                    if ($this->arParams['RESIZE_USE'] && in_array($this->arParams['RESIZE_TYPE'], self::SUPPORTED_RESIZE_TYPES)) {
                        if ($this->arParams['RESIZE_WIDTH'] && $this->arParams['RESIZE_HEIGHT']) {
                            //! TODO: Switch to new API  
                            $resizedFile = \CFile::ResizeImageGet(
                                $file['ID'],
                                [
                                    'width' => $this->arParams['RESIZE_WIDTH'],
                                    'height' => $this->arParams['RESIZE_HEIGHT'],
                                ],
                                $this->arParams['RESIZE_TYPE'],
                                true
                            );

                            return [
                                'FILE_ID' => $file['ID'],
                                'FILE_PATH' => $resizedFile['src'],
                                'IMG_WIDTH' => $resizedFile['width'],
                                'IMG_HEIGHT' => $resizedFile['height'],
                                'IMG_ALT' => $this->arParams['IMG_ALT'],
                                'IMG_ID' => $this->arParams['IMG_ID'],
                                'IMG_CLASS' => $this->arParams['IMG_CLASS'],
                            ];
                        }
                    }

                    return [
                        'FILE_ID' => $file['ID'],
                        'FILE_PATH' => $this->arParams['FILE'],
                        'IMG_WIDTH' => $file['WIDTH'],
                        'IMG_HEIGHT' => $file['HEIGHT'],
                        'IMG_ALT' => $this->arParams['IMG_ALT'],
                        'IMG_ID' => $this->arParams['IMG_ID'],
                        'IMG_CLASS' => $this->arParams['IMG_CLASS'],
                    ];
                }
            }
            // Даже если не нашли в FileTable то попробуем найти локально

            $subdirectory = str_replace(Application::getDocumentRoot(), '', $this->arParams['FILE']);
            $absolutePath = Application::getDocumentRoot() . $subdirectory;

            $file = new File($absolutePath);

            if (!$file->isExists()) {
                return;
            }

            $contentType = $file->getContentType();

            if (!in_array($contentType, self::SUPPORTED_IMAGE_TYPES)) {
                return;
            }

            $imgDimensions = getimagesize($absolutePath);

            return [
                'FILE_PATH' => $this->arParams['FILE'],
                'IMG_WIDTH' => $imgDimensions[0],
                'IMG_HEIGHT' => $imgDimensions[1],
                'IMG_ALT' => $this->arParams['IMG_ALT'],
                'IMG_ID' => $this->arParams['IMG_ID'],
                'IMG_CLASS' => $this->arParams['IMG_CLASS'],
            ];
        } catch (\Throwable $th) {
        }
    }
}
