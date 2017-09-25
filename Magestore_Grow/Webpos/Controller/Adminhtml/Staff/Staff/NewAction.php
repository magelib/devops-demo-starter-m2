<?php

/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace Magestore\Webpos\Controller\Adminhtml\Staff\Staff;

use Magento\Framework\Controller\ResultFactory;

/**
 * class \Magestore\Webpos\Controller\Adminhtml\Staff\Staff\NewAction
 *
 * New user
 * Methods:
 *  execute
 *
 * @category    Magestore
 * @package     Magestore\Webpos\Controller\Adminhtml\Staff\Staff
 * @module      Webpos
 * @author      Magestore Developer
 */
class NewAction extends \Magestore\Webpos\Controller\Adminhtml\Staff\Staff
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        return $resultForward->forward('edit');
    }
}
