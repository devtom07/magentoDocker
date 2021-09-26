<?php
namespace Vnext\RewardPoints\Model\ResourceModel;

use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Model\AbstractModel;

class Transaction extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    ) {

        parent::__construct($context);
    }

    protected function _construct() {
        $this->_init('transactions', 'entity_id');
    }


}
