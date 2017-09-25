<?php
/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace Magestore\Webpos\Block\Adminhtml\Report\Salestaffdaily;

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
        $this->_columnGroupBy = 'created_at';
        $this->_timeColumnReportKey = 'created_at';
        $this->_firstColumnReportKey = 'webpos_staff_name';
        $this->_firstColumnReportName = 'Staff';       
        return parent::_construct();
    }

    /**
     * set resource model
     *
     * @return Magestore\Webpos\Model\ResourceModel\{collectionName}
     */
    public function getResourceCollectionName()
    {                
        return 'Magestore\Webpos\Model\ResourceModel\Report\SaleStaffDaily\Collection';
    }
}
