<?php
/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace Magestore\WebposPaypal\Controller\Adminhtml\Api;

/**
 * Class Test
 * @package Magestore\WebposPaypal\Controller\Adminhtml\Api
 */
class Test extends \Magestore\WebposPaypal\Controller\Adminhtml\AbstractAction
{
    /**
     * @return \Magento\Framework\Controller\Result\Json $resultJson
     */
    public function execute()
    {
        $response = [
            'url' => '',
            'message' => '',
            'success' => true
        ];
        $isEnable = $this->paypalService->isEnable();
        if ($isEnable) {
            $connected = $this->paypalService->canConnectToApi();
            $response['success'] = ($connected)?true:false;
            $response['message'] = ($connected) ? '' : __('Cannot connect to the Paypal API');
        } else {
            $message = $this->paypalService->getConfigurationError();
            $response['success'] = false;
            $response['message'] = __($message);
        }
        return $this->createJsonResult($response);
    }
}
