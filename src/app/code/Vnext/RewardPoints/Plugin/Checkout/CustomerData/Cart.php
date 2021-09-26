<?php

namespace Vnext\RewardPoints\Plugin\Checkout\CustomerData;

use Vnext\RewardPoints\Model\ResourceModel\Earningrate\CollectionFactory;

class Cart
{
    protected $earning;
    public function __construct(CollectionFactory $earning)
    {
        $this->earning = $earning;
    }
    public function afterGetSectionData(\Magento\Checkout\CustomerData\Cart $subject, array $result)
    {
        $point = $this->earning->create();
        $total = $result['subtotalAmount'];
        $pointData = $point->getData();
        if($pointData != null){
            foreach ($point as $data){
                $money_spent = $data->getMoneySpent();
                $earning_point = $data->getEaringPoints();
            }
            $earning = round($total / $money_spent * $earning_point);
            $result['extra_data'] = $earning;
        }else{
            $result['extra_data'] = "Bạn chưa thể tính point cho đơn hàng này";
        }
        return $result;
    }
}