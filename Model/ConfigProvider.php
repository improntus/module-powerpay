<?php
namespace Improntus\PowerPay\Model;

use Improntus\PowerPay\Helper\Data as PowerPayHelper;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Store\Model\StoreManagerInterface;
use MagePal\GmailSmtpApp\Model\Store;

/**
 * Class ConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'powerpay';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var AssetRepository
     *
     */
    private $assetRepository;

    /**
     * @var PowerPayHelper
     */
    private $powerPayHelper;

    public function __construct
    (
        AssetRepository $assetRepository,
        PowerPayHelper $powerPayHelper,
        StoreManagerInterface $storeManager
    )
    {
        $this->storeManager = $storeManager;
        $this->assetRepository = $assetRepository;
        $this->powerPayHelper = $powerPayHelper;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getConfig()
    {
        $storeId = $this->storeManager->getStore()->getId();
        return [
            'payment' => [
                self::CODE => [
                    'active' => $this->powerPayHelper->isActive() && $this->powerPayHelper->validateCredentials(),
                    'redirect_url' => $this->powerPayHelper->getRedirectUrl(),
                    'title' => $this->powerPayHelper->getTitle(),
                    'banner' => $this->assetRepository->getUrl("Improntus_PowerPay::images/PowerPay-Logo.svg"),
                    'widgets_active' => $this->powerPayHelper->getWidgetsEnabled($storeId),
                    'checkout_widget' => $this->powerPayHelper->getCheckoutWidgetEnabled($storeId),
                    'client_id' => $this->powerPayHelper->getClientId($storeId)
                ]
            ],
        ];
    }
}
