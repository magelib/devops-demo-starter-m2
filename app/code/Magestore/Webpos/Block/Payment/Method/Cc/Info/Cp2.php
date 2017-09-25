<?php

/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace Magestore\Webpos\Block\Payment\Method\Cc\Info;

/**
 * class \Magestore\Webpos\Block\Payment\Method\Cc\Info\Cp2
 * 
 * CP2 for POS info block
 * Methods:
 *  _construct
 *  _prepareSpecificInformation
 *  getMethodTitle
 * 
 * @category    Magestore
 * @package     Magestore\Webpos\Block\Payment\Method\Cc\Info
 * @module      Webpos
 * @author      Magestore Developer
 */
class Cp2 extends \Magestore\Webpos\Block\Payment\Method\InfoAbstract
{
    /**
     * Get method title from setting
     */
    public function getMethodTitle()
    {
        return $this->_helperPayment->getCp2MethodTitle();
    }

}