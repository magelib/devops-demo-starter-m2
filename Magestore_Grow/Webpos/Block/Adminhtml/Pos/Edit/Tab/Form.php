<?php

/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace Magestore\Webpos\Block\Adminhtml\Pos\Edit\Tab;

/**
 * Class Form
 * @package Magestore\Webpos\Block\Adminhtml\Pos\Edit\Tab
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
    implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    protected $_objectManager;

    protected $_eventManager;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = array()
    )
    {
        $this->_objectManager = $objectManager;
        $this->_eventManager = $context->getEventManager();
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout() {
        $this->getLayout()->getBlock('page.title')->setPageTitle($this->getPageTitle());
    }


    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {

        $model = $this->_coreRegistry->registry('current_pos');
        $data = array();
        if ($model->getId()) {
            $data = $model->getData();
        }
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('page_');
        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('POS Information')));

        if ($model->getData('pos_id')) {
            $fieldset->addField('pos_id', 'hidden', array('name' => 'pos_id'));
        }

        $posId = $this->getRequest()->getParam('id');

        $fieldset->addField('pos_name', 'text', array(
            'label'     => __('POS Name'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'pos_name',
            'disabled' => false,
        ));

        $fieldset->addField('location_id', 'select', array(
            'label' => __('Location'),
            'required' => true,
            'class' => 'required-entry',
            'name' => 'location_id',
            'values' => $this->_objectManager->get('Magestore\Webpos\Model\Location\Location')->getValuesForForm()
        ));

        $fieldset->addField('store_id', 'select', array(
            'label' => __('Store View'),
            'required' => true,
            'class' => 'required-entry',
            'name' => 'store_id',
            'values' => $this->_objectManager->get('\Magento\Store\Model\System\Store')->getStoreValuesForForm()
        ));

        $fieldset->addField('staff_id', 'select', array(
            'label' => __('Current Staff'),
            'name' => 'staff_id',
            'values' => $this->_objectManager->get('Magestore\Webpos\Model\Pos\Pos')->getValuesForForm($posId),
            'note' => __('Staff is working on the POS')
        ));

        $fieldset->addField('status', 'select', array(
            'label' => __('Status'),
            'name' => 'status',
            'values' => $this->_objectManager->get('Magestore\Webpos\Model\Source\Adminhtml\Status')->toOptionArray()
        ));

        $fieldset->addField('auto_join', 'checkbox', array(
            'label' => __('Available for Other Staff'),
            'name' => 'auto_join',
            'value' => 1,
            'note' => __('When checked: another staff can use the POS when it is available.')
        ));

        $this->_eventManager->dispatch('webpos_pos_edit_form', ['form'=>$form,'field_set' => $fieldset,'model_data'=>$model]);
        $data['auto_join'] = $form->getElement('auto_join')->getValue();
        $form->setValues($data);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * @return mixed
     */
    public function getCurrentModel() {
        return $this->_coreRegistry->registry('current_pos');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getPageTitle() {
        return $this->getCurrentModel()->getId() ? __("Edit POS %1",
            $this->escapeHtml($this->getCurrentModel()->getData('pos_name'))) : __('New POS');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('POS Information');
    }


    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('POS Information');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }


}