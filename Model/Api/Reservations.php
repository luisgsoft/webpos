<?php

namespace Gsoft\Webpos\Model\Api;



class Reservations implements \Gsoft\Webpos\Api\ReservationsInterface
{
    protected $storeManager;
    protected $reservesFactory;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Gsoft\Webpos\Model\StockreservationFactory $reservationF

    )
    {
        $this->storeManager = $storeManager;
        $this->reservesFactory=$reservationF;
    }

    public function reservations($source, $page=0, $limit=20, $notaccepted=0){

        $model=$this->reservesFactory->create();
        /**@var \Gsoft\Webpos\Model\ResourceModel\Stockreservation\Collection $collection */
        $collection = $model->getCollection()->addFieldToFilter("source", $source);
        if($notaccepted == "1"){
            $collection->addFieldToFilter("accepted", 0);
            return $collection->getSize();
        }else {
            $collection->getSelect()->join("sales_order", "sales_order.entity_id=main_table.order_id", "sales_order.increment_id");
            $collection->setPageSize($limit);
            $collection->addOrder("created_at", "DESC");
            $collection->setCurPage($page);
        }
        return $collection->toArray();

    }

    public function acceptReservation($id, $name){
        try {


            /**@var \Gsoft\Webpos\Model\ResourceModel\Stockreservation $model */
            $model = $this->reservesFactory->create();
            $model->load($id);
            if($model->getData("shipped")=="1"){
                $model->delete();
            }else {
                $model->setData("accepted", 1);
                $model->setData("accepted_at", date("Y-m-d H:i:s"));
                $model->setData("accepted_user", $name);
                $model->save();
            }
            return ['error'=>0];
        }catch(Exception $e){
            return ['error'=>1, 'msg'=>$e->getMessage()];
        }
    }
}