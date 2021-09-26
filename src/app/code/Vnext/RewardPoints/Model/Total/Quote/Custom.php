<?php

namespace Vnext\RewardPoints\Model\Total\Quote;
/**
 * Class Custom
 * @package Mageplaza\HelloWorld\Model\Total\Quote
 */
class Custom extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;
    /**
     * @var\Vnext\RewardPoints\Controller\Ajax
     */
    protected $ajax;
    /**
     * Custom constructor.
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    protected $_collectionFactory;

    public function __construct(
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Vnext\RewardPoints\Model\ResourceModel\Moneypoint\CollectionFactory $collectionFactory
    )
    {
        $this->_collectionFactory = $collectionFactory;
        $this->_priceCurrency = $priceCurrency;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this|bool
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    )
    {
        parent::collect($quote, $shippingAssignment, $total);
        $result = $this->_collectionFactory->create();
        $result->addFieldToFilter('quote_id', $quote->getEntityId());
        $result->getSelect()->order('create_at' , \Magento\Framework\DB\Select::SQL_DESC);
        $array = $result->getData();
        $money = end($array)['money'];
        if($money == null) {
            $money = 0;
        }
        $baseDiscount = $money;
        $discount = $this->_priceCurrency->convert($baseDiscount);
        $total->addTotalAmount('custom_discount', -$discount);
        $total->addBaseTotalAmount('custom_discount', -$baseDiscount);
        $total->setBaseGrandTotal($total->getBaseGrandTotal() - $baseDiscount);
        $quote->setCustomDiscount(-$discount);
        return $this;
    }
}
