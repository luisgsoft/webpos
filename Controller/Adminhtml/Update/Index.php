<?php
/**
 * Generar catalogo en pdf
 * Copyright (C) 2019 Gsoft
 *
 * This file is part of Istobal/Catalogpdf.
 *
 * Istobal/Catalogpdf is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Gsoft\Webpos\Controller\Adminhtml\Update;

class Index extends \Magento\Backend\App\Action
{

    protected $scopeConfig;
    protected $directoryList;
    protected $hlp;
    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Gsoft\Webpos\Helper\Data $helper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->directoryList=$directoryList;
        $this->hlp = $helper;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
        $output=['error'=>0];
        $path = $this->directoryList->getPath('var').DS."package.zip";
        try {
            $f = file_put_contents($path, fopen($this->scopeConfig->getValue("webpos/general/update_url"), 'r'), LOCK_EX);
            if (FALSE === $f) {
                $output['error']=1;
                $output['msg']="Error al descargar el paquete";
            } else {

                $zip = new \ZipArchive;
                $res = $zip->open($path);
                $path_extract = $this->directoryList->getPath('app') . DS . 'code' . DS.'Gsoft'.DS;
                if ($res === TRUE) {
                    $zip->extractTo($path_extract);
                    $zip->close();
                    if ($this->hlp->isVersionGreatherOrEqual("2.3")) {
                        $this->removeDir($path_extract . DS . "Webpos" . DS . "Version" . DS . "V2");
                    } else {
                        $this->removeDir($path_extract . DS . "Webpos" . DS . "Version" . DS . "V3");
                    }

                    //
                } else {
                    $output['error']=1;
                    $output['msg']="Error al descomprimir";
                }
            }
        }catch(\Exception $e){
            $output['error']=1;
            $output['msg']=$e->getMessage();
        }
        header('Content-Type: application/json');
        echo json_encode($output);
        die();
    }
    private function removeDir($dir){

        $it = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($it,
            \RecursiveIteratorIterator::CHILD_FIRST);
        foreach($files as $file) {
            if ($file->isDir()){
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($dir);
    }
}
