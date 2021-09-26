<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnext\RewardPoints\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;

/**
 * Class to build edit and delete link for each item.
 */
class CustomerpointActions extends Column
{
    /**
     * Url path
     */
    const URL_PATH_EDIT = 'point/customerpoint/edit';
    const URL_PATH_DELETE = 'point/customerpoint/delete';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        Escaper $escaper,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->escaper = $escaper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['entity_id'])) {
                    $customerPoint = $this->escaper->escapeHtml($item['entity_id']);
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    'entity_id' => $item['entity_id'],
                                ]
                            ),
                            'label' => __('Update'),
                            '__disableTmpl' => true,
                        ],
//                        'delete' => [
//                            'href' => $this->urlBuilder->getUrl(
//                                static::URL_PATH_DELETE,
//                                [
//                                    'entity_id' => $item['entity_id'],
//                                ]
//                            ),
//                            'label' => __('Delete'),
//                            'confirm' => [
//                                'title' => __('Delete %1', $customerPoint),
//                                'message' => __('Are you sure you want to delete a %1 record?', $customerPoint),
//                            ],
//                            'post' => true,
//                            '__disableTmpl' => true,
//                        ],
                    ];
                }
            }
        }

        return $dataSource;
    }

    /**
     * Get instance of escaper
     *
     * @return Escaper
     * @deprecated 101.0.7
     */
    private function getEscaper()
    {
        if (!$this->escaper) {
            $this->escaper = ObjectManager::getInstance()->get(Escaper::class);
        }
        return $this->escaper;
    }
}
