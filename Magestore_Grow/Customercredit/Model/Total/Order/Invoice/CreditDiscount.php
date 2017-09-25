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
namespace Magestore\Customercredit\Model\Total\Order\Invoice;

/**
 * Giftvoucher Total Order Invoice Giftvoucher Model
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class CreditDiscount extends \Magento\Sales\Model\Order\Total\AbstractTotal
{
    /**
     * @var \Magestore\Customercredit\Helper\Data
     */
    protected $_creditHelper;

    /**
     * @param \Magestore\Customercredit\Helper\Data $creditHelper
     */
    public function __construct(
        \Magestore\Customercredit\Helper\Data $creditHelper
    ) {
        $this->_creditHelper = $creditHelper;
    }


    /**
     * Collect invoice giftvoucher
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $order = $invoice->getOrder();
        if ($order->getCustomercreditDiscount() < 0.0001) {
            return;
        }

        $invoice->setBaseCustomercreditDiscount(0);
        $invoice->setCustomercreditDiscount(0);

        $totalDiscountInvoiced = 0;
        $totalBaseDiscountInvoiced = 0;

        $totalDiscountAmount = 0;
        $totalBaseDiscountAmount = 0;

        $totalHiddenTax = 0;
        $totalBaseHiddenTax = 0;

        $hiddenTaxInvoiced = 0;
        $baseHiddenTaxInvoiced = 0;
        $checkAddShipping = true;

        foreach ($order->getInvoiceCollection() as $previousInvoice) {
            if ($previousInvoice->getCustomercreditDiscount()) {
                $checkAddShipping = false;
                $totalBaseDiscountInvoiced += $previousInvoice->getBaseCustomercreditDiscount();
                $totalDiscountInvoiced += $previousInvoice->getCustomercreditDiscount();

                $hiddenTaxInvoiced += $previousInvoice->getCustomercreditHiddenTax();
                $baseHiddenTaxInvoiced += $previousInvoice->getBaseCustomercreditHiddenTax();
            }
        }

        if ($checkAddShipping) {
            $totalBaseDiscountAmount += $order->getBaseCustomercreditDiscountForShipping();
            $totalDiscountAmount += $order->getCustomercreditDiscountForShipping();

            $totalBaseHiddenTax += $order->getBaseCustomercreditShippingHiddenTax();
            $totalHiddenTax += $order->getCustomercreditShippingHiddenTax();
        }

        if ($invoice->isLast()) {
            $totalBaseDiscountAmount = $order->getBaseCustomercreditDiscount() - $totalBaseDiscountInvoiced;
            $totalDiscountAmount = $order->getCustomercreditDiscount() - $totalDiscountInvoiced;

            $totalHiddenTax = $order->getCustomercreditHiddenTax() - $hiddenTaxInvoiced;
            $totalBaseHiddenTax = $order->getBaseCustomercreditHiddenTax() - $baseHiddenTaxInvoiced;
        } else {
            foreach ($invoice->getAllItems() as $item) {
                $orderItem = $item->getOrderItem();
                if ($orderItem->isDummy()) {
                    continue;
                }
                $baseOrderItemCustomercreditDiscount = (float)$orderItem->getBaseCustomercreditDiscount();
                $orderItemCustomercreditDiscount = (float)$orderItem->getCustomercreditDiscount();

                $baseOrderItemHiddenTax = (float)$orderItem->getBaseCustomercreditHiddenTax();
                $orderItemHiddenTax = (float)$orderItem->getCustomercreditHiddenTax();

                $orderItemQty = $orderItem->getQtyOrdered();
                $invoiceItemQty = $item->getQty();

                if ($baseOrderItemCustomercreditDiscount && $orderItemQty) {
                    $totalBaseDiscountAmount += $baseOrderItemCustomercreditDiscount / $orderItemQty * $invoiceItemQty;
                    $totalDiscountAmount += $orderItemCustomercreditDiscount / $orderItemQty * $invoiceItemQty;

                    $totalHiddenTax += $orderItemHiddenTax / $orderItemQty * $invoiceItemQty;
                    $totalBaseHiddenTax += $baseOrderItemHiddenTax / $orderItemQty * $invoiceItemQty;
                }
            }
        }

        $invoice->setBaseCustomercreditDiscount($totalBaseDiscountAmount);
        $invoice->setCustomercreditDiscount($totalDiscountAmount);

        $invoice->setBaseCustomercreditHiddenTax($totalBaseHiddenTax);
        $invoice->setCustomercreditHiddenTax($totalHiddenTax);

        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $totalBaseDiscountAmount + $totalBaseHiddenTax);
        $invoice->setGrandTotal($invoice->getGrandTotal() - $totalDiscountAmount + $totalHiddenTax);

        $address = $invoice->getShippingAddress();
        $baseShippingDiscount = 0;
        if ($this->_creditHelper->getSpendConfig('shipping')) {
            $order = $address->getOrder();
            $baseShippingDiscount = $order->getBaseShippingAmount() + $order->getBaseShippingTaxAmount() - $order->getBaseShippingDiscountAmount() - $order->getMagestoreBaseDiscountForShipping();
            $totalBaseDiscountAmount -= $baseShippingDiscount;
            $totalDiscountAmount -= $baseShippingDiscount;
        }

        if($this->_creditHelper->getSpendConfig('tax', $invoice->getStoreId()) == '0' ){
            if ($totalDiscountAmount != 0){
                foreach ($invoice->getAllItems() as $item) {
                    $orderItem = $item->getOrderItem();
                    $rate = $orderItem->getBasePrice() / $invoice->getBaseSubtotal();
                    $orderItem->setBaseDiscountAmount($orderItem->getBaseDiscountAmount() - round($rate * $totalBaseDiscountAmount, 2));
                    $orderItem->setDiscountAmount($orderItem->getDiscountAmount() - round($rate * $totalDiscountAmount, 2));
                }
            }
        }

        return $this;
    }

}
