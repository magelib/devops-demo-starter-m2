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
 * @package     Magestore_RewardPoints
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Action Abstract Model to Change Points on Reward Points system
 *
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
namespace Magestore\Rewardpoints\Model\Action;
abstract class AbstractAction extends \Magento\Framework\DataObject
{
    /**
     * @var \Magestore\Rewardpoints\Helper\Data
     */
    protected $_transaction;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;
    /**
     * @var \Magestore\Rewardpoints\Helper\Data
     */
    protected $_helper;

    /**
     * Invoice constructor.
     * @param \Magestore\Rewardpoints\Model\TransactionFactory $transaction
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magestore\Rewardpoints\Helper\Data $helper,
        \Magestore\Rewardpoints\Model\TransactionFactory $transaction,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlBuilder
    )
    {
        $this->_helper = $helper;
        $this->_transaction = $transaction;
        $this->_storeManager = $storeManager;
        $this->_urlBuilder = $urlBuilder;
    }
    /**
     * Action Code
     *
     * @var string
     */
    protected $_code = null;

    /**
     * get action code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * set action code
     *
     * @param string $value
     * @return Magestore_RewardPoints_Model_Action_Abstract
     */
    public function setCode($value)
    {
        $this->_code = $value;
        return $this;
    }

    /**
     * get HTML Title for action depend on current transaction
     *
     * @param Magestore_RewardPoints_Model_Transaction $transaction
     * @return string
     */
    public function getTitleHtml($transaction = null)
    {
        return $this->getTitle();
    }

    /**
     * prepare data of action to storage on transactions
     * the array that returned from function $action->getData('transaction_data')
     * will be setted to transaction model
     *
     * @return Magestore_RewardPoints_Model_Action_Abstract
     */
    public function prepareTransaction()
    {
        return $this;
    }

    /**
     * Calculate Expiration Date for transaction
     *
     * @param int $days Days to be expired
     * @return null|string
     */
    public function getExpirationDate($days = 0)
    {
        if ($days <= 0) {
            return null;
        }
        $timestamp = time() + $days * 86400;
        return date('Y-m-d H:i:s', $timestamp);
    }
}
