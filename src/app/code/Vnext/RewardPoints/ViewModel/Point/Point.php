<?php

namespace Vnext\RewardPoints\ViewModel\Point;

use Vnext\RewardPoints\Model\PointFactory;
use Vnext\RewardPoints\Model\TransactionFactory;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Point implements ArgumentInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    protected $point;
    protected $_transaction;

    public function __construct(\Magento\Framework\App\Request\Http               $request,
                                \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
                                \Magento\Customer\Model\SessionFactory            $customerSession,
                                PointFactory                                      $point,
                                TransactionFactory                                $_transaction
    )
    {
        $this->request = $request;
        $this->customerRepository = $customerRepository;
        $this->_customerSession = $customerSession;
        $this->point = $point;
        $this->_transaction = $_transaction;
    }

    public function getDataPoint()
    {
        $customer = $this->_customerSession->create();
        $customerId = $customer->getCustomer()->getId();
        $data = $this->point->create();
        $pointData = $data->load($customerId, 'customer_id');
        return $pointData;
    }

    public function getTransaction()
    {
        $customer = $this->_customerSession->create();
        $customerId = $customer->getCustomer()->getId();
        $transaction = $this->_transaction->create()->getCollection();
        foreach ($transaction as $transactions) {
            $customer_id = $transactions->getCustomerId();
            if ($customerId == $customer_id) {
                return $transaction;
            }
        }
    }


}