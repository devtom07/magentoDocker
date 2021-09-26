<?php
namespace Vnext\RewardPoints\Cron;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;

class SendEmailExpirationPoints
{
    /** @var  Magento\Store\Model\StoreManagerInterface */
    protected $storeManager;
    /** @var  Magento\Framework\Mail\Template\TransportBuilder */
    protected $transportBuilder;
    /** @var  \Psr\Log\LoggerInterface */
    protected $logger;
    /** @var  \Vnext\RewardPoints\Model\PointFactory */
    protected $pointFactory;

    protected $pointCollection;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger,
        \Vnext\RewardPoints\Model\PointFactory $pointFactory,
        \Vnext\RewardPoints\Model\ResourceModel\Point\CollectionFactory $pointCollection
    )
    {
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->pointFactory = $pointFactory;
        $this->pointCollection = $pointCollection;
    }
    public function execute()
    {
        $pointFactory = $this->pointCollection->create();
        foreach ($pointFactory as $point)
        {
            $point->setData('point',0);
            $this->sendEmail('Customer',$point->getData('customer_email'));
        }
        $pointFactory->save();
    }
    public function sendEmail($customerName,$customerEmail)
    {
        $receiverInfo = [
            'name' => $customerName,
            'email' => $customerEmail,
        ];
        $store = $this->storeManager->getStore();
        $templateParams = ['store' => $store, 'administrator_name' => $receiverInfo['name']];
        $transport = $this->transportBuilder->setTemplateIdentifier(
            'expiration_point_email_tempalte'
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
    }
}
