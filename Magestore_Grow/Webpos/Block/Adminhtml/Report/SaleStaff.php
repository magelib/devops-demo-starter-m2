<?php

/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace Magestore\Webpos\Block\Adminhtml\Report;

/**
 * class \Magestore\Webpos\Block\Adminhtml\Report\SaleStaff
 * 
 * @category    Magestore
 * @package     Magestore\Webpos
 * @module      Webpos
 * @author      Magestore Developer
 */
class SaleStaff extends \Magestore\Webpos\Block\Adminhtml\Report\Report
{
    /**
     * contructor
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_report_salestaff';
        $this->_blockGroup = 'Magestore_Webpos';
        $this->_headerText = __('Sales by staff');
        return parent::_construct();
    }
}

