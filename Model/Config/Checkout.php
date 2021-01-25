<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Config;

use Magento\Framework\App\Helper\AbstractHelper;

class Checkout extends AbstractHelper
{
    /**
     *
     */
    private const PATH_CHECKOUT_URI = 'briqpay';

    /**
     * @param array $params
     *
     * @return string
     */
    public function getCheckoutUrl($params = [])
    {
        return $this->_getUrl(self::PATH_CHECKOUT_URI, $params);
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function getSaveCouponUrl($params = [])
    {
        return $this->_getUrl(self::PATH_CHECKOUT_URI . '/update/SaveCoupon', $params);
    }

    /**
     * @return bool
     */
    public function isRegisterOnCheckout()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getCommentSubmitUrl()
    {
        return $this->_getUrl(self::PATH_CHECKOUT_URI . '/update/SaveComment');
    }
}
