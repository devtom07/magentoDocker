<?php
namespace Vnext\RewardPoints\Controller\Adminhtml\Spendingrate;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Vnext_RewardPoints::spendingrate_list');
        $resultPage->getConfig()->getTitle()->prepend(__('Spending Rate List'));
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Vnext_RewardPoints::spendingrate_list');
    }
}
