<?php
/**
 * Copyright © asd All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gsoft\Webpos\Model;

use Gsoft\Webpos\Api\Data\OrderPaymentInterface;
use Gsoft\Webpos\Api\Data\OrderPaymentInterfaceFactory;
use Gsoft\Webpos\Api\Data\OrderPaymentSearchResultsInterfaceFactory;
use Gsoft\Webpos\Api\OrderPaymentRepositoryInterface;
use Gsoft\Webpos\Model\ResourceModel\OrderPayment as ResourceOrderPayment;
use Gsoft\Webpos\Model\ResourceModel\OrderPayment\CollectionFactory as OrderPaymentCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class OrderPaymentRepository implements OrderPaymentRepositoryInterface
{

    /**
     * @var ResourceOrderPayment
     */
    protected $resource;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var OrderPayment
     */
    protected $searchResultsFactory;

    /**
     * @var OrderPaymentCollectionFactory
     */
    protected $orderPaymentCollectionFactory;

    /**
     * @var OrderPaymentInterfaceFactory
     */
    protected $orderPaymentFactory;


    /**
     * @param ResourceOrderPayment $resource
     * @param OrderPaymentInterfaceFactory $orderPaymentFactory
     * @param OrderPaymentCollectionFactory $orderPaymentCollectionFactory
     * @param OrderPaymentSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceOrderPayment $resource,
        OrderPaymentInterfaceFactory $orderPaymentFactory,
        OrderPaymentCollectionFactory $orderPaymentCollectionFactory,
        OrderPaymentSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->orderPaymentFactory = $orderPaymentFactory;
        $this->orderPaymentCollectionFactory = $orderPaymentCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(OrderPaymentInterface $orderPayment)
    {
        try {
            $this->resource->save($orderPayment);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the orderPayment: %1',
                $exception->getMessage()
            ));
        }
        return $orderPayment;
    }

    /**
     * @inheritDoc
     */
    public function get($orderPaymentId)
    {
        $orderPayment = $this->orderPaymentFactory->create();
        $this->resource->load($orderPayment, $orderPaymentId);
        if (!$orderPayment->getId()) {
            throw new NoSuchEntityException(__('OrderPayment with id "%1" does not exist.', $orderPaymentId));
        }
        return $orderPayment;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->orderPaymentCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model;
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(OrderPaymentInterface $orderPayment)
    {
        try {
            $orderPaymentModel = $this->orderPaymentFactory->create();
            $this->resource->load($orderPaymentModel, $orderPayment->getOrderpaymentId());
            $this->resource->delete($orderPaymentModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the OrderPayment: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($orderPaymentId)
    {
        return $this->delete($this->get($orderPaymentId));
    }
}
