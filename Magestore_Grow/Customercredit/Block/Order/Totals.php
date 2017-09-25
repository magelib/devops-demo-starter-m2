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

namespace Magestore\Customercredit\Block\Order;

class Totals extends \Magento\Sales\Block\Adminhtml\Totals
{
    public function initTotals()
    {
        parent::_initTotals();
        $orderTotalsBlock = $this->getParentBlock();
        $order = $orderTotalsBlock->getOrder();
        if ($order->getCustomercreditDiscount() > 0) {
            $orderTotalsBlock->addTotal(new \Magento\Framework\DataObject(array(
                'code' => 'customercredit',
                'label' => __('Customer Credit'),
                'value' => -$order->getCustomercreditDiscount(),
                'base_value' => -$order->getBaseCustomercreditDiscount(),
            )), 'subtotal');
        }
    }
}