<?php

namespace Gsoft\Webpos\Plugin\Api;


class CreditmemoCreate extends \Magento\Sales\Model\RefundOrder implements \Magento\Sales\Api\RefundOrderInterface
{


    public function execute(
        $orderId,
        array $items = [],
        $notify = false,
        $appendComment = false,
        \Magento\Sales\Api\Data\CreditmemoCommentCreationInterface $comment = null,
        \Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface $arguments = null
    )
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $eventManager=$objectManager->get('\Magento\Framework\Event\Manager');

        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        /**@var $ext \Magento\Sales\Api\Data\CreditmemoCreationArgumentsExtensionMagento\Sales\Api\Data\CreditmemoExtension */
        $ext = $arguments->getExtensionAttributes();
        $terminal = $ext->getWebposTerminal();
        $payment = $ext->getWebposPayment();
        //$eventManager->dispatch('gsoft_webpos_creditmemo_api', ['creditmemo' => 13, 'arguments'=>$arguments]);
        $id = parent::execute($orderId, $items, $notify, $appendComment, $comment, $arguments);
        $eventManager->dispatch('gsoft_webpos_creditmemo_api', ['creditmemo' => $id, 'arguments'=>$arguments]);
        if($id>0) {
            $sql="update sales_creditmemo set webpos_terminal=" . $connection->quote($terminal) . ",webpos_payment=" . $connection->quote($payment) . " where entity_id=" . $id;
            $connection->query($sql);
            /**@var /Magento\Sales\Model\CreditmemoRepository $creditmemos*/


        }
        return $id;

    }

}