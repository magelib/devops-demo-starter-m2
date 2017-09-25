<?php
/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace Magestore\Webpos\Block\Adminhtml\Report\Salepayment;

/**
 * Report grid container.
 * @category Magestore
 * @package  Magestore_Webpos
 * @module   Webpos
 * @author   Magestore Developer
 */
class Grid extends \Magestore\Webpos\Block\Adminhtml\Report\AbstractGrid
{

    /**
     * contructor
     */
    protected function _construct()
    {
        $this->_columnGroupBy = 'payment.method_title';
        $this->_firstColumnReportKey = 'payment.method_title';
        $this->_firstColumnReportName = 'Payment Method';
        return parent::_construct();
    }

       /**
     * set resource model
     *
     * @return Magestore\Webpos\Model\ResourceModel\{collectionName}
     */
    public function getResourceCollectionName()
    {                
        return 'Magestore\Webpos\Model\ResourceModel\Report\SalePayment\Collection';
    }
}
