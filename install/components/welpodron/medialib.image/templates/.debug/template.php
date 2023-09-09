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


?>
<div>
    <p>$arResult</p>
    <pre>
        <? var_dump($arResult) ?>
    </pre>
    <p>$arParams</p>
    <pre>
        <? var_dump($arParams) ?>
    </pre>
</div>