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

namespace Magestore\Customercredit\Model\Total\Quote;

use Magento\Framework\App\Area;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class CreditDiscount extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magestore\Customercredit\Helper\Data
     */
    protected $_creditHelper;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $_sessionQuote;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Magestore\Customercredit\Model\CustomercreditFactory
     */
    protected $_creditModel;
    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $_helperTax;
    /**
     * @var PriceCurrencyInterface $priceCurrency
     */
    protected $priceCurrency;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;
    /**
     * @var \Magestore\Customercredit\Helper\Account
     */
    protected $_accountHelper;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magestore\Customercredit\Helper\Data $creditHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magestore\Customercredit\Model\CustomercreditFactory $creditModel
     * @param \Magento\Tax\Helper\Data $helperTax
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magestore\Customercredit\Helper\Account $accountHelper
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magestore\Customercredit\Helper\Data $creditHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Customer\Model\Session $customerSession,
        \Magestore\Customercredit\Model\CustomercreditFactory $creditModel,
        \Magento\Tax\Helper\Data $helperTax,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magestore\Customercredit\Helper\Account $accountHelper
    )
    {
        $this->setCode('creditdiscount');
        $this->storeManager = $storeManager;
        $this->_creditHelper = $creditHelper;
        $this->_checkoutSession = $checkoutSession;
        $this->_sessionQuote = $sessionQuote;
        $this->_customerSession = $customerSession;
        $this->_creditModel = $creditModel;
        $this->_helperTax = $helperTax;
        $this->priceCurrency = $priceCurrency;
        $this->objectManager = $objectManager;
        $this->_accountHelper = $accountHelper;
    }

    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    )
    {
        $store = $this->storeManager->getStore($quote->getStoreId());
        $address = $shippingAssignment->getShipping()->getAddress();
        $items = $shippingAssignment->getItems();

        if(!$this->_creditHelper->getSpendConfig('tax',$quote->getStoreId())
            || ($quote->isVirtual() && $address->getAddressType() == 'shipping')
            || (!$quote->isVirtual() && $address->getAddressType() == 'billing')
            ||!count($items)
        ){
            return $this;
        }

        $creditAmountEntered = $this->_checkoutSession->getCustomerCreditAmount();
        if ($creditAmountEntered === 0 || !$this->_accountHelper->customerGroupCheck()) {
            $this->_checkoutSession->setCreditdiscountAmount(null);
            $this->_checkoutSession->setBaseCreditdiscountAmount(null);
            return $this;
        }
        $baseDiscountTotal = 0;
        foreach ($items as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    if (!$child->isDeleted() && $child->getProduct()->getTypeId() != 'customercredit') {
                        $itemDiscount = $child->getBaseRowTotal() + $child->getBaseTaxAmount() - $child->getBaseDiscountAmount() - $child->getMagestoreBaseDiscount();
                        $baseDiscountTotal += $itemDiscount;
                    }
                }
            } else if ($item->getProduct()) {
                if (!$item->isDeleted() && $item->getProduct()->getTypeId() != 'customercredit') {
                    $itemDiscount = $item->getBaseRowTotal() + $item->getBaseTaxAmount() - $item->getBaseDiscountAmount() - $item->getMagestoreBaseDiscount();
                    $baseDiscountTotal += $itemDiscount;
                }
            }
        }
        $baseShippingDiscount = 0;
        if ($this->_creditHelper->getSpendConfig('shipping')) {
            $baseShippingDiscount = $address->getBaseShippingAmount() + $address->getBaseShippingTaxAmount() - $address->getBaseShippingDiscountAmount() - $address->getMagestoreBaseDiscountForShipping();
            $baseDiscountTotal += $baseShippingDiscount;
        }
        $baseDiscount = min($creditAmountEntered, $baseDiscountTotal);
        $discount = $this->priceCurrency->convert($baseDiscount);


        $total->addTotalAmount('creditdiscount', -$discount);
        $total->addBaseTotalAmount('creditdiscount', -$baseDiscount);

        $total->setCustomercreditAmount($discount);
        $total->setBaseCustomercreditAmount($baseDiscount);

        $quote->setCreditdiscountAmount($discount);
        $quote->setBaseCreditdiscountAmount($baseDiscount);
        $this->_checkoutSession->setCreditdiscountAmount($discount);
        $this->_checkoutSession->setBaseCreditdiscountAmount($baseDiscount);

        $total->setGrandTotal($total->getGrandTotal() - $discount);
        $total->setBaseGrandTotal($total->getBaseGrandTotal() - $baseDiscount);
        if ($baseDiscountTotal != 0)
            $this->_prepareDiscountCreditForItem($address, $quote, $baseDiscount / $baseDiscountTotal, $baseShippingDiscount);
        return $this;
    }

    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $result = null;
        if(!$this->_creditHelper->getSpendConfig('tax',$quote->getStoreId()) || !$this->_accountHelper->customerGroupCheck()){
            return $result;
        }

        $amount = $total->getCustomercreditAmount();
        if (!$amount) {
            $amount = $this->_checkoutSession->getCreditdiscountAmount();
        }
        if ($amount != 0) {
            $result = [
                'code' => $this->getCode(),
                'title' => __('Customer Credit'),
                'value' => -$amount
            ];
        }
        return $result;
    }

    public function _prepareDiscountCreditForItem(\Magento\Quote\Model\Quote\Address $address, $quote, $rate, $baseShippingDiscount)
    {
        // Update discount for each item
        $helper = $this->_creditHelper;
        foreach ($address->getAllItems() as $item) {
            if ($item->getParentItemId())
                continue;
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    if (!$child->isDeleted() && $child->getProduct()->getTypeId() != 'customercredit') {
                        $baseItemPrice = $child->getBaseRowTotal() + $child->getBaseTaxAmount() - $child->getBaseDiscountAmount();
                        $itemBaseDiscount = $baseItemPrice * $rate;
                        $itemDiscount = $this->priceCurrency->convert($itemBaseDiscount);
                        $child->setBaseCustomercreditDiscount($itemBaseDiscount)
                            ->setCustomercreditDiscount($itemDiscount);
                    }
                }
            } else if ($item->getProduct()) {
                if (!$item->isDeleted() && $item->getProduct()->getTypeId() != 'customercredit') {
                    $baseItemPrice = $item->getBaseRowTotal() + $item->getBaseTaxAmount() - $item->getBaseDiscountAmount();
                    $itemBaseDiscount = $baseItemPrice * $rate;
                    $itemDiscount = $this->priceCurrency->convert($itemBaseDiscount);
                    $item->setBaseCustomercreditDiscount($itemBaseDiscount)
                        ->setCustomercreditDiscount($itemDiscount);
                }
            }
        }
        if ($helper->getSpendConfig('shipping') && $baseShippingDiscount) {
            $baseShippingPrice = $address->getBaseShippingAmount() + $address->getBaseShippingTaxAmount() - $address->getBaseShippingDiscountAmount();
            $baseShippingDiscount = min($baseShippingPrice, $baseShippingDiscount);
            $baseShippingDiscount = $baseShippingDiscount * $rate;
            $shippingDiscount = $this->priceCurrency->convert($baseShippingDiscount);
            $address->setBaseCustomercreditDiscountForShipping($baseShippingDiscount);
            $address->setCustomercreditDiscountForShipping($shippingDiscount);
            $quote->setBaseCustomercreditDiscountForShipping($baseShippingDiscount);
            $quote->setCustomercreditDiscountForShipping($shippingDiscount);
        }
        return $this;
    }

}
