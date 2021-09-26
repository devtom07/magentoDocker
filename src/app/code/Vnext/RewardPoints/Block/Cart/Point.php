<?php

namespace Vnext\RewardPoints\Block\Cart;

class Point extends \Magento\Framework\View\Element\Template
{
    protected $customerSession;
    protected $pointFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Vnext\RewardPoints\Model\PointFactory $pointFactory
    )
    {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->pointFactory = $pointFactory;
    }
    public function getPoint()
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        $model_point = $this->pointFactory->create();
        $model_point->load($customerId,'customer_id');
        $array = $model_point->getData();
        if(count($array) == 0){
            $message = 'Not Found Spending Rate';
            return $message;
        }
        $point = $array['point'];
        return $array;
    }
    public function getCustomerSession()
    {
        return $this->customerSession;
    }
}
