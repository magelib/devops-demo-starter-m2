<?php
/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace Magestore\Webpos\Block\Adminhtml\Report\Orderliststaff;

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
        $this->_columnGroupBy = 'webpos_staff_name';
        $this->_firstColumnReportKey = 'webpos_staff_name';
        $this->_firstColumnReportName = 'Staff';
        $this->_secondColumnReportKey = 'increment_id';        
        $this->_secondColumnReportName = 'Order ID';   
        $this->_isShowOrderNumber = false;     
        return parent::_construct();
    }

    /**
     * set resource model
     *
     * @return Magestore\Webpos\Model\ResourceModel\{collectionName}
     */
    public function getResourceCollectionName()
    {                
        return 'Magestore\Webpos\Model\ResourceModel\Report\OrderListStaff\Collection';
    }
}
