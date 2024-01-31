<?

namespace Welpodron\Image\Utils;

use Bitrix\Main\Application;
use Bitrix\Main\IO\File;
use Bitrix\Main\Config\Option;
use Bitrix\Main\File\Image;
use Bitrix\Main\File\Image\Rectangle;

class Converter
{
    private static $instance;

    private $uploadDirName = 'upload';

    const SUPPORTED_ORIGINAL_TYPES = ['image/jpeg', 'image/png', 'image/jpg'];
    const SUPPORTED_CONVERTED_TYPES = ['webp', 'avif'];

    private function __construct()
    {
        $this->uploadDirName = Option::get("main", "upload_dir", "upload");
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new Converter();
        }

        return self::$instance;
    }

    public function checkSupport($type)
    {
        if ($type === 'webp') {
            if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') === false && strpos($_SERVER['HTTP_USER_AGENT'], ' Chrome/') === false) {
                return false;
            }
        } elseif ($type === 'avif') {
            if (strpos($_SERVER['HTTP_ACCEPT'], 'image/avif') === false && strpos($_SERVER['HTTP_USER_AGENT'], ' Chrome/') === false) {
                return false;
            }
        } else {
            return false;
        }

        return true;
    }

    //! Original: bitrix/modules/main/classes/general/file.php ResizeImageFile 
    public function resize($originalPath, $arSize, $resizeType = 'DEFAULT', $saveDirName = 'resize_cache')
    {
        try {
            if (!is_string($originalPath)) {
                return;
            }

            if ($resizeType !== constant('BX_RESIZE_IMAGE_EXACT') && $resizeType !== constant('BX_RESIZE_IMAGE_PROPORTIONAL_ALT')) {
                $resizeType = constant('BX_RESIZE_IMAGE_PROPORTIONAL');
            }

            if (!is_array($arSize)) {
                return;
            }

            if (!array_key_exists("width", $arSize)) {
                return;
            }

            if (!array_key_exists("height", $arSize)) {
                return;
            }

            $convertedWidth = intval($arSize["width"]);
            $convertedHeight = intval($arSize["height"]);

            $originalRelativePath = str_replace(Application::getDocumentRoot(), '', $originalPath);
            $originalAbsolutePath = Application::getDocumentRoot() . $originalRelativePath;

            $originalFile = new File($originalAbsolutePath);

            if (!$originalFile->isExists()) {
                return;
            }

            $originalImage = new Image($originalAbsolutePath);
            $originalInfo = $originalImage->getInfo();

            if ($originalInfo === null || !$originalInfo->isSupported()) {
                //! TODO: Add support to AVIF 
                return;
            }

            $originalType = $originalInfo->getFormat();

            if ($originalType == Image::FORMAT_BMP || $originalType == 'image/webp' || $originalType == 'image/avif') {
                //! TODO: Implement BMP + Webp + Avif support 
                return;
            }

            if ($saveDirName === false) {
                // if saveDirName === false then resize in place
                $convertedRelativePath = $originalRelativePath;
                $convertedAbsolutePath = $originalAbsolutePath;
            } elseif ($saveDirName === true) {
                //! TODO: Implement resize in the same folder as original 
                // if saveDirName === true then resize in the same folder as original
                return;
            } else {
                $originalName = $originalFile->getName();

                $originalDir = str_replace($originalName, '', str_replace($this->uploadDirName . DIRECTORY_SEPARATOR, '', $originalRelativePath));

                $convertedRelativePath = DIRECTORY_SEPARATOR . $this->uploadDirName . DIRECTORY_SEPARATOR . $saveDirName . $originalDir . $originalName;
                $convertedAbsolutePath = Application::getDocumentRoot() . $convertedRelativePath;
            }

            $orientation = 0;

            if ($originalType == Image::FORMAT_JPEG) {
                $exifData = $originalImage->getExifData();
                if (isset($exifData['Orientation'])) {
                    $orientation = $exifData['Orientation'];
                    //swap width and height
                    if ($orientation >= 5 && $orientation <= 8) {
                        $originalInfo->swapSides();
                    }
                }
            }

            $result = false;

            $originalRectangle = $originalInfo->toRectangle();
            $convertedRectangle = new Rectangle($convertedWidth, $convertedHeight);

            //! Сохраняется в виде булевой потому что если картинка уже нужных размеров то не нужно ее ресайзить 
            $needResize = $originalRectangle->resize($convertedRectangle, $resizeType);

            $hLock = $originalFile->open("r+");
            $useLock = defined("BX_FILE_USE_FLOCK");

            $convertedFile = new File($convertedAbsolutePath);

            if ($hLock) {
                if ($useLock) {
                    flock($hLock, LOCK_EX);
                }
                if ($convertedFile->isExists()) {
                    $convertedInfo = (new Image($convertedAbsolutePath))->getInfo();
                    if ($convertedInfo) {
                        if ($convertedInfo->getWidth() == $convertedRectangle->getWidth() && $convertedRectangle->getHeight() == $convertedRectangle->getHeight()) {
                            //nothing to do
                            $result = true;
                        }
                    }
                }
            }

            if ($result === false) {
                $copyFlag = true;

                if ($originalAbsolutePath !== $convertedAbsolutePath) {
                    $copyFlag = CopyDirFiles($originalAbsolutePath, $convertedAbsolutePath);
                };

                if ($copyFlag) {
                    $convertedImage = new Image($convertedAbsolutePath);

                    if ($convertedImage->load()) {
                        if ($orientation > 1) {
                            $convertedImage->autoRotate($orientation);
                        }

                        $isConverted = false;

                        if ($needResize) {
                            // actual sizes
                            $originalRectangle = $convertedImage->getDimensions();
                            $convertedRectangle = new Rectangle($convertedWidth, $convertedHeight);

                            $originalRectangle->resize($convertedRectangle, $resizeType);

                            $isConverted = $convertedImage->resize($originalRectangle, $convertedRectangle);
                        }

                        if ($isConverted) {
                            $convertedFile->delete();

                            if ($originalType == Image::FORMAT_BMP) {
                                //! TODO: Implement BMP support 
                            } else {
                                $convertedImage->save();
                            }

                            $convertedImage->clear();
                        }
                    }

                    $result = true;
                }
            }

            if ($hLock) {
                if ($useLock) {
                    flock($hLock, LOCK_UN);
                }
                fclose($hLock);
            }

            if ($result) {
                return [
                    'SRC' => $convertedRelativePath,
                    'WIDTH' => $convertedRectangle->getWidth(),
                    'HEIGHT' => $convertedRectangle->getHeight(),
                    'SIZE' => $convertedFile->getSize(),
                    'CONTENT_TYPE' => $convertedFile->getContentType(),
                ];
            }
        } catch (\Throwable $th) {
        }
    }

    public function convert($originalPath, $convertedType, $quality = 100, $saveDirName = null)
    {
        try {
            if (!in_array($convertedType, self::SUPPORTED_CONVERTED_TYPES)) {
                return;
            }

            if (!$this->checkSupport($convertedType)) {
                return;
            }

            if ($convertedType === 'webp') {
                if (!function_exists('imagewebp')) {
                    return;
                }
            } elseif ($convertedType === 'avif') {
                if (!function_exists('imageavif')) {
                    return;
                }
            }

            if (!is_string($originalPath)) {
                return;
            }

            $originalRelativePath = str_replace(Application::getDocumentRoot(), '', $originalPath);
            $originalAbsolutePath = Application::getDocumentRoot() . $originalRelativePath;

            $originalFile = new File($originalAbsolutePath);

            if (!$originalFile->isExists()) {
                return;
            }

            $quality = intval($quality);

            if ($quality <= 0 || $quality >= 100) {
                if ($convertedType === 'webp') {
                    $quality = 85;
                } elseif ($convertedType === 'avif') {
                    $quality = 55;
                }
            }

            $originalType = $originalFile->getContentType();

            if (!in_array($originalType, self::SUPPORTED_ORIGINAL_TYPES)) {
                return;
            }

            // fix to not convert wtf

            if ($originalType == 'image/webp') {
                return [
                    'SRC' => $originalRelativePath,
                    'SIZE' => $originalFile->getSize(),
                    'CONTENT_TYPE' => $originalType,
                ];
            };

            $originalName = $originalFile->getName();
            $originalDir = str_replace($originalName, '', str_replace($this->uploadDirName . DIRECTORY_SEPARATOR, '', $originalRelativePath));

            if ($saveDirName === false || $saveDirName === true) {
                // convert in place
                $convertedRelativePath = $originalRelativePath;
                $convertedAbsolutePath = $originalAbsolutePath;
            } else {
                if ($saveDirName === null) {
                    // convert in the folder with the same name as converted type (webp or avif)
                    $saveDirName = $convertedType;
                }

                $convertedRelativePath = DIRECTORY_SEPARATOR . $this->uploadDirName . DIRECTORY_SEPARATOR . $saveDirName . $originalDir . $originalName;
                $convertedAbsolutePath = Application::getDocumentRoot() . $convertedRelativePath;
            }

            if ($originalType === 'image/png') {
                $convertedAbsolutePath = str_replace('.png', $convertedType === 'webp' ? '.webp' : '.avif', $convertedAbsolutePath);
                $convertedRelativePath = str_replace('.png', $convertedType === 'webp' ? '.webp' : '.avif', $convertedRelativePath);
            } else {
                $convertedAbsolutePath = str_replace('.jpg', $convertedType === 'webp' ? '.webp' : '.avif', $convertedAbsolutePath);
                $convertedRelativePath = str_replace('.jpg', $convertedType === 'webp' ? '.webp' : '.avif', $convertedRelativePath);
                $convertedAbsolutePath = str_replace('.jpeg', $convertedType === 'webp' ? '.webp' : '.avif', $convertedAbsolutePath);
                $convertedRelativePath = str_replace('.jpeg', $convertedType === 'webp' ? '.webp' : '.avif', $convertedRelativePath);
            }

            $convertedFile = new File($convertedAbsolutePath);

            if (!$convertedFile->isExists()) {
                if ($originalType === 'image/png') {
                    $im = imagecreatefrompng($originalAbsolutePath);
                    imagepalettetotruecolor($im);
                    imagealphablending($im, true);
                    imagesavealpha($im, true);
                } elseif ($originalType === 'image/jpeg' || $originalType === 'image/jpg') {
                    $im = imagecreatefromjpeg($originalAbsolutePath);
                }

                if ($im) {
                    $convertedFile->putContents('');

                    if ($convertedType === 'webp') {
                        if (!imagewebp($im, $convertedAbsolutePath, $quality)) {
                            $convertedFile->delete();
                            return;
                        }
                    } else {
                        if (!imageavif($im, $convertedAbsolutePath, $quality)) {
                            $convertedFile->delete();
                            return;
                        }
                    }

                    imagedestroy($im);

                    return [
                        'SRC' => $convertedRelativePath,
                        'SIZE' => $convertedFile->getSize(),
                        'CONTENT_TYPE' => $convertedFile->getContentType(),
                    ];
                }
            } else {
                return [
                    'SRC' => $convertedRelativePath,
                    'SIZE' => $convertedFile->getSize(),
                    'CONTENT_TYPE' => $convertedFile->getContentType(),
                ];
            }
        } catch (\Throwable $th) {
        }
    }

    public function getBackground($path, $type, $quality)
    {
        if (!$this->checkSupport($type)) {
            return $path;
        }

        $srcset = $this->convert($path, $type, $quality);

        if (!is_array($srcset)) {
            return $path;
        }

        return 'background-image: url(' . $srcset['SRC'] . ');';
    }

    public function getSource($path, $type, $quality, $lazy = false)
    {
        if (!$this->checkSupport($type)) {
            return '';
        }

        $srcset = $this->convert($path, $type, $quality);

        if (!is_array($srcset)) {
            return '';
        }

        if (!$lazy) {
            return '<source srcset="' . $srcset['SRC'] . '" type="image/' . $type . '">';
        }

        return '<source srcset="data:image/gif;base64,R0lGODlhAQABAID/AP///wAAACwAAAAAAQABAAACAkQBADs=" data-w-lz-srcset="' . $srcset['SRC'] . '" type="image/' . $type . '">';
    }
}
