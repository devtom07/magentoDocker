<?php
namespace Vnext\RewardPoints\Controller\Customer;


use Magento\Framework\App\Action\Action;

class Index extends Action{
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }


}
