<?php

namespace Gsoft\Webpos\Model\Api\Data;

use Gsoft\Webpos\Api\Data\StockInterface;
use Gsoft\Webpos\Api\Data\StocksourceInterface;

class Stocksource implements \Gsoft\Webpos\Api\Data\StocksourceInterface
{

    protected $qty;
    protected $status;
    protected $source_code;
    protected $source_id;


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
    public function getSourceCode()
    {
        return $this->source_code;
    }

    /**
     * @inheritDoc
     */
    public function setSourceCode($source)
    {
        $this->source_code=$source;
    }

    /**
     * @inheritDoc
     */
    public function getSourceId()
    {
        return $this->source_id;
    }

    /**
     * @inheritDoc
     */
    public function setSourceId($source_id)
    {
         $this->source_id=$source_id;
    }
}