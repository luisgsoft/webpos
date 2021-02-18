<?php

namespace Gsoft\Webpos\Model\Api;

use Gsoft\Webpos\Api\CatalogInterface;

class Catalog implements CatalogInterface
{

    protected $hlp;
    protected $manager;


    public function __construct(
        \Gsoft\Webpos\Helper\Data $helper
    )
    {

        $this->hlp = $helper;

        if ($this->hlp ->isVersionGreatherOrEqual("2.3")) {
            $this->manager = $this->hlp->loadObject("\Gsoft\Webpos\Version\V3\Model\Api\Catalog");
        } else {
            $this->manager = $this->hlp->loadObject("\Gsoft\Webpos\Version\V2\Model\Api\Catalog");

        }

    }

    /**
     * Get simples from configurable
     *
     * @api
     * @param string $sku
     * @return mixed[]
     */

    public function getSimples($sku, $store_id)
    {


        return $this->manager->getSimples($sku, $store_id);

    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getProductList($searchCriteria)
    {

        return $this->manager->getProductList($searchCriteria);

    }


}