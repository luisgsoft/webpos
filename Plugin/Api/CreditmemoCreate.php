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
        $eventManager = $objectManager->get('\Magento\Framework\Event\Manager');

        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();

        /**@var $ext \Magento\Sales\Api\Data\CreditmemoCreationArgumentsExtensionMagento\Sales\Api\Data\CreditmemoExtension */
        $ext = $arguments->getExtensionAttributes();
        $terminal=$user=$booking=$payment=null;
        if(!empty($ext)) {
            $terminal = $ext->getWebposTerminal();
            $user = $ext->getWebposUser() ?? null;

            $booking = $ext->getWebposBooking() ?? NULL;
            $payment = $ext->getWebposPayment();
        }
        //$eventManager->dispatch('gsoft_webpos_creditmemo_api', ['creditmemo' => 13, 'arguments'=>$arguments]);
        $id = parent::execute($orderId, $items, $notify, $appendComment, $comment, $arguments);
        $eventManager->dispatch('gsoft_webpos_creditmemo_api', ['creditmemo' => $id, 'arguments' => $arguments]);

        if ($id > 0 && !empty($terminal)) {

            $creditmemo = $objectManager->get('\Magento\Sales\Model\Order\CreditmemoRepository')->get($id);
            /**@var $creditmemo \Magento\Sales\Api\Data\CreditmemoInterface */
            $orderPaymentFactory = $objectManager->get('\Gsoft\Webpos\Model\OrderPaymentFactory');
            $objDate = $objectManager->create('\Magento\Framework\Stdlib\DateTime\DateTime');

            try {

                $sql = "update sales_creditmemo set webpos_terminal=" . $connection->quote($terminal) . ",webpos_payment=" . $connection->quote($payment) . ", webpos_booking=".($booking=="1"?1:'NULL')." where entity_id=" . $id;
                $connection->query($sql);
                /**@var /Magento\Sales\Model\CreditmemoRepository $creditmemos */

                $payments = json_decode($payment, true);
                foreach ($payments as $orderpayment) {
                    $webpospayment = $orderPaymentFactory->create();
                    $webpospayment->setData($orderpayment);
                    $webpospayment->setData("increment_id", $creditmemo->getIncrementId());
                    $webpospayment->setData("user", $user);
                    $webpospayment->setData("amount", $orderpayment['delivered']);
                    $webpospayment->setData("creditmemo_id", $id);
                    $webpospayment->setData("webpos_booking", ($booking!="1")?null:1);
                    $webpospayment->setData("terminal", $terminal);
                    $webpospayment->setData("created_at", $objDate->gmtDate());
                    $webpospayment->save();
                }
            } catch (\Exception $e) {

            }

        }

        return $id;

    }

}
