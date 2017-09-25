<?php
/**
 * Copyright © 2017 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Giftvoucher\Block\Adminhtml\Order;

/**
 * Adminhtml Giftvoucher Order Totals Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Totals extends \Magento\Sales\Block\Adminhtml\Totals
{

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_dataObject;

    /**
     * Credit constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param \Magento\Framework\DataObject $dataObject
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        \Magento\Framework\DataObject $dataObject,
        array $data = []
    ) {
        $this->_dataObject = $dataObject;
        parent::__construct($context, $registry, $adminHelper, $data);
    }

    public function initTotals()
    {
        $orderTotalsBlock = $this->getParentBlock();
        $order = $orderTotalsBlock->getOrder();
        if ($order->getGiftVoucherDiscount() && $order->getGiftVoucherDiscount() > 0) {
            $giftVoucherDiscount = $order->getGiftVoucherDiscount();
            $baseCurrency = $this->_storeManager->getStore($order->getStoreId())->getBaseCurrency();
            if ($rate = $baseCurrency->getRate($order->getOrderCurrencyCode())) {
                $giftVoucherDiscount = $giftVoucherDiscount * $rate;
            }
            $orderTotalsBlock->addTotal(new \Magento\Framework\DataObject(
                [
                    'code' => 'giftvoucher',
                    'label' => __('Gift Card (%1)', $order->getGiftVoucherGiftCodes()),
                    'value' => -$giftVoucherDiscount,
                    'base_value' => -$order->getBaseGiftVoucherDiscount(),
                ]
            ), 'subtotal');
        }
        $refund = $this->getGiftCardRefund($order);
        if (($refund >0 || $refund===0.0) && ($order->getUseGiftCreditAmount() || $order->getGiftVoucherDiscount())) {
            $baseCurrency = $this->_storeManager->getStore($order->getStoreId())->getBaseCurrency();
            if ($rate = $baseCurrency->getRate($order->getOrderCurrencyCode())) {
                $refundAmount = $refund / $rate;
            }
//            if ($order->getCustomerIsGuest()) {
                $label = __('Refund to customer gift card code used to check out');
//            }
            $dataObject = $this->_dataObject->setData(
                [
                    'code' => 'giftcard_refund',
                    'label' => $label,
                    'value' => $refund,
                    'base_value' => $refundAmount,
                    'area' => 'footer',
                ]
            );
            $orderTotalsBlock->addTotal($dataObject, 'subtotal');
        }
    }

    /**
     * Get Gift Card refunded amount
     *
     * @param \Magento\Sales\Model\Order $order
     * @return float
     */
    public function getGiftCardRefund($order)
    {
        $refund = 0;
        foreach ($order->getCreditmemosCollection() as $creditmemo) {
            $refund += $creditmemo->getGiftcardRefundAmount();
        }
        return $refund;
    }
}
