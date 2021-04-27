<?php declare(strict_types=1);

namespace Briqpay\Checkout\Helper;

class UserAgent
{
    /**
     * @var \Magento\Framework\App\ProductMetadata
     */
    private $productMetadata;

    /**
     * UserAgent constructor.
     */
    public function __construct(\Magento\Framework\App\ProductMetadata $productMetadata)
    {
        $this->productMetadata = $productMetadata;
    }

    /**
     * @return string
     */
    public function getHeader()
    {
        return "Magento2/{$this->productMetadata->getVersion()};{$this->getUrl()} - BPC : 1.0.0 - PHP Version: {$this->getPhpVersion()} - NWT";
    }

    /**
     * @return string
     */
    private function getUrl()
    {
        return (isset($_SERVER['REQUEST_SCHEME']) === 'https' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }

    /**
     * @return string
     */
    private function getPhpVersion()
    {
        return phpversion();
    }
}
