<?php

/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace Magestore\Webpos\Controller\Adminhtml\Report\SalePayment;

// use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportCsv extends \Magestore\Webpos\Controller\Adminhtml\Report\AbstractReport
{
    /**
     * Export sales report grid to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $fileName = 'sale_payment.csv';
        $grid = $this->_view->getLayout()->createBlock('Magestore\Webpos\Block\Adminhtml\Report\Salepayment\Grid');
        $this->_initReportAction($grid);
        return $this->_fileFactory->create($fileName, $grid->getCsvFile(), DirectoryList::VAR_DIR);
    }
}
