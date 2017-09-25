<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Customercredit
 * @copyright   Copyright (c) 2017 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 *
 */

namespace Magestore\Customercredit\Plugin;
/**
 * Class UpdateDiscountForOrder
 * @package Magestore\Rewardpoints\Model\Plugin
 */
class UpdateDiscountForOrder
{
    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;
    /**
     *
     */
    const AMOUNT_Payment = 'payment_fee';
    /**
     *
     */
    const AMOUNT_SUBTOTAL = 'subtotal';

    /**
     * UpdateDiscountForOrder constructor.
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Quote\Model\Quote $quote,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Registry $registry
    ) {
        $this->quote = $quote;
        $this->logger = $logger;
        $this->_checkoutSession = $checkoutSession;
        $this->_registry = $registry;
    }

    /**
     * @param $cart
     * @param $result
     * @return mixed
     */
    public function afterGetAmounts($cart, $result)
    {
        $total = $result;
        $quote = $this->_checkoutSession->getQuote();
        $paymentMethod = $quote->getPayment()->getMethod();
        $paypalMehodList = ['payflowpro','payflow_link','payflow_advanced','braintree_paypal','paypal_express_bml','payflow_express_bml','payflow_express','paypal_express'];

        if(in_array($paymentMethod,$paypalMehodList)){
            $total[self::AMOUNT_SUBTOTAL] = $total[self::AMOUNT_SUBTOTAL] - $quote->getCreditdiscountAmount();
        }

        return $total;
    }

    /**
     * @param $cart
     */
    public function beforeGetAllItems($cart)
    {
        $quote = $this->_checkoutSession->getQuote();
        $paymentMethod = $quote->getPayment()->getMethod();

        if(!$this->_checkoutSession->getData('addedcustomitempayment')) {
            $paypalMehodList = ['payflowpro','payflow_link','payflow_advanced','braintree_paypal','paypal_express_bml','payflow_express_bml','payflow_express','paypal_express'];
            if($quote->getCreditdiscountAmount() && ($paymentMethod == null || in_array($paymentMethod,$paypalMehodList))) {
                if(method_exists($cart , 'addCustomItem' ))
                {
                    $cart->addCustomItem(__("Customer Credit"), 1 ,  -1.00 * $quote->getCreditdiscountAmount());
                    $this->_checkoutSession->setData('addedcustomitempayment', true);
                }
            }
        }
    }

}