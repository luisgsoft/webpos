<?php
/**
 * Copyright © asd All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gsoft\Webpos\Controller\Adminhtml\Hold;

class Delete extends \Gsoft\Webpos\Controller\Adminhtml\Hold
{

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('hold_id');
        if ($id) {
            try {
                // init model and delete
                $model = $this->_objectManager->create(\Gsoft\Webpos\Model\Hold::class);
                $model->load($id);
                $model->delete();
                // display success message
                $this->messageManager->addSuccessMessage(__('You deleted the Hold.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['hold_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a Hold to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}

