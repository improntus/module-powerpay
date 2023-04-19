<?php
namespace Improntus\PowerPay\Model;

use Improntus\PowerPay\Helper\Data as PowerPayHelper;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;

/**
 * Class ConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'powerpay';

    private $assetRepository;
    private $powerPayHelper;

    public function __construct
    (
        AssetRepository $assetRepository,
        PowerPayHelper $powerPayHelper
    )
    {
        $this->assetRepository = $assetRepository;
        $this->powerPayHelper = $powerPayHelper;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'active' => $this->powerPayHelper->isActive() && $this->powerPayHelper->validateCredentials(),
                    'order_create_url' => $this->powerPayHelper->getCreateUrl(),
                    'title' => $this->powerPayHelper->getTitle(),
                    'banner' => $this->assetRepository->getUrl("Improntus_PowerPay::images/PowerPay-Logo.png")
                ]
            ],
        ];
    }
}
