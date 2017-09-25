<?php
/**
 * Copyright © 2017 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Giftvoucher\Model\Total\Quote;

/**
 * Giftvoucher Total Quote Giftvoucher Model
 *
 * @category    Magestore
 * @package     Magestore_Giftvoucher
 * @author      Magestore Developer
 */
class Giftvoucher extends \Magestore\Giftvoucher\Model\Total\Quote\GiftvoucherAbstract
{
    /**
     * @var string
     */
    protected $_code = 'giftvoucher';

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return bool
     */
    public function isActive(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $applyGiftAfterTax = $this->calculationService->isApplyGiftAfterTax($quote->getStoreId());
        return ($applyGiftAfterTax)?false:true;
    }
}
