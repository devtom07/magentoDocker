<?php

namespace Vnext\RewardPoints\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Vnext\RewardPoints\Model\EarningrateFactory;
use Vnext\RewardPoints\Model\SpendingrateFactory;

class InstallData implements InstallDataInterface
{
    protected $_postFactory;
    protected $_spent;

    public function __construct(EarningrateFactory $postFactory,SpendingrateFactory $_spent)
    {
        $this->_postFactory = $postFactory;
        $this->_spent = $_spent;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $data = [
            'website_id' => 1,
            'customer_group_id' => 1,
            'money_spent' => 100,
            'earing_points' => 1,
            'priority' => 0
        ];
        $dataSpent = [
            'website_id' => 1,
            'customer_group_id' => 1,
            'spending_point' => 3,
            'discount_reserved' => 10,
            'priority' => 0
        ];
        $post = $this->_postFactory->create();
        $spent = $this->_spent->create();
        $post->addData($data)->save();
        $spent->addData($dataSpent)->save();
    }
}
