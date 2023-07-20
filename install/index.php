<?

use Bitrix\Main\ModuleManager;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\Application;

class welpodron_image extends CModule
{
    public function InstallFiles()
    {
        global $APPLICATION;

        try {
            // На данный момент папка перемещается в local пространство
            if (!CopyDirFiles(__DIR__ . '/components/', Application::getDocumentRoot() . '/local/components', true, true)) {
                $APPLICATION->ThrowException('Не удалось скопировать компоненты');
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
        Directory::deleteDirectory(Application::getDocumentRoot() . '/local/components/welpodron/medialib.image');
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
    }
}
