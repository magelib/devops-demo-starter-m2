<?php
/**
 * Copyright © 2017 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Giftvoucher\Block\Order;

/**
 * Giftvoucher Order Totals Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Totals extends \Magento\Sales\Block\Order\Totals
{

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_dataObject;

    /**
     * Totals constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\DataObject $dataObject
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\DataObject $dataObject,
        array $data = []
    ) {
        $this->_dataObject = $dataObject;
        parent::__construct($context, $registry, $data);
    }

    public function initTotals()
    {
        $orderTotalsBlock = $this->getParentBlock();
        $order = $orderTotalsBlock->getOrder();
        if ($order->getGiftVoucherDiscount() && $order->getGiftVoucherDiscount() > 0) {
            $orderTotalsBlock->addTotal(new \Magento\Framework\DataObject(
                [
                    'code' => 'giftvoucher',
                    'label' => __('Gift Card (%1)', $order->getGiftVoucherGiftCodes()),
                    'value' => -$order->getGiftVoucherDiscount(),
                    'base_value' => -$order->getBaseGiftVoucherDiscount(),
                ]
            ), 'subtotal');
        }
        $refund = $this->getGiftCardRefund($order);
        if (($refund >0 || $refund ===0.0 ) && $order->getGiftVoucherDiscount()) {
                $refundAmount = $refund;
            $label = __('Refund to your gift card code');
            $dataObject = $this->_dataObject->addData(
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
