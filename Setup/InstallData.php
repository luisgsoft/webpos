<?php

namespace Gsoft\Webpos\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\InstallDataInterface;

class InstallData implements InstallDataInterface
{

    protected $scopeConfig;
    protected $directoryList;
    protected $productMetadata;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Filesystem\DirectoryList    $directoryList,
        \Magento\Framework\App\ProductMetadataInterface    $productMetadata)
    {
        $this->scopeConfig = $scopeConfig;
        $this->directoryList = $directoryList;
        $this->productMetadata = $productMetadata;
    }

    protected function isVersionGreatherOrEqual($version)
    {
        if (version_compare($this->productMetadata->getVersion(), $version, ">=")) return true;
        else return false;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->execute();
        $setup->endSetup();
    }

    public function execute()
    {
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

}
