<?php declare(strict_types=1);

namespace Briqpay\Checkout\Block\Adminhtml\Payment\Checkout;


class Info extends \Magento\Payment\Block\Info
{
    /**
     * @var string
     */
    protected $_template = 'Briqpay_Checkout::payment/checkout/info.phtml';

    /**
     * @return string
     */
    public function toPdf()
    {
        $this->setTemplate('Briqpay_Checkout::payment/checkout/pdf.phtml');
        return $this->toHtml();
    }

    /**
     * @return mixed|string
     */
    public function getBriqpayPaymentMethod()
    {
        try {
            return $this->getInfo()->getAdditionalInformation('briqpay_method');
        } catch (\Exception $e) {
            return "";
        }
    }

    /**
     * @return mixed|string
     */
    public function getBriqpayPurchaseId()
    {
        try {
            return $this->getInfo()->getAdditionalInformation('briqpay_purchase_id');
        } catch (\Exception $e) {
            return "";
        }
    }
}
