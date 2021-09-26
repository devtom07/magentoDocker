<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnext\RewardPoints\Model\Transaction\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class IsActive
 */
class Customer implements OptionSourceInterface
{
    /**
     * @var \Magento\Cms\Model\Block
     */
    protected $cmsBlock;
    /**
     * Customer Group
     *
     * @var \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    protected $_customerGroup;
    /**
     * Constructor
     *
     * @param \Magento\Cms\Model\Block $cmsBlock
     */
    public function __construct(
        \Magento\Cms\Model\Block $cmsBlock,
        \Magento\Customer\Model\ResourceModel\Customer\Collection $customerGroup
    )
    {
        $this->_customerGroup = $customerGroup;
        $this->cmsBlock = $cmsBlock;
    }
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $customerGroups = $this->_customerGroup->toOptionArray();
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
