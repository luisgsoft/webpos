<?php

namespace Gsoft\Webpos\Setup\Patch\Data;


use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Store\Model\StoreManagerInterface;

class DetectVersion implements DataPatchInterface, PatchRevertableInterface
{
    protected $scopeConfig;
    protected $directoryList;
    protected $productMetadata;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface                           $moduleDataSetup,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Filesystem\DirectoryList    $directoryList,
        \Magento\Framework\App\ProductMetadataInterface    $productMetadata
    )
    {
        /**
         * If before, we pass $setup as argument in install/upgrade function, from now we start
         * inject it with DI. If you want to use setup, you can inject it, with the same way as here
         */
        $this->moduleDataSetup = $moduleDataSetup;

        $this->scopeConfig = $scopeConfig;
        $this->directoryList = $directoryList;
        $this->productMetadata = $productMetadata;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
        $output = ['error' => 0];

        try {
            $path_extract = $this->directoryList->getPath('vendor') . DS . 'gsoft' . DS . 'module-webpos';
            if ($this->isVersionGreatherOrEqual("2.3")) {
                $this->removeDir($path_extract . DS . "Version" . DS . "V2");
            } else {
                $this->removeDir($path_extract . DS . "Version" . DS . "V3");
            }

        } catch (\Exception $e) {
            $output['error'] = 1;
            $output['msg'] = $e->getMessage();
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }
    private function removeDir($dir)
    {
        if (!file_exists($dir)) return;
        $it = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($it,
            \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($dir);
    }
    private function isVersionGreatherOrEqual($version)
    {
        if (version_compare($this->productMetadata->getVersion(), $version, ">=")) return true;
        else return false;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        /**
         * This is dependency to another patch. Dependency should be applied first
         * One patch can have few dependencies
         * Patches do not have versions, so if in old approach with Install/Ugrade data scripts you used
         * versions, right now you need to point from patch with higher version to patch with lower version
         * But please, note, that some of your patches can be independent and can be installed in any sequence
         * So use dependencies only if this important for you
         */
        return [

        ];
    }

    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        //Here should go code that will revert all operations from `apply` method
        //Please note, that some operations, like removing data from column, that is in role of foreign key reference
        //is dangerous, because it can trigger ON DELETE statement
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        /**
         * This internal Magento method, that means that some patches with time can change their names,
         * but changing name should not affect installation process, that's why if we will change name of the patch
         * we will add alias here
         */
        return [];
    }
}
