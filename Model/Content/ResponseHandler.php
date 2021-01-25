<?php

namespace Briqpay\Checkout\Model\Content;

/** @var $this \Magento\Checkout\Controller\Action */

trait ResponseHandler
{
    /**
     * @param null $blocks
     * @param bool $updateCheckout
     */
    protected function handleResponse($blocks = [], $updateCheckout = true)
    {
        $response = [];
        // Reload the blocks even we have an error
        if (is_null($blocks)) {
            $blocks = ['shipping_method','cart','coupon','messages', 'briqpay','newsletter'];
        } elseif ($blocks) {
            $blocks = (array)$blocks;
        } else {
            $blocks = [];
        }

        if (!in_array('messages', $blocks)) {
            $blocks[] = 'messages';
        }

        if ($updateCheckout) {  //if blocks contains only "messages" do not update
            if (!empty($response['redirect'])) {
                if ($this->getRequest()->isXmlHttpRequest()) {
                    $response['redirect'] = $this->storeManager->getStore()->getUrl($response['redirect']);
                    $this->getResponse()->setBody(json_encode($response));
                } else {
                    $this->_redirect($response['redirect']);
                }
                return;
            }
        }

        $response['ok'] = true;

        if (!$this->getRequest()->isXmlHttpRequest()) {
            $this->_redirect('*');
            return;
        }

        $response['ok'] = true;
        if ($blocks) {
            $this->_view->loadLayout('briqpay_checkout_order_update');
            foreach ($blocks as $id) {
                $name = "briqpay_checkout.{$id}";
                $block = $this->_view->getLayout()->getBlock($name);
                if ($block) {
                    $response['updates'][$id] = $block->toHtml();
                }
            }
        }

        $this->getResponse()->setBody(json_encode($response));
    }

    /**
     * Validate ajax request and redirect on failure
     *
     * @return bool
     */
    protected function ajaxRequestAllowed()
    {
        return $this->getRequest()->isXmlHttpRequest();
    }
}
