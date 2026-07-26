<?php
/**
 * @author Improntus Dev Team
 * @copyright Copyright (c) 2026 Improntus (https://www.improntus.com/)
 */

namespace Improntus\PowerPay\Model\Config\Source;

use Improntus\PowerPay\Helper\Data;
use Magento\Framework\Data\OptionSourceInterface;

class ProductWidget implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => Data::PRODUCT_WIDGET_DISABLED, 'label' => __('No')],
            ['value' => Data::PRODUCT_WIDGET_DEFAULT, 'label' => __('Default (Powerpay)')],
            ['value' => Data::PRODUCT_WIDGET_NEUTRAL, 'label' => __('Neutral')],
        ];
    }
}
