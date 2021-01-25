<?php declare(strict_types=1);

namespace Briqpay\Checkout\Block;

class Messages extends \Magento\Framework\View\Element\Messages
{
    /**
     * @return \Magento\Framework\View\Element\Messages
     */
    protected function _prepareLayout()
    {
        $this->addMessages($this->messageManager->getMessages(true));

        return parent::_prepareLayout();
    }
}
