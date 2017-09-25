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
class GiftvoucherAbstract extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var \Magestore\Giftvoucher\Service\Redeem\CalculationService
     */
    protected $calculationService;

    /**
     * Giftvoucher constructor.
     * @param \Magestore\Giftvoucher\Service\Redeem\CalculationService $calculationService
     */
    public function __construct(
        \Magestore\Giftvoucher\Service\Redeem\CalculationService $calculationService
    ) {
        $this->calculationService = $calculationService;
    }

    /**
     * Collect totals process.
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        if ($this->isActive($quote, $total)) {
            $this->calculationService->collect($quote, $shippingAssignment, $total, $this->getCode());
        }
        return $this;
    }

    /**
     * Fetch (Retrieve data as array)
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array|null
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $result = null;
        if (!$this->isActive($quote, $total)) {
            return $result;
        }
        if ($giftVoucherDiscount = $total->getGiftVoucherDiscount()) {
            $result = [
                'code' => $this->getCode(),
                'title' => __('Gift Card'),
                'value' => -$giftVoucherDiscount,
                'gift_codes' => $quote->getGiftVoucherGiftCodes(),
                'codes_base_discount' => $quote->getCodesBaseDiscount(),
                'codes_discount' => $quote->getCodesDiscount()
            ];
        }
        return $result;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return bool
     */
    public function isActive(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        return true;
    }
}
