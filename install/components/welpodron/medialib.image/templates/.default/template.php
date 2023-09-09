<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */


use Bitrix\Main\Loader;

Loader::includeModule('welpodron.image');

use Welpodron\Image\Utils\Converter;

?>

<? if ($arResult) : ?>
    <picture>
        <?
        if ($arParams['AVIF_USE']) {
            echo Converter::getInstance()->getSource($arResult['FILE_PATH'], 'avif', $arParams['AVIF_QUALITY'], $arParams['LAZY_USE']);
        }
        ?>
        <?
        if ($arParams['WEBP_USE']) {
            echo Converter::getInstance()->getSource($arResult['FILE_PATH'], 'webp', $arParams['WEBP_QUALITY'], $arParams['LAZY_USE']);
        }
        ?>
        <img <?= ($arResult['IMG_ID'] ? 'id="' . $arResult['IMG_ID'] . '"' : '') ?> <?= ($arResult['IMG_CLASS'] ? 'class="' . $arResult['IMG_CLASS'] . '"' : '') ?> <?= ($arResult['IMG_ALT'] ? 'alt="' . $arResult['IMG_ALT'] . '"' : 'alt') ?> <?= ($arParams['LAZY_USE'] ? 'data-lz src="data:image/gif;base64,R0lGODlhAQABAID/AP///wAAACwAAAAAAQABAAACAkQBADs=" data-src="' . $arResult['FILE_PATH'] . '"' : 'src="' . $arResult['FILE_PATH'] . '"') ?> width="<?= $arResult['IMG_WIDTH'] ?>" height="<?= $arResult['IMG_HEIGHT'] ?>" />
    </picture>
<? endif ?>