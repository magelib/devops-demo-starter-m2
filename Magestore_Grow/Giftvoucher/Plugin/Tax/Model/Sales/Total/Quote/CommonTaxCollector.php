<?php

/**
 * Copyright © 2017 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Giftvoucher\Plugin\Tax\Model\Sales\Total\Quote;

use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * Class CommonTaxCollector
 * @package Magestore\Giftvoucher\Plugin\Tax\Model\Sales\Total\Quote
 */
class CommonTaxCollector
{
    /**
     * @param \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector $commonTaxCollector
     * @param \Closure $proceed
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $itemDataObjectFactory
     * @param AbstractItem $item
     * @param $priceIncludesTax
     * @param $useBaseCurrency
     * @param null $parentCode
     * @return mixed
     */
    public function aroundMapItem(
        \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector $commonTaxCollector,
        \Closure $proceed,
        \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $itemDataObjectFactory,
        AbstractItem $item,
        $priceIncludesTax,
        $useBaseCurrency,
        $parentCode = null
    ) {
        $result = $proceed($itemDataObjectFactory, $item, $priceIncludesTax, $useBaseCurrency, $parentCode);
        if ($useBaseCurrency) {
            $result->setDiscountAmount($item->getDiscountAmount() + $item->getBaseGiftVoucherDiscount());
        } else {
            $result->setDiscountAmount($item->getDiscountAmount()+$item->getGiftVoucherDiscount());
        }
        return $result;
    }
}
