<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\PurchaseOrderSuccess\Controller\Adminhtml\PurchaseOrder;

/**
 * Class PrintPurchaseOrder
 * @package Magestore\PurchaseOrderSuccess\Controller\Adminhtml\PurchaseOrder
 */
class PrintPurchaseOrder extends \Magestore\PurchaseOrderSuccess\Controller\Adminhtml\AbstractAction
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magestore_PurchaseOrderSuccess::print_purchase_order';

    /**
     * @var \Magestore\PurchaseOrderSuccess\Api\PurchaseOrderRepositoryInterface
     */
    protected $purchaseOrderRepository;

    /**
     * @var \Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\PurchaseOrderService
     */
    protected $purchaseOrderService;

    /**
     * @var \Magestore\SupplierSuccess\Api\SupplierRepositoryInterface
     */
    protected $supplierRepository;

    /**
     * SendRequest constructor.
     * @param \Magestore\PurchaseOrderSuccess\Controller\Adminhtml\Context $context
     * @param \Magestore\PurchaseOrderSuccess\Api\PurchaseOrderRepositoryInterface $purchaseOrderRepository
     * @param \Magestore\SupplierSuccess\Api\SupplierRepositoryInterface $supplierRepository
     * @param \Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\PurchaseOrderService $purchaseOrderService
     */
    public function __construct(
        \Magestore\PurchaseOrderSuccess\Controller\Adminhtml\Context $context,
        \Magestore\PurchaseOrderSuccess\Api\PurchaseOrderRepositoryInterface $purchaseOrderRepository,
        \Magestore\SupplierSuccess\Api\SupplierRepositoryInterface $supplierRepository,
        \Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\PurchaseOrderService $purchaseOrderService
    ){
        parent::__construct($context);
        $this->purchaseOrderRepository = $purchaseOrderRepository;
        $this->supplierRepository = $supplierRepository;
        $this->purchaseOrderService = $purchaseOrderService;

    }

    /**
     * Quotation grid
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $purchaseId = $this->getRequest()->getParam('purchase_id');
        $type = $this->getRequest()->getParam('type');
        if(!class_exists('mPDF')){
            return $this->redirectForm(
                $type,
                $purchaseId,
                __('You must install mPDF if you want to print purchase order. Please visit here "%1" to do it.',
                    'https://github.com/mpdf/mpdf/releases'),
                \Magento\Framework\Message\MessageInterface::TYPE_ERROR
            );
        }
        $purchaseOrder = $this->purchaseOrderRepository->get($purchaseId);
        if($purchaseOrder && $purchaseOrder->getPurchaseOrderId()){
            $this->_registry->register('current_purchase_order', $purchaseOrder);
            try{
                $supplier = $this->supplierRepository->getById($purchaseOrder->getSupplierId());
                $this->_registry->register('current_purchase_order_supplier', $supplier);
                $fileName = 'PurchaseOrder';
                $html = $this->_resultPageFactory->create()->getLayout()->getBlock('print-header')->toHtml();
                $html .= $this->_resultPageFactory->create()->getLayout()->getBlock('print-items')->toHtml();
                $html .= $this->_resultPageFactory->create()->getLayout()->getBlock('print-total')
                    ->setWidth('44%')->toHtml();
                $mPDF = $this->_objectManager->create('mPDF');
                $mPDF->WriteHTML($html);
                $mPDF->Output($fileName . '.pdf', 'D');
                return;
            }catch (\Exception $e){
                return $this->redirectForm(
                    $type,
                    $purchaseId,
                    //$e->getMessage(),
                    __('Could not print this Purchase Sales'),
                    \Magento\Framework\Message\MessageInterface::TYPE_ERROR
                );
            }
        }else{
            return $this->redirectForm(
                $type,
                $purchaseId,
                __('Can not find the Purchase Order to print.'),
                \Magento\Framework\Message\MessageInterface::TYPE_ERROR
            );
        }
    }

}