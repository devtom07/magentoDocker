<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnext\RewardPoints\Controller\Adminhtml\Customerpoint;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Backend\App\Action\Context;
use Vnext\RewardPoints\Model\ResourceModel\Point as Resource;
use Vnext\RewardPoints\Model\PointFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Vnext\RewardPoints\Model\TransactionFactory;

/**
 * Save CMS block action.
 */
class Save extends \Vnext\RewardPoints\Controller\Adminhtml\Earningrate\Block implements HttpPostActionInterface
{
    /**
     * @var DataPersistorInterface
     */
    protected $_transactionFactory;
    protected $dataPersistor;

    /**
     * @var BlockFactory
     */
    private $customerpointFactory;

    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param DataPersistorInterface $dataPersistor
     * @param BlockFactory|null $blockFactory
     * @param BlockRepositoryInterface|null $blockRepository
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        DataPersistorInterface $dataPersistor,
        PointFactory $customerpointFactory,
        TransactionFactory $_transactionFactory,
        Resource $resource

    ) {
        $this->dataPersistor = $dataPersistor;
        $this->customerpointFactory = $customerpointFactory;
        $this->_transactionFactory = $_transactionFactory;
        $this->resource = $resource;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            if (empty($data['entity_id'])) {
                $data['entity_id'] = null;
            }

            /** @var \Magento\Cms\Model\Block $model */
            $model = $this->customerpointFactory->create();
            $transaction = $this->_transactionFactory->create();
            $id = $this->getRequest()->getParam('entity_id');
            //transaction get data
            $pointData = $model->load($id);
            $comment = $this->getRequest()->getParam('comment');
            $customerId = $pointData->getData('customer_id');
            $customerEmail = $pointData->getData('customer_email');
            $expirationDate = $this->getRequest()->getParam('expiration_date');
            $point = $this->getRequest()->getParam('point');

            if ($id) {
                try {
                    $this->resource->load($model, $id);
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage(__('This block no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
            }

            $model->setData($data);
            //transaction
            $transaction->setComment($comment);
            $transaction->setAmount($point);
            $transaction->setStatus('cancel');
            $transaction->setCustomerId($customerId);
            $transaction->setCustomerEmail($customerEmail);
            $transaction->setExpirationDate($expirationDate);
            $transaction->save();
            try {
                $this->resource->save($model);
                $this->messageManager->addSuccessMessage(__('You saved customer point rate .'));
                $this->dataPersistor->clear('students');
                // return $this->processBlockReturn($model, $data, $resultRedirect);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the block.'));
            }

            $this->dataPersistor->set('customer_point', $data);
            return $resultRedirect->setPath('*/*/edit', ['entity_id' => $id]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Process and set the block return
     *
     * @param \Magento\Cms\Model\Block $model
     * @param array $data
     * @param \Magento\Framework\Controller\ResultInterface $resultRedirect
     * @return \Magento\Framework\Controller\ResultInterface
     */
    private function processBlockReturn($model, $data, $resultRedirect)
    {
        $redirect = $data['back'] ?? 'close';

        if ($redirect ==='continue') {
            $resultRedirect->setPath('*/*/fix', ['entity_id' => $model->getId()]);
        } else if ($redirect === 'close') {
            $resultRedirect->setPath('*/*/');
        }
        return $resultRedirect;
    }


}
