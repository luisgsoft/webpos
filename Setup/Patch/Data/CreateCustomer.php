<?php

namespace Gsoft\Webpos\Setup\Patch\Data;


use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Store\Model\StoreManagerInterface;

class CreateCustomer implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
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
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $configWriter;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface                           $moduleDataSetup,
        \Magento\Customer\Model\CustomerFactory            $customerFactory,
        \Magento\Store\Model\StoreManagerInterface         $storeManager,
        \Magento\Customer\Api\AddressRepositoryInterface   $addressRepository,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
    )
    {
        /**
         * If before, we pass $setup as argument in install/upgrade function, from now we start
         * inject it with DI. If you want to use setup, you can inject it, with the same way as here
         */
        $this->moduleDataSetup = $moduleDataSetup;

        $this->customerFactory = $customerFactory;
        $this->storeManager = $storeManager;
        $this->addressRepository = $addressRepository;
        $this->addressDataFactory = $addressDataFactory;
        $this->scopeConfig=$scopeConfig;
        $this->configWriter = $configWriter;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $websiteId = 1;// $this->storeManager->getWebsite()->getWebsiteId();

        // Instantiate object (this is the most important part)
        /**@var \Magento\Customer\Model\Customer $customer */
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
            /**@var \Magento\Customer\Model\Address $address */
            $address = $this->addressDataFactory->create();

            $country = $this->scopeConfig->getValue('general/store_information/country_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if (empty($country)) $country = "ES";

            $regionId = $this->scopeConfig->getValue('general/store_information/region_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if (empty($regionId)) $regionId = "177";

            $region = null;

            $city = $this->scopeConfig->getValue('general/store_information/city', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if (empty($city)) $city = "Valencia";

            $zip = $this->scopeConfig->getValue('general/store_information/postcode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if (empty($zip)) $zip = "46000";

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

            $this->configWriter->save('webpos/general/guest_customer', $customer->getId(), \Magento\Store\Model\ScopeInterface::SCOPE_STORE, 0);

            /**@todo
             * No guarda los datos al crear el customer. tambien habría que guardar el metodo de envio
             **/

        } catch (\Exception $e) {

            echo $e->getMessage();
            echo $e->getTraceAsString();
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        /**
         * This is dependency to another patch. Dependency should be applied first
         * One patch can have few dependencies
         * Patches do not have versions, so if in old approach with Install/Ugrade data scripts you used
         * versions, right now you need to point from patch with higher version to patch with lower version
         * But please, note, that some of your patches can be independent and can be installed in any sequence
         * So use dependencies only if this important for you
         */
        return [

        ];
    }

    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        //Here should go code that will revert all operations from `apply` method
        //Please note, that some operations, like removing data from column, that is in role of foreign key reference
        //is dangerous, because it can trigger ON DELETE statement
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        /**
         * This internal Magento method, that means that some patches with time can change their names,
         * but changing name should not affect installation process, that's why if we will change name of the patch
         * we will add alias here
         */
        return [];
    }
}
