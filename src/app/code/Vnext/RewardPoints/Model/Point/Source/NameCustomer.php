<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnext\RewardPoints\Model\Point\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class IsActive
 */
class NameCustomer implements OptionSourceInterface
{
    /**
     * @var \Magento\Cms\Model\Block
     */
    protected $cmsBlock;
    /**
     * Customer Group
     *
     * @var \Magento\Customer\Model\ResourceModel\Group\Collection
     */
    protected $customerFactory;
    /**
     * Constructor
     *
     * @param \Magento\Cms\Model\Block $cmsBlock
     */
    public function __construct(
        \Magento\Cms\Model\Block $cmsBlock,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    )
    {
        $this->customerFactory = $customerFactory;
        $this->cmsBlock = $cmsBlock;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $customerGroups = $this->customerFactory->toOptionArray();
        $options = [];
        foreach ($customerGroups as $key => $value) {
            $options[] = [
                'label' => $value['label'],
                'value' => $value['value'],
            ];
        }
        array_shift($options);
        return $options;
    }
}
