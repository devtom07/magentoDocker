<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnext\RewardPoints\Controller\Cart;


use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PointPost extends \Magento\Framework\App\Action\Action
{
    protected $json;
    protected $resultJsonFactory;
    protected $customerSession;
    protected $_collectionFactory;
    protected $_collection;
    private $checkoutSession;
    protected $_priceCurrency;
    protected $moneypoint;
    protected $moneypointresource;
    protected $_cacheTypeList;
    protected $pointFactory;
    protected $messageManager;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Vnext\RewardPoints\Model\ResourceModel\Spendingrate\CollectionFactory $collectionFactory,
        CheckoutSession $checkoutSession,
        \Vnext\RewardPoints\Model\MoneypointFactory $moneypoint,
        \Vnext\RewardPoints\Model\ResourceModel\Moneypoint $moneypointresource,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Vnext\RewardPoints\Model\PointFactory $pointFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager

    )
    {
        $this->messageManager = $messageManager;
        $this->pointFactory = $pointFactory;
        $this->_cacheTypeList = $cacheTypeList;
        $this->moneypointresource = $moneypointresource;
        $this->moneypoint = $moneypoint;
        $this->checkoutSession = $checkoutSession;
        $this->_collectionFactory = $collectionFactory;
        $this->customerSession = $customerSession;
        $this->json = $json;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $number_points = $this->getRequest()->getParam('number_points');
        $id = $this->customerSession->getCustomer()->getId();
        $id_group = $this->customerSession->getCustomer()->getGroupId();
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->_redirect->getRefererUrl());

        if (!$this->_collection) {
            $result = $this->_collectionFactory->create();
            $result->addFieldToFilter('customer_group_id', $id_group);
            $result->getSelect()->order('priority' . ' ' . \Magento\Framework\DB\Select::SQL_DESC);
        }

        $array = $result->getData();
        if(count($array) == 0){
            $this->messageManager->addErrorMessage(__('Not Found Spending Rate'));
            return $resultRedirect;
        }
        $discount_reserved = $array[0]['discount_reserved'];
        $spending_point = $array[0]['spending_point'];
        $point = $number_points;
        //
        $id_customer = $this->customerSession->getCustomer()->getId();
        $model = $this->pointFactory->create();
        $quantity_point = $model->load($id_customer,'customer_id')->getPoint();
        if ($quantity_point<$point){
            $this->messageManager->addErrorMessage(__('Your bonus points are not enough'));
            return $resultRedirect;
        }
        //
        $rewardpoints = round(($point * $discount_reserved) / $spending_point);
        try {
            $quote = $this->checkoutSession->getQuote();
        } catch (NoSuchEntityException $e) {
        } catch (LocalizedException $e) {
        }
        $total = $quote->getBaseGrandTotal();
        $discount = $rewardpoints/(int)$total;
        if($discount>0.2){
            $this->messageManager->addErrorMessage(__('Your discount is too much for your order.Please enter less points'));
            return $resultRedirect;
        }
        $quote_id = $this->checkoutSession->getQuoteId();
        $money_quote = $this->moneypoint->create();
        $money_quote->setData('quote_id', $quote_id);
        $money_quote->setData('money', $rewardpoints);
        $money_quote->setData('point', $point);
        try {
            $this->moneypointresource->save($money_quote);
        } catch (AlreadyExistsException $e) {
        }

        $this->messageManager->addSuccessMessage(__('You appyle the point success.'));
        return $resultRedirect;
    }
}

