<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Gsoft\Webpos\Setup;

use Magento\Customer\Model\Customer;
use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Setup\SetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DB\FieldDataConverterFactory;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Customer\Setup\CustomerSetupFactory;

/**
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Customer setup factory
     *
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * @var AllowedCountries
     */
    private $allowedCountriesReader;

    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var FieldDataConverterFactory
     */
    private $fieldDataConverterFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var Magento\Customer\Api\Data\AddressInterfaceFactory
     */
    protected $addressDataFactory;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */

    /**
     *  @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $configWriter;


    protected $scopeConfig;
    /**
     * @param CustomerSetupFactory $customerSetupFactory
     * @param IndexerRegistry $indexerRegistry
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param FieldDataConverterFactory|null $fieldDataConverterFactory
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        IndexerRegistry $indexerRegistry,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\App\State $state,
        FieldDataConverterFactory $fieldDataConverterFactory = null
    )
    {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->indexerRegistry = $indexerRegistry;
        $this->eavConfig = $eavConfig;
        $this->customerFactory = $customerFactory;
        $this->storeManager = $storeManager;
        $this->addressRepository = $addressRepository;
        $this->addressDataFactory = $addressDataFactory;
        $this->scopeConfig=$scopeConfig;
        $this->fieldDataConverterFactory = $fieldDataConverterFactory ?: ObjectManager::getInstance()->get(
            FieldDataConverterFactory::class
        );
        $this->configWriter = $configWriter;
        $state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        /** @var CustomerSetup $customerSetup */


        if (version_compare($context->getVersion(), '1.0.1', '<')) {

            $websiteId = 1;// $this->storeManager->getWebsite()->getWebsiteId();

            // Instantiate object (this is the most important part)
            /**@var \Magento\Customer\Model\Customer $customer*/
            $customer = $this->customerFactory->create();

            //$customer->setWebsiteId($websiteId);
            $customer->setEmail("webpos@mail.com");
            $customer->setFirstname("Webpos");
            $customer->setLastname("Webpos");
            $customer->setPassword(uniqid());
            $customer->setTaxvat(".");
            $customer->setAddresses(null);



            $storeId = $this->storeManager->getWebsite($websiteId)->getDefaultStore()->getId();
            $customer->setStoreId($storeId);

            $storeName = $this->storeManager->getStore($customer->getStoreId())->getName();
            $customer->setCreatedIn($storeName);
            try {
                $customer->save();
                /**@var \Magento\Customer\Model\Address $address*/
                $address = $this->addressDataFactory->create();

                $country=$this->scopeConfig->getValue('general/store_information/country_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if(empty($country)) $country="ES";

                $regionId=$this->scopeConfig->getValue('general/store_information/region_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if(empty($regionId)) $regionId="177";

                //$region=$this->scopeConfig->getValue('general/store_information/region', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                //if(empty($region))
                $region=null;

                $city=$this->scopeConfig->getValue('general/store_information/city', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if(empty($city)) $city="Valencia";

                $zip=$this->scopeConfig->getValue('general/store_information/postcode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if(empty($zip)) $zip="46019";

                $address->setFirstname("webpos")
                    ->setLastname("webpos")
                    ->setCountryId($country)
                    ->setRegionId($regionId)
                    ->setRegion($region)
                    ->setCity($city)
                    ->setPostcode($zip)
                    ->setCustomerId($customer->getId())
                    ->setStreet(['XXX'])
                    ->setCompany('XX')
                    ->setVatId(".")
                    ->setTelephone("XX");


                $this->addressRepository->save($address);

                $customer->setDefaultBilling($address->getId())->setDefaultShipping($address->getId())->save();

                $this->configWriter->save('webpos/general/guest_default',  $customer->getId(), $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $scopeId = 0);

                /**@todo
                 * No guarda los datos al crear el customer. tambien habría que guardar el metodo de envio
                 **/

            }catch(\Exception $e) {

                echo $e->getMessage();
                echo $e->getTraceAsString();
            }
            /*  $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
              $attributeCode = "local_sync_at";

              //$customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, $attributeCode);

              $customerSetup->addAttribute('customer',
                  $attributeCode, [
                      'label' => 'Passport',
                      'type' => 'datetime',
                      'frontend_input' => 'text',
                      'required' => false,
                      'visible' => false,
                      'system'=> 0,
                      //'position' => 105,
                  ]);
  */
        }


        $this->eavConfig->clear();
        $setup->endSetup();
    }

    /**
     * Retrieve Store Manager
     *
     * @deprecated 100.1.3
     * @return StoreManagerInterface
     */
    private function getStoreManager()
    {
        if (!$this->storeManager) {
            $this->storeManager = ObjectManager::getInstance()->get(StoreManagerInterface::class);
        }

        return $this->storeManager;
    }

    /**
     * Retrieve Allowed Countries Reader
     *
     * @deprecated 100.1.3
     * @return AllowedCountries
     */
    private function getAllowedCountriesReader()
    {
        if (!$this->allowedCountriesReader) {
            $this->allowedCountriesReader = ObjectManager::getInstance()->get(AllowedCountries::class);
        }

        return $this->allowedCountriesReader;
    }

    /**
     * Merge allowed countries between different scopes
     *
     * @param array $countries
     * @param array $newCountries
     * @param string $identifier
     * @return array
     */
    private function mergeAllowedCountries(array $countries, array $newCountries, $identifier)
    {
        if (!isset($countries[$identifier])) {
            $countries[$identifier] = $newCountries;
        } else {
            $countries[$identifier] =
                array_replace($countries[$identifier], $newCountries);
        }

        return $countries;
    }

    /**
     * Retrieve countries not depending on global scope
     *
     * @param string $scope
     * @param int $scopeCode
     * @return array
     */
    private function getAllowedCountries($scope, $scopeCode)
    {
        $reader = $this->getAllowedCountriesReader();
        return $reader->makeCountriesUnique($reader->getCountriesFromConfig($scope, $scopeCode));
    }

    /**
     * Merge allowed countries from stores to websites
     *
     * @param SetupInterface $setup
     * @return void
     */
    private function migrateStoresAllowedCountriesToWebsite(SetupInterface $setup)
    {
        $allowedCountries = [];
        //Process Websites
        foreach ($this->getStoreManager()->getStores() as $store) {
            $allowedCountries = $this->mergeAllowedCountries(
                $allowedCountries,
                $this->getAllowedCountries(ScopeInterface::SCOPE_STORE, $store->getId()),
                $store->getWebsiteId()
            );
        }
        //Process stores
        foreach ($this->getStoreManager()->getWebsites() as $website) {
            $allowedCountries = $this->mergeAllowedCountries(
                $allowedCountries,
                $this->getAllowedCountries(ScopeInterface::SCOPE_WEBSITE, $website->getId()),
                $website->getId()
            );
        }

        $connection = $setup->getConnection();

        //Remove everything from stores scope
        $connection->delete(
            $setup->getTable('core_config_data'),
            [
                'path = ?' => AllowedCountries::ALLOWED_COUNTRIES_PATH,
                'scope = ?' => ScopeInterface::SCOPE_STORES
            ]
        );

        //Update websites
        foreach ($allowedCountries as $scopeId => $countries) {
            $connection->update(
                $setup->getTable('core_config_data'),
                [
                    'value' => implode(',', $countries)
                ],
                [
                    'path = ?' => AllowedCountries::ALLOWED_COUNTRIES_PATH,
                    'scope_id = ?' => $scopeId,
                    'scope = ?' => ScopeInterface::SCOPE_WEBSITES
                ]
            );
        }
    }

    /**
     * @param array $entityAttributes
     * @param CustomerSetup $customerSetup
     * @return void
     */
    protected function upgradeAttributes(array $entityAttributes, CustomerSetup $customerSetup)
    {
        foreach ($entityAttributes as $entityType => $attributes) {
            foreach ($attributes as $attributeCode => $attributeData) {
                $attribute = $customerSetup->getEavConfig()->getAttribute($entityType, $attributeCode);
                foreach ($attributeData as $key => $value) {
                    $attribute->setData($key, $value);
                }
                $attribute->save();
            }
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function upgradeHash($setup)
    {
        $customerEntityTable = $setup->getTable('customer_entity');

        $select = $setup->getConnection()->select()->from(
            $customerEntityTable,
            ['entity_id', 'password_hash']
        );

        $customers = $setup->getConnection()->fetchAll($select);
        foreach ($customers as $customer) {
            if ($customer['password_hash'] === null) {
                continue;
            }
            list($hash, $salt) = explode(Encryptor::DELIMITER, $customer['password_hash']);

            $newHash = $customer['password_hash'];
            if (strlen($hash) === 32) {
                $newHash = implode(Encryptor::DELIMITER, [$hash, $salt, Encryptor::HASH_VERSION_MD5]);
            } elseif (strlen($hash) === 64) {
                $newHash = implode(Encryptor::DELIMITER, [$hash, $salt, Encryptor::HASH_VERSION_SHA256]);
            }

            $bind = ['password_hash' => $newHash];
            $where = ['entity_id = ?' => (int)$customer['entity_id']];
            $setup->getConnection()->update($customerEntityTable, $bind, $where);
        }
    }

    /**
     * @param CustomerSetup $customerSetup
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function upgradeVersionTwoZeroOne($customerSetup)
    {
        $entityAttributes = [
            'customer' => [
                'website_id' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => false,
                ],
                'created_in' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => false,
                    'is_searchable_in_grid' => true,
                ],
                'email' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => true,
                ],
                'group_id' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => false,
                ],
                'dob' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => false,
                ],
                'taxvat' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => false,
                    'is_searchable_in_grid' => true,
                ],
                'confirmation' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => false,
                ],
                'created_at' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => false,
                ],
                'gender' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => false,
                ],
            ],
            'customer_address' => [
                'company' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'is_searchable_in_grid' => true,
                ],
                'street' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'is_searchable_in_grid' => true,
                ],
                'city' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'is_searchable_in_grid' => true,
                ],
                'country_id' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => false,
                ],
                'region' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => false,
                    'is_searchable_in_grid' => true,
                ],
                'region_id' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => false,
                ],
                'postcode' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => true,
                ],
                'telephone' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => true,
                ],
                'fax' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'is_searchable_in_grid' => true,
                ],
            ],
        ];
        $this->upgradeAttributes($entityAttributes, $customerSetup);
    }

    /**
     * @param CustomerSetup $customerSetup
     * @return void
     */
    private function upgradeVersionTwoZeroTwo($customerSetup)
    {
        $entityTypeId = $customerSetup->getEntityTypeId(Customer::ENTITY);
        $attributeId = $customerSetup->getAttributeId($entityTypeId, 'gender');

        $option = ['attribute_id' => $attributeId, 'values' => [3 => 'Not Specified']];
        $customerSetup->addAttributeOption($option);
    }

    /**
     * @param CustomerSetup $customerSetup
     * @return void
     */
    private function upgradeVersionTwoZeroThree($customerSetup)
    {
        $entityAttributes = [
            'customer_address' => [
                'region_id' => [
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'is_searchable_in_grid' => false,
                ],
                'firstname' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'is_searchable_in_grid' => true,
                ],
                'lastname' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'is_searchable_in_grid' => true,
                ],
            ],
        ];
        $this->upgradeAttributes($entityAttributes, $customerSetup);
    }

    /**
     * @param CustomerSetup $customerSetup
     * @return void
     */
    private function upgradeVersionTwoZeroFour($customerSetup)
    {
        $customerSetup->addAttribute(
            Customer::ENTITY,
            'updated_at',
            [
                'type' => 'static',
                'label' => 'Updated At',
                'input' => 'date',
                'required' => false,
                'sort_order' => 87,
                'visible' => false,
                'system' => false,
            ]
        );
    }

    /**
     * @param CustomerSetup $customerSetup
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function upgradeVersionTwoZeroFive($customerSetup, $setup)
    {
        $this->upgradeHash($setup);
        $entityAttributes = [
            'customer_address' => [
                'fax' => [
                    'is_visible' => false,
                    'is_system' => false,
                ],
            ],
        ];
        $this->upgradeAttributes($entityAttributes, $customerSetup);
    }

    /**
     * @param CustomerSetup $customerSetup
     * @return void
     */
    private function upgradeVersionTwoZeroSix($customerSetup)
    {
        $customerSetup->updateEntityType(
            \Magento\Customer\Model\Customer::ENTITY,
            'entity_model',
            \Magento\Customer\Model\ResourceModel\Customer::class
        );
        $customerSetup->updateEntityType(
            \Magento\Customer\Model\Customer::ENTITY,
            'increment_model',
            \Magento\Eav\Model\Entity\Increment\NumericValue::class
        );
        $customerSetup->updateEntityType(
            \Magento\Customer\Model\Customer::ENTITY,
            'entity_attribute_collection',
            \Magento\Customer\Model\ResourceModel\Attribute\Collection::class
        );
        $customerSetup->updateEntityType(
            'customer_address',
            'entity_model',
            \Magento\Customer\Model\ResourceModel\Address::class
        );
        $customerSetup->updateEntityType(
            'customer_address',
            'entity_attribute_collection',
            \Magento\Customer\Model\ResourceModel\Address\Attribute\Collection::class
        );
        $customerSetup->updateAttribute(
            'customer_address',
            'country_id',
            'source_model',
            \Magento\Customer\Model\ResourceModel\Address\Attribute\Source\Country::class
        );
        $customerSetup->updateAttribute(
            'customer_address',
            'region',
            'backend_model',
            \Magento\Customer\Model\ResourceModel\Address\Attribute\Backend\Region::class
        );
        $customerSetup->updateAttribute(
            'customer_address',
            'region_id',
            'source_model',
            \Magento\Customer\Model\ResourceModel\Address\Attribute\Source\Region::class
        );
    }

    /**
     * @param CustomerSetup $customerSetup
     * @return void
     */
    private function upgradeVersionTwoZeroSeven($customerSetup)
    {
        $customerSetup->addAttribute(
            Customer::ENTITY,
            'failures_num',
            [
                'type' => 'static',
                'label' => 'Failures Number',
                'input' => 'hidden',
                'required' => false,
                'sort_order' => 100,
                'visible' => false,
                'system' => true,
            ]
        );

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'first_failure',
            [
                'type' => 'static',
                'label' => 'First Failure Date',
                'input' => 'date',
                'required' => false,
                'sort_order' => 110,
                'visible' => false,
                'system' => true,
            ]
        );

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'lock_expires',
            [
                'type' => 'static',
                'label' => 'Failures Number',
                'input' => 'date',
                'required' => false,
                'sort_order' => 120,
                'visible' => false,
                'system' => true,
            ]
        );
    }

    /**
     * @param CustomerSetup $customerSetup
     * @return void
     */
    private function upgradeVersionTwoZeroTwelve(CustomerSetup $customerSetup)
    {
        $customerSetup->updateAttribute('customer_address', 'vat_id', 'frontend_label', 'VAT Number');
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function upgradeCustomerPasswordResetlinkExpirationPeriodConfig($setup)
    {
        $configTable = $setup->getTable('core_config_data');

        $setup->getConnection()->update(
            $configTable,
            ['value' => new \Zend_Db_Expr('value*24')],
            ['path = ?' => \Magento\Customer\Model\Customer::XML_PATH_CUSTOMER_RESET_PASSWORD_LINK_EXPIRATION_PERIOD]
        );
    }

    /**
     * @param CustomerSetup $customerSetup
     */
    private function upgradeVersionTwoZeroThirteen(CustomerSetup $customerSetup)
    {
        $entityAttributes = [
            'customer_address' => [
                'firstname' => [
                    'input_filter' => 'trim'
                ],
                'lastname' => [
                    'input_filter' => 'trim'
                ],
                'middlename' => [
                    'input_filter' => 'trim'
                ],
            ],
            'customer' => [
                'firstname' => [
                    'input_filter' => 'trim'
                ],
                'lastname' => [
                    'input_filter' => 'trim'
                ],
                'middlename' => [
                    'input_filter' => 'trim'
                ],
            ],
        ];
        $this->upgradeAttributes($entityAttributes, $customerSetup);
    }
}
