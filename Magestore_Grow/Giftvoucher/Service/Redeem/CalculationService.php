<?php
/**
 * Copyright © 2017 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Giftvoucher\Service\Redeem;

/**
 * Class CalculationService
 * @package Magestore\Giftvoucher\Service\Redeem
 */
class CalculationService implements \Magestore\Giftvoucher\Api\Redeem\CalculationServiceInterface
{
    protected $_hiddentBaseDiscount = 0;
    protected $_hiddentDiscount = 0;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magestore\Giftvoucher\Model\GiftvoucherFactory
     */
    protected $giftvoucherFactory;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magestore\Giftvoucher\Helper\Data
     */
    protected $helper;

    /*
    * @var  \Magento\Tax\Model\Calculation
    */
    protected $taxCalculation;

    /*
    * @var  \Magento\Tax\Model\Config
    */
    protected $taxConfig;

    /*
    * @var \Magento\Tax\Helper\Data
    */
    protected $helperTax;

    /**
     * CalculationService constructor.
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magestore\Giftvoucher\Model\GiftvoucherFactory $giftvoucherFactory
     * @param \Magestore\Giftvoucher\Helper\Data $helper
     * @param \Magento\Tax\Model\Calculation $taxCalculation
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Tax\Helper\Data $helperTax
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magestore\Giftvoucher\Model\GiftvoucherFactory $giftvoucherFactory,
        \Magestore\Giftvoucher\Helper\Data $helper,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Tax\Helper\Data $helperTax
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->giftvoucherFactory = $giftvoucherFactory;
        $this->priceCurrency = $priceCurrency;
        $this->helper = $helper;
        $this->taxCalculation = $taxCalculation;
        $this->taxConfig = $taxConfig;
        $this->helperTax = $helperTax;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @param string $code
     * @return $this|bool
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total,
        $code
    ) {
        $address = $shippingAssignment->getShipping()->getAddress();
        if (!$this->validateQuote($quote, $address)) {
            return false;
        }
        if ($codes = $quote->getGiftVoucherGiftCodes()) {
            $codesArray = array_unique(explode(',', $codes));
            $baseTotalDiscount = 0;
            $totalDiscount = 0;

            $codesBaseDiscount = array();
            $codesDiscount = array();
            $baseAmountUsed = array();
            foreach ($codesArray as $key => $value) {
                $baseAmountUsed[$value] = '';
            }
            $amountUsed = $baseAmountUsed;
            $giftMaxUseAmount = unserialize($quote->getGiftVoucherGiftCodesMaxDiscount());
            if (!is_array($giftMaxUseAmount)) {
                $giftMaxUseAmount = array();
            }

            foreach ($codesArray as $key => $code) {
                $giftvoucher = $this->giftvoucherFactory->create()->loadByCode($code);
                if (!$this->validateGiftCode($giftvoucher, $quote, $address, $baseAmountUsed[$code])) {
                    $codesBaseDiscount[] = 0;
                    $codesDiscount[] = 0;
                } else {
                    if ($this->validateCustomer($giftvoucher, $quote->getCustomerId())) {
                        $baseBalance = $giftvoucher->getBaseBalance() - $baseAmountUsed[$code];
                        if (array_key_exists($code, $giftMaxUseAmount)) {
                            if ($this->isApplyGiftAfterTax($quote->getStoreId())) {
                                $maxDiscount = max(floatval($giftMaxUseAmount[$code]), 0)
                                    / $this->priceCurrency->convert(1, false, false);
                            } else {
                                $maxDiscount = max(floatval($giftMaxUseAmount[$code]), 0);
                            }
                            $baseBalance = min($baseBalance, $maxDiscount);
                        }
                        if ($baseBalance > 0) {
                            $baseDiscountTotal = 0;
                            foreach ($address->getQuote()->getAllItems() as $item) {
                                if ($item->getParentItemId()) {
                                    continue;
                                }
                                $baseDiscountTotal += $this->calculateItemDiscount($giftvoucher, $item);
                            }
                            $baseDiscountTotal += $this->calculateShipingDiscount($address, $total);
                        }
                    }
                    if (!isset($baseDiscountTotal)) {
                        $baseDiscountTotal = 0;
                    }
                    if (!isset($baseBalance)) {
                        $baseBalance = 0;
                    }
                    $baseDiscount = min($baseDiscountTotal, $baseBalance);
                    $baseDiscount = ($baseDiscount > 0)?$baseDiscount:0;
                    $discount = $baseDiscount;
                    if ($this->isApplyGiftAfterTax($quote->getStoreId())) {
                        $discount = $this->priceCurrency->convertAndRound($baseDiscount);
                        $discount = ($discount > 0)?$discount:0;
                    }
                    if ($baseDiscountTotal > 0) {
                        $calculate = $baseDiscount / $baseDiscountTotal;
                    } else {
                        $calculate = 0;
                    }
                    $this->prepareGiftDiscountForItem($total, $address, $calculate, $giftvoucher, $baseDiscount);

                    $baseAmountUsed[$code] += $baseDiscount;
                    $amountUsed[$code] = $this->priceCurrency->convert($baseAmountUsed[$code]);

                    $baseTotalDiscount += $baseDiscount;
                    $totalDiscount += $discount;

                    $codesBaseDiscount[] = $baseDiscount;
                    $codesDiscount[] = $discount;
                }
            }
            $codesBaseDiscountString = implode(',', $codesBaseDiscount);
            $codesDiscountString = implode(',', $codesDiscount);
            //update address
            if ($this->isApplyGiftAfterTax($quote->getStoreId())) {
                $total->setBaseGrandTotal($total->getBaseGrandTotal() - $baseTotalDiscount);
                $total->setGrandTotal($this->priceCurrency->convert($total->getBaseGrandTotal()));
                $total->setGiftVoucherDiscount($totalDiscount);
                $total->setMagestoreBaseDiscount($total->getMagestoreBaseDiscount() + $baseTotalDiscount);
            } else {
                $total->setBaseGrandTotal($total->getBaseGrandTotal() + $this->_hiddentBaseDiscount - $baseTotalDiscount);
                $total->setGrandTotal($total->getGrandTotal() + $this->_hiddentDiscount - $totalDiscount);
                $total->setGiftVoucherDiscount($this->priceCurrency->convert($totalDiscount));
                $total->setMagestoreBaseDiscount($address->getMagestoreBaseDiscount() + $baseTotalDiscount);
            }


            $total->setBaseGiftVoucherDiscount($baseTotalDiscount);
            $total->setCodesBaseDiscount($codesBaseDiscountString);
            $total->setCodesDiscount($codesDiscountString);
            $total->setGiftvoucherBaseHiddenTaxAmount($this->_hiddentBaseDiscount);
            $total->setGiftvoucherHiddenTaxAmount($this->_hiddentDiscount);
            $total->addTotalAmount($code, -($this->priceCurrency->convert($totalDiscount)));
            $total->addBaseTotalAmount($code, -$baseTotalDiscount);
            $total->setGiftVoucherGiftCodes($codes);

            //update quote
            $quote->setGiftvoucherBaseHiddenTaxAmount($this->_hiddentBaseDiscount);
            $quote->setGiftvoucherHiddenTaxAmount($this->_hiddentDiscount);
            $quote->setBaseGiftVoucherDiscount($baseTotalDiscount);
            $quote->setGiftVoucherDiscount($this->priceCurrency->convert($totalDiscount));
            $quote->setGiftVoucherGiftCodes($codes);
            $quote->setGiftVoucherGiftCodesDiscount($codesDiscountString);
            $quote->setCodesBaseDiscount($codesBaseDiscountString);
            $quote->setCodesDiscount($codesDiscountString);
        }
        return $this;
    }

    /**
     * @param $quote
     * @return $this
     */
    public function clearData($quote)
    {
        $quote->setGiftVoucherGiftCodes('');
        $quote->setGiftVoucherGiftCodesDiscount('');
        $quote->setGiftVoucherGiftCodesMaxDiscount('');
        $quote->setGiftvoucherBaseHiddenTaxAmount(0);
        $quote->setGiftvoucherHiddenTaxAmount(0);
        $quote->setBaseGiftVoucherDiscount(0);
        $quote->setGiftVoucherDiscount(0);
        $quote->setCodesBaseDiscount('');
        $quote->setCodesDiscount('');
        $this->quoteRepository->save($quote);
        return $this;
    }

