<?php

namespace Gsoft\Webpos\Model\Api\Data;

use Gsoft\Webpos\Api\Data\StockInterface;

class Stock implements \Gsoft\Webpos\Api\Data\StockInterface
{

    protected $qty;
    protected $status;
    protected $sources;


    /**
     * @inheritDoc
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * @inheritDoc
     */
    public function setQty($qty)
    {
        $this->qty=$qty;
    }

    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @inheritDoc
     */
    public function setStatus($status)
    {
        $this->status=$status;
    }

    /**
     * @inheritDoc
     */
    public function getSources()
    {
        return $this->sources;
    }

    /**
     * @inheritDoc
     */
    public function setSources($sources)
    {
        $this->sources=$sources;
    }
}