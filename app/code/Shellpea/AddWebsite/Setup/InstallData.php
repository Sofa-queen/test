<?php

namespace Shellpea\AddWebsite\Setup;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\ResourceModel\Group;
use Magento\Store\Model\ResourceModel\Store;
use Magento\Store\Model\ResourceModel\Website;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\WebsiteFactory;

class InstallData implements InstallDataInterface
{
    /**
     * @var ManagerInterface
     */
    private $eventManager;
    /**
     * @var GroupFactory
     */
    private $groupFactory;
    /**
     * @var Group
     */
    private $groupResourceModel;
    /**
     * @var StoreFactory
     */
    private $storeFactory;
    /**
     * @var Store
     */
    private $storeResourceModel;
    /**
     * @var WebsiteFactory
     */
    private $websiteFactory;
    /**
     * @var Website
     */
    private $websiteResourceModel;

    /**
     * InstallData constructor.
     * @param WebsiteFactory $websiteFactory
     * @param Website $websiteResourceModel
     * @param Store $storeResourceModel
     * @param Group $groupResourceModel
     * @param StoreFactory $storeFactory
     * @param GroupFactory $groupFactory
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        Group $groupResourceModel,
        GroupFactory $groupFactory,
        ManagerInterface $eventManager,
        Store $storeResourceModel,
        StoreFactory $storeFactory,
        Website $websiteResourceModel,
        WebsiteFactory $websiteFactory
    ) {
        $this->eventManager = $eventManager;
        $this->groupFactory = $groupFactory;
        $this->groupResourceModel = $groupResourceModel;
        $this->storeFactory = $storeFactory;
        $this->storeResourceModel = $storeResourceModel;
        $this->websiteFactory = $websiteFactory;
        $this->websiteResourceModel = $websiteResourceModel;
    }

    /**
     * @param  ModuleDataSetupInterface $setup
     * @param  ModuleContextInterface $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var \Magento\Store\Model\Website $website */
        $website = $this->websiteFactory->create();
        $website->load('my_custom_code');
        if(!$website->getId()){
            $website->setCode('nonsense');
            $website->setName('outrage');
            $website->setDefaultGroupId(3);
            $this->websiteResourceModel->save($website);

        }

        if($website->getId()){
            /** @var \Magento\Store\Model\Group $group */
            $group = $this->groupFactory->create();
            $group->setWebsiteId($website->getWebsiteId());
            $group->setName('My outrage');
            $group->setRootCategoryId(2);
            $group->setDefaultStoreId(3);
            $this->groupResourceModel->save($group);
        }

        /** @var  \Magento\Store\Model\Store $store */
        $store = $this->storeFactory->create();
        $store->load('store_outrage');
        if(!$store->getId()){
            $group = $this->groupFactory->create();
            $group->load('My outrage', 'name');
            $store->setCode('my_custom_outrage');
            $store->setName('My Custom outrage');
            $store->setWebsite($website);
            $store->setGroupId($group->getId());
            $store->setData('is_active','1');
            $this->storeResourceModel->save($store);
            // Trigger event to insert some data to the sales_sequence_meta table (fix bug place order in checkout)
            $this->eventManager->dispatch('store_add', ['store' => $store]);
        }
    }

}
