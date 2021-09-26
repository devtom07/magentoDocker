<?php

namespace Vnext\RewardPoints\Observer\Checkout;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Vnext\RewardPoints\Model\PointFactory;
use Vnext\RewardPoints\Model\TransactionFactory;
use Vnext\RewardPoints\Model\ResourceModel\Earningrate\CollectionFactory;


class CustomPoint implements ObserverInterface
{
    protected $earning;
    protected $logger;
    protected $_pointFactory;
    protected $checkoutSession;
    protected $_collectionFactory;
    protected $storeManager;
    protected $studentCollection;
    protected $customerSession;
    protected $pointFactory;
    protected $_transaction;

    public function __construct(
        LoggerInterface                                                      $logger,
        CollectionFactory                                                    $earning,
        CheckoutSession                                                      $checkoutSession,
        PointFactory                                                         $_pointFactory,
        \Vnext\RewardPoints\Model\ResourceModel\Moneypoint\CollectionFactory $collectionFactory,
        \Magento\Framework\Mail\Template\TransportBuilder                    $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface                           $storeManager,
        \Magento\Customer\Model\Session                                      $customerSession,
        \Vnext\RewardPoints\Model\PointFactory                               $pointFactory,
        \Magento\Quote\Model\Quote                                           $quote,
        TransactionFactory                                                   $_transaction
    )
    {
        $this->_collectionFactory = $collectionFactory;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
        $this->earning = $earning;
        $this->_pointFactory = $_pointFactory;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->pointFactory = $pointFactory;
        $this->quote = $quote;
        $this->_transaction = $_transaction;
    }

    public function getPoint()
    {
        $quote = $this->checkoutSession->getQuoteId();
        $result = $this->_collectionFactory->create();
        $result->addFieldToFilter('quote_id', $quote);
        $result->getSelect()->order('create_at', \Magento\Framework\DB\Select::SQL_DESC);
        $array = $result->getData();
        if (count($array) == 0) {
            $point = 0;
        } else {
            $point = end($array)['point'];
        }
        return $point;
    }

    public function getPointCustomer()
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        $model_point = $this->pointFactory->create();
        $model_point->load($customerId, 'customer_id');
        $array = $model_point->getData();
        $point = $array['point'];
        return $point;
    }

    public function getPointSpent()
    {
        $quoteId = $this->quote->getId();
        $result = $this->_collectionFactory->create();
        $result->addFieldToFilter('quote_id', $quoteId);
        $result->getSelect()->order('create_at', \Magento\Framework\DB\Select::SQL_DESC);
        $array = $result->getData();
        $point = end($array)['point'];
        if ($point == null) {
            $point = 0;
        }
        return $point;
    }

    public function sendtoEmail()
    {
        try {
            $point = $this->getPointCustomer();
            $pointSpent = 1;
            $receiverInfo = [
                'name' => $this->customerSession->getCustomer()->getName(),
                'email' => $this->customerSession->getCustomer()->getEmail(),
                'point' => $point,
                'spent' => $pointSpent,
            ];

            $store = $this->storeManager->getStore();
            $templateParams = ['store' => $store, 'administrator_name' => $receiverInfo['name'], 'point' => $receiverInfo['point'], 'spent' => $receiverInfo['spent']];
            $transport = $this->transportBuilder->setTemplateIdentifier(
                'point_email_tempalte'
            )->setTemplateOptions(
                ['area' => 'frontend', 'store' => $store->getId()]
            )->addTo(
                $receiverInfo['email'], $receiverInfo['name']
            )->setTemplateVars(
                $templateParams
            )->setFrom(
                'general'
            )->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

    }

    public function execute(Observer $observer)
    {
        $expirationDate = mktime(0, 0, 0, date("m"), date("d") + 30, date("Y"));
        $transactionEarning = $this->_transaction->create();
        $transactionSpent = $this->_transaction->create();
        $pointFactory = $this->_pointFactory->create();
        $order = $observer->getEvent()->getOrder();
        $point = $this->getPoint();
        $customerId = $order->getCustomerId();
        $customEmail = $order->getCustomerEmail();
        $orderNumber = $order->getIncrementId();
        $total = $order->getSubtotal();
        $earning_point = $this->earning->create();
        $checkpoint = $earning_point->getData();
        if ($customerId != null) {
            if ($checkpoint != null) {
                foreach ($earning_point as $data) {
                    $money_spent = $data->getMoneySpent();
                    $earningPoint = $data->getEaringPoints();
                    $earning = round($total / $money_spent * $earningPoint);
                }
            } else {
                $earning = 0;
            }
            $customPointId = $pointFactory->load($customerId, 'customer_id')->getCustomerId();
            if (isset($customPointId)) {
                //point
                $customerPoint = $pointFactory->load($customerId, 'customer_id')->getPoint();
                $customerPointSpent = $pointFactory->load($customerId, 'customer_id')->getData('point_spent');
                $point_update = $customerPoint + $earning - $point;
                $pointFactory->load($customerId, 'customer_id');
                $pointFactory->setPoint($point_update);
                $pointFactory->setPointSpent($customerPointSpent + $point);
                $pointFactory->setPointEarning($customerPoint + $earning);
                $pointFactory->setExpirationDate(date("d/m/Y", $expirationDate));
                $pointFactory->save();
                $this->sendtoEmail();
                //transactions
                if ($earning > 0) {
                    $transactionEarning->setComment("Earn points for purchasing order" . " " . $orderNumber);
                    $transactionEarning->setAmount("+" . $earning);
                    $transactionEarning->setStatus('Completed');
                    $transactionEarning->setCustomerId($customerId);
                    $transactionEarning->setCustomerEmail($customEmail);
                    $transactionEarning->setExpirationDate($expirationDate);
                    $transactionEarning->save();
                }
                if ($point > 0) {
                    $transactionSpent->setComment("Spent points for purchasing order" . " " . $orderNumber);
                    $transactionSpent->setAmount("-" . $point);
                    $transactionSpent->setStatus('Completed');
                    $transactionSpent->setCustomerId($customerId);
                    $transactionSpent->setCustomerEmail($customEmail);
                    $transactionSpent->setExpirationDate($expirationDate);
                    $transactionSpent->save();
                }
            } else {
                $pointFactory->setPoint($earning);
                $pointFactory->setCustomerId($customerId);
                $pointFactory->setCustomerEmail($customEmail);
                $pointFactory->setPointSpent("0");
                $pointFactory->setPointEarning($earning);
                $pointFactory->setExpirationDate($expirationDate);
                $pointFactory->save();
                $this->sendtoEmail();
            }
        }
    }

}
