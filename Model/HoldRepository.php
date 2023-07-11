<?php
/**
 * Copyright © asd All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gsoft\Webpos\Model;

use Gsoft\Webpos\Api\Data\HoldInterface;
use Gsoft\Webpos\Api\Data\HoldInterfaceFactory;
use Gsoft\Webpos\Api\Data\HoldSearchResultsInterfaceFactory;
use Gsoft\Webpos\Api\HoldRepositoryInterface;
use Gsoft\Webpos\Model\ResourceModel\Hold as ResourceHold;
use Gsoft\Webpos\Model\ResourceModel\Hold\CollectionFactory as HoldCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class HoldRepository implements HoldRepositoryInterface
{

    /**
     * @var HoldCollectionFactory
     */
    protected $holdCollectionFactory;

    /**
     * @var HoldInterfaceFactory
     */
    protected $holdFactory;

    /**
     * @var ResourceHold
     */
    protected $resource;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var Hold
     */
    protected $searchResultsFactory;


    /**
     * @param ResourceHold $resource
     * @param HoldInterfaceFactory $holdFactory
     * @param HoldCollectionFactory $holdCollectionFactory
     * @param HoldSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceHold $resource,
        HoldInterfaceFactory $holdFactory,
        HoldCollectionFactory $holdCollectionFactory,
        HoldSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->holdFactory = $holdFactory;
        $this->holdCollectionFactory = $holdCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(HoldInterface $hold)
    {
        try {
            $this->resource->save($hold);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the hold: %1',
                $exception->getMessage()
            ));
        }
        return $hold;
    }

    /**
     * @inheritDoc
     */
    public function get($holdId)
    {
        $hold = $this->holdFactory->create();
        $this->resource->load($hold, $holdId);
        if (!$hold->getId()) {
            throw new NoSuchEntityException(__('Hold with id "%1" does not exist.', $holdId));
        }
        return $hold;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->holdCollectionFactory->create();

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
    public function delete(HoldInterface $hold)
    {
        try {
            $holdModel = $this->holdFactory->create();
            $this->resource->load($holdModel, $hold->getHoldId());
            $this->resource->delete($holdModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Hold: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($holdId)
    {
        return $this->delete($this->get($holdId));
    }
}