    /**
     * @param int $storeId
     * @return bool
     */
    public function isApplyGiftAfterTax($storeId)
    {
        $applyGiftAfterTax = (bool) $this->helper->getGeneralConfig('apply_after_tax', $storeId);
        return $applyGiftAfterTax;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param $address
     * @return bool
     */
    public function validateQuote(\Magento\Quote\Model\Quote $quote, $address)
    {
        if ($address->getAddressType() == 'billing' && !$quote->isVirtual() || !$quote->getGiftVoucherGiftCodes()) {
            return false;
        }

        if ($quote->isVirtual() && $address->getAddressType() == 'shipping') {
            return false;
        }

        if (!$quote->isVirtual() && $address->getAddressType() == 'billing') {
            return false;
        }
        if ($quote->getCouponCode() && !$this->helper->getGeneralConfig('use_with_coupon')) {
            $this->clearData($quote);
            return false;
        }
        return true;
    }

    /**
     * @param \Magestore\Giftvoucher\Model\Giftvoucher $giftvoucher
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param float $baseAmountUsed
     * @return bool
     */
    public function validateGiftCode(\Magestore\Giftvoucher\Model\Giftvoucher $giftvoucher, $quote, $address, $baseAmountUsed)
    {
        $storeId = $quote->getStoreId();
        if ($giftvoucher->getStatus() != \Magestore\Giftvoucher\Model\Status::STATUS_ACTIVE
            || $giftvoucher->getBalance() == 0 || $giftvoucher->getBaseBalance() <= $baseAmountUsed
            || !$giftvoucher->validate($address)
            || !(!$giftvoucher->getStoreId() || !$storeId || $storeId == $giftvoucher->getStoreId())
        ) {
            return false;
        }
        return true;
    }

    /**
     * @param \Magestore\Giftvoucher\Model\Giftvoucher $giftvoucher
     * @param int $customerId
     * @return bool
     */
    public function validateCustomer(\Magestore\Giftvoucher\Model\Giftvoucher $giftvoucher, $customerId)
    {
        if (!($giftvoucher instanceof \Magestore\Giftvoucher\Model\Giftvoucher)) {
            return false;
        }
        if (!$giftvoucher->getId()) {
            return false;
        }
        $shareCard = intval($this->helper->getGeneralConfig('share_card'));
        if ($shareCard < 1) {
            return true;
        }
        $customersUsed = $giftvoucher->getCustomerIdsUsed();
        if ($shareCard > count($customersUsed) || in_array($customerId, $customersUsed)
        ) {
            return true;
        }
        return false;
    }

    /**
     * @param \Magestore\Giftvoucher\Model\Giftvoucher $giftvoucher
     * @param $item
     * @return float
     */
    public function calculateItemDiscount(\Magestore\Giftvoucher\Model\Giftvoucher $giftvoucher, $item)
    {
        $baseDiscountTotal = 0;
        if ($item->getHasChildren() && $item->isChildrenCalculated()) {
            foreach ($item->getChildren() as $child) {
                if (!$child->isDeleted() && $child->getProduct()->getTypeId() != 'giftvoucher'
                    && $giftvoucher->getActions()->validate($child)
                ) {
                    if ($this->isApplyGiftAfterTax($item->getQuote()->getStoreId())) {
                        $itemDiscount = $child->getBaseRowTotal()
                            - $child->getMagestoreBaseDiscount() - $child->getBaseDiscountAmount()
                            + $child->getBaseTaxAmount();
                    } else {
                        if ($this->helperTax->priceIncludesTax()) {
                            $itemDiscount = $child->getRowTotalInclTax()
                                - $child->getMagestoreBaseDiscount() - $child->getDiscountAmount();
                        } else {
                            $itemDiscount = $child->getBaseRowTotal()
                                - $child->getMagestoreBaseDiscount()
                                - $child->getBaseDiscountAmount();
                        }
                    }
                    $baseDiscountTotal += $itemDiscount;
                }
            }
        } elseif ($item->getProduct()) {
            if (!$item->isDeleted() && $item->getProduct()->getTypeId() != 'giftvoucher'
                && $giftvoucher->getActions()->validate($item)
            ) {
                if ($this->isApplyGiftAfterTax($item->getQuote()->getStoreId())) {
                    $itemDiscount = $item->getBaseRowTotal() - $item->getMagestoreBaseDiscount()
                        - $item->getBaseDiscountAmount() + $item->getBaseTaxAmount();
                } else {
                    if ($this->helperTax->priceIncludesTax()) {
                        $itemDiscount = $item->getRowTotalInclTax()
                            - $item->getMagestoreBaseDiscount() - $item->getDiscountAmount();
                    } else {
                        $itemDiscount = $item->getBaseRowTotal()
                            - $item->getMagestoreBaseDiscount()
                            - $item->getBaseDiscountAmount();
                    }
                }
                $baseDiscountTotal += $itemDiscount;
            }
        }
        return $baseDiscountTotal;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return int
     */
    public function calculateShipingDiscount(\Magento\Quote\Model\Quote\Address $address, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $shipDiscount = 0;
        if ($this->helper->getStoreConfig('giftvoucher/general/use_for_ship', $address->getQuote()->getStoreId())) {
            if ($this->isApplyGiftAfterTax($address->getQuote()->getStoreId())) {
                $shipDiscount += $total->getBaseShippingAmount()
                    - $total->getMagestoreBaseDiscountForShipping()
                    - $total->getBaseShippingDiscountAmount() + $total->getBaseShippingTaxAmount();
            } else {
                if ($this->helperTax->shippingPriceIncludesTax()) {
                    $shipDiscount += $address->getShippingInclTax()
                        - $address->getMagestoreBaseDiscountForShipping()
                        - $address->getShippingDiscountAmount();
                } else {
                    $shipDiscount += $address->getBaseShippingAmount()
                        - $address->getMagestoreBaseDiscountForShipping()
                        - $address->getBaseShippingDiscountAmount();
                }
            }
        }
        return $shipDiscount;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param float $rateDiscount
     * @param \Magestore\Giftvoucher\Model\Giftvoucher $model
     * @return $this
     */
    public function prepareGiftDiscountForItem(
        \Magento\Quote\Model\Quote\Address\Total $total,
        \Magento\Quote\Model\Quote\Address $address,
        $rateDiscount,
        $model
    ) {
        foreach ($address->getQuote()->getAllItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    if (!$child->isDeleted() && $child->getProduct()->getTypeId() != 'giftvoucher'
                        && $model->getActions()->validate($child)
                    ) {
                        if ($this->isApplyGiftAfterTax($address->getQuote()->getStoreId())) {
                            $this->_processItemDiscountAfterTax($child, $rateDiscount);
                        } else {
                            $this->_processItemDiscountBeforeTax($child, $rateDiscount);
                        }
                    }
                }
            } elseif ($item->getProduct()) {
                if (!$item->isDeleted() && $item->getProduct()->getTypeId() != 'giftvoucher'
                    && $model->getActions()->validate($item)
                ) {
                    if ($this->isApplyGiftAfterTax($address->getQuote()->getStoreId())) {
                        $this->_processItemDiscountAfterTax($item, $rateDiscount);
                    } else {
                        $this->_processItemDiscountBeforeTax($item, $rateDiscount);
                    }
                }
            }
        }
        if ($this->helper->getStoreConfig('giftvoucher/general/use_for_ship', $address->getQuote()->getStoreId())) {
            if ($this->isApplyGiftAfterTax($address->getQuote()->getStoreId())) {
                $this->_processShippingDiscountAfterTax($address, $total, $rateDiscount);
            } else {
                $this->_processShippingDiscountBeforeTax($address, $total, $rateDiscount);
            }
        }


        return $this;
    }

    /**
     * Get the tax rate of shipping
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param \Magento\Store\Model\Store $store
     * @return float
     */
    public function getShipingTaxRate($address, $store)
    {
        $request = $this->taxCalculation->getRateRequest(
            $address,
            $address->getQuote()->getBillingAddress(),
            $address->getQuote()->getCustomerTaxClassId(),
            $store
        );
        $request->setProductClassId($this->taxConfig->getShippingTaxClass($store));
        $rate = $this->taxCalculation->getRate($request);
        return $rate;
    }

    /**
     * @param $item
     * @param float $rateDiscount
     */
    protected function _processItemDiscountAfterTax($item, $rateDiscount)
    {
        $itemDiscount = $item->getBaseRowTotal() - $item->getMagestoreBaseDiscount()
            - $item->getBaseDiscountAmount() + $item->getBaseTaxAmount();
        $item->setMagestoreBaseDiscount($item->getMagestoreBaseDiscount() + $itemDiscount * $rateDiscount);
        $item->setBaseGiftVoucherDiscount($item->getBaseGiftVoucherDiscount()
            + $itemDiscount * $rateDiscount);
        $item->setGiftVoucherDiscount($item->getGiftVoucherDiscount()
            + $this->priceCurrency->convert($itemDiscount * $rateDiscount));
    }

    /**
     * @param $item
     * @param float $rateDiscount
     */
    protected function _processItemDiscountBeforeTax($item, $rateDiscount)
    {
        if ($this->helperTax->priceIncludesTax()) {
            $itemDiscount = $item->getRowTotalInclTax() - $item->getMagestoreBaseDiscount()
                - $item->getDiscountAmount();
        } else {
            $itemDiscount = $item->getBaseRowTotal() - $item->getMagestoreBaseDiscount()
                - $item->getBaseDiscountAmount();
        }

        $item->setMagestoreBaseDiscount($item->getMagestoreBaseDiscount() + $itemDiscount * $rateDiscount);
        $item->setBaseGiftVoucherDiscount($item->getBaseGiftVoucherDiscount()
            + $itemDiscount * $rateDiscount);
        $item->setGiftVoucherDiscount($item->getGiftVoucherDiscount()
            + $this->priceCurrency->convert($itemDiscount * $rateDiscount));

        $baseTaxableAmount = $item->getBaseTaxableAmount();
        $taxableAmount = $item->getTaxableAmount();
        $item->setBaseTaxableAmount($item->getBaseTaxableAmount() - $item->getBaseGiftVoucherDiscount());
        $item->setTaxableAmount($item->getTaxableAmount() - $item->getGiftVoucherDiscount());

        if ($this->helperTax->priceIncludesTax()) {
            $rate = $this->helper->getItemRateOnQuote($item->getProduct(), $store);
            $hiddenBaseTaxBeforeDiscount = $this->taxCalculation
                ->calcTaxAmount($baseTaxableAmount, $rate, true, false);
            $hiddenTaxBeforeDiscount = $this->taxCalculation
                ->calcTaxAmount($taxableAmount, $rate, true, false);

            $hiddenBaseTaxAfterDiscount = $this->taxCalculation
                ->calcTaxAmount($item->getBaseTaxableAmount(), $rate, true, false);
            $hiddenTaxAfterDiscount = $this->taxCalculation
                ->calcTaxAmount($item->getTaxableAmount(), $rate, true, false);

            $hiddentBaseDiscount = $this->taxCalculation->round($hiddenBaseTaxBeforeDiscount)
                - $this->taxCalculation->round($hiddenBaseTaxAfterDiscount);
            $hiddentDiscount = $this->taxCalculation->round($hiddenTaxBeforeDiscount)
                - $this->taxCalculation->round($hiddenTaxAfterDiscount);

            $item->setGiftvoucherBaseHiddenTaxAmount($hiddentBaseDiscount);
            $item->setGiftvoucherHiddenTaxAmount($hiddentDiscount);

            $this->_hiddentBaseDiscount += $hiddentBaseDiscount;
            $this->_hiddentDiscount += $hiddentDiscount;
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @param float $rateDiscount
     */
    protected function _processShippingDiscountAfterTax($address, $total, $rateDiscount)
    {
        $shipDiscount = $total->getBaseShippingAmount() - $total->getMagestoreBaseDiscountForShipping()
            - $total->getBaseShippingDiscountAmount() + $total->getBaseShippingTaxAmount();
        $total->setMagestoreBaseDiscountForShipping($total->getMagestoreBaseDiscountForShipping()
            + $shipDiscount * $rateDiscount);
        $total->setBaseGiftvoucherDiscountForShipping($total->getBaseGiftvoucherDiscountForShipping()
            + $shipDiscount * $rateDiscount);
        $total->setGiftvoucherDiscountForShipping($total->getGiftvoucherDiscountForShipping()
            + $this->priceCurrency->convert($shipDiscount * $rateDiscount));

        $address->getQuote()->setBaseGiftvoucherDiscountForShipping($total->getBaseGiftvoucherDiscountForShipping());
        $address->getQuote()->setGiftvoucherDiscountForShipping($total->getGiftvoucherDiscountForShipping());
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @param float $rateDiscount
     */
    protected function _processShippingDiscountBeforeTax($address, $total, $rateDiscount)
    {
        if ($this->helperTax->shippingPriceIncludesTax()) {
            $shipDiscount = $total->getShippingInclTax() - $total->getMagestoreBaseDiscountForShipping()
                - $total->getShippingDiscountAmount();
        } else {
            $shipDiscount = $total->getBaseShippingAmount() - $total->getMagestoreBaseDiscountForShipping()
                - $total->getBaseShippingDiscountAmount();
        }

        $total->setMagestoreBaseDiscountForShipping($total->getMagestoreBaseDiscountForShipping()
            + $shipDiscount * $rateDiscount);
        $total->setBaseGiftvoucherDiscountForShipping($total->getBaseGiftvoucherDiscountForShipping()
            + $shipDiscount * $rateDiscount);
        $total->setGiftvoucherDiscountForShipping($total->getGiftvoucherDiscountForShipping()
            + $this->priceCurrency->convert($shipDiscount * $rateDiscount));

        $address->getQuote()->setBaseGiftvoucherDiscountForShipping($total->getBaseGiftvoucherDiscountForShipping());
        $address->getQuote()->setGiftvoucherDiscountForShipping($total->getGiftvoucherDiscountForShipping());

        $baseTaxableAmount = $total->getBaseShippingTaxable();
        $taxableAmount = $total->getShippingTaxable();

        $total->setBaseShippingTaxable($total->getBaseShippingTaxable()
            - $total->getBaseGiftvoucherDiscountForShipping());
        $total->setShippingTaxable($total->getShippingTaxable()
            - $total->getGiftvoucherDiscountForShipping());

        if ($this->helperTax->shippingPriceIncludesTax() && $shipDiscount) {
            $rate = $this->getShipingTaxRate($address, $store);
            $hiddenBaseTaxBeforeDiscount = $this->taxCalculation
                ->calcTaxAmount($baseTaxableAmount, $rate, true, false);
            $hiddenTaxBeforeDiscount = $this->taxCalculation
                ->calcTaxAmount($taxableAmount, $rate, true, false);

            $hiddenBaseTaxAfterDiscount = $this->taxCalculation
                ->calcTaxAmount($total->getBaseShippingTaxable(), $rate, true, false);
            $hiddenTaxAfterDiscount = $this->taxCalculation
                ->calcTaxAmount($total->getShippingTaxable(), $rate, true, false);

            $hiddentBaseShippingDiscount = $this->taxCalculation->round($hiddenBaseTaxBeforeDiscount)
                - $this->taxCalculation->round($hiddenBaseTaxAfterDiscount);
            $hiddentShippingDiscount = $this->taxCalculation->round($hiddenTaxBeforeDiscount)
                - $this->taxCalculation->round($hiddenTaxAfterDiscount);

            $total->setGiftvoucherBaseShippingHiddenTaxAmount($hiddentBaseShippingDiscount);
            $total->setGiftvoucherShippingHiddenTaxAmount($hiddentShippingDiscount);

            $address->getQuote()->setGiftvoucherBaseShippingHiddenTaxAmount($hiddentBaseShippingDiscount);
            $address->getQuote()->setGiftvoucherShippingHiddenTaxAmount($hiddentShippingDiscount);

            $this->_hiddentBaseDiscount += $hiddentBaseShippingDiscount;
            $this->_hiddentDiscount += $hiddentShippingDiscount;
        }
    }
}
