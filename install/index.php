<?

use Bitrix\Main\ModuleManager;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\Application;

class welpodron_image extends CModule
{
    var $MODULE_ID = 'welpodron.image';

    public function InstallFiles()
    {
        global $APPLICATION;

        try {
            if (!CopyDirFiles(__DIR__ . '/packages/', Application::getDocumentRoot() . '/local/packages', true, true)) {
                $APPLICATION->ThrowException('Не удалось скопировать используемый модулем пакет');
                return false;
            };
            if (!CopyDirFiles(__DIR__ . '/components/', Application::getDocumentRoot() . '/local/components', true, true)) {
                $APPLICATION->ThrowException('Не удалось скопировать компоненты модуля');
                return false;
            };
        } catch (\Throwable $th) {
            $APPLICATION->ThrowException($th->getMessage() . '\n' . $th->getTraceAsString());
            return false;
        }

        return true;
    }

    public function UnInstallFiles()
    {
        Directory::deleteDirectory(Application::getDocumentRoot() . '/local/packages/' . $this->MODULE_ID);

        $arComponents = scandir(__DIR__ . '/components/welpodron');

        if ($arComponents) {
            $arComponents = array_diff($arComponents, ['..', '.']);

            foreach ($arComponents as $component) {
                Directory::deleteDirectory(Application::getDocumentRoot() . '/local/components/welpodron/' . $component);
            }
        }
    }

    public function DoInstall()
    {
        global $APPLICATION;

        if (!CheckVersion(ModuleManager::getVersion('main'), '14.00.00')) {
            $APPLICATION->ThrowException('Версия главного модуля ниже 14.00.00');
            return false;
        }

        if (!$this->InstallFiles()) {
            return false;
        }

        ModuleManager::registerModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile('Установка модуля ' . $this->MODULE_ID, __DIR__ . '/step.php');
    }

    public function DoUninstall()
    {
        global $APPLICATION;

        $this->UnInstallFiles();

        ModuleManager::unRegisterModule($this->MODULE_ID);
        $APPLICATION->IncludeAdminFile('Деинсталляция модуля ' . $this->MODULE_ID, __DIR__ . '/unstep.php');
    }

    public function __construct()
    {
        $this->MODULE_ID = 'welpodron.image';
        $this->MODULE_NAME = 'Изображения';
        $this->MODULE_DESCRIPTION = 'Модуль для работы с изображениями';
        $this->PARTNER_NAME = 'Welpodron';
        $this->PARTNER_URI = 'https://github.com/Welpodron';

        $arModuleVersion = [];
        include(__DIR__ . "/version.php");

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
    }
}
