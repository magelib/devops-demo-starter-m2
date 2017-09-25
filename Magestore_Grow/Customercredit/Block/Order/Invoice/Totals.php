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

namespace Magestore\Customercredit\Block\Order\Invoice;

class Totals extends \Magento\Sales\Block\Adminhtml\Totals
{
    public function initTotals()
    {
        parent::_initTotals();
        $invoiceTotalsBlock = $this->getParentBlock();
        $invoice = $invoiceTotalsBlock->getInvoice();
        if ($invoice->getCustomercreditDiscount() > 0) {
            $invoiceTotalsBlock->addTotal(new \Magento\Framework\DataObject(array(
                'code' => 'customercredit',
                'label' => __('Customer Credit'),
                'value' => -$invoice->getCustomercreditDiscount(),
                'base_value' => -$invoice->getBaseCustomercreditDiscount(),
            )), 'subtotal');
        }
    }

}