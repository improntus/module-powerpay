<?php


namespace Improntus\PowerPay\Block\Adminhtml\System\Config;


use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use Improntus\PowerPay\Helper\Data as PowerPayHelper;


class CredentialsStatus extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * @var PowerPayHelper
     */
    private $powerPayHelper;
    public function __construct(
        Context $context,
        PowerPayHelper $powerPayHelper,
        array $data = []
    )
    {
        $this->powerPayHelper = $powerPayHelper;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
         if ($this->powerPayHelper->validateCredentials() === $this->powerPayHelper::USER_AUTHENTICATED) {
             $status = 'success';
             $label = __("Credentials are filled. But couldn't be verified");
         } else {
                $status = 'warning';
                $label = __('Credentials section is incomplete. Please, fill the section and try again.');

        }

        return sprintf('<div class="control-value"><span class="%s">%s</span></div>', $status, $label);
    }

    /**
     * @param AbstractElement $element
     * @param string $html
     *
     * @return string
     */
    protected function _decorateRowHtml(AbstractElement $element, $html)
    {
        return '<tr id="row_' . $element->getHtmlId() . '" class="row_payment_other_modo_validation_credentials">' . $html . '</tr>';
    }
}
