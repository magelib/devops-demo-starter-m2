<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\SupplierSuccess\Block\Adminhtml\Supplier\Grid\Column\Renderer;

use Magento\Framework\DataObject;

class Text extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    /**
     * Renders grid column
     *
     * @param   Object $row
     * @return  string
     */
    public function render(DataObject $row)
    {
        if ($this->getColumn()->getEditable()) {
            $result = '<div class="admin__grid-control">';
            $result .= $this->getColumn()->getEditOnly() ? ''
                : '<span class="admin__grid-control-value">' . $this->_getValue($row) . '</span>';

            return $result . $this->_getInputHiddenValueElement($row) .$this->_getInputValueElement($row) . '</div>' ;
        }
        return $this->_getValue($row);
    }

    /**
     * @param Object $row
     * @return string
     */
    public function _getInputValueElement(DataObject $row)
    {
        return '<input type="text" style="display: none" class="input-text ' .
        $this->getColumn()->getValidateClass() .
        '" name="' .
        $this->getColumn()->getId() .
        '" value="' .
        $this->_getInputValue(
            $row
        ) . '"/>';
    }

    /**
     * @param Object $row
     * @return string
     */
    public function _getInputHiddenValueElement(DataObject $row)
    {
        return '<input type="text" style="display: none" class="input-text ' .
        $this->getColumn()->getValidateClass() .
        '" name="' .
        $this->getColumn()->getId() . '_old' .
        '" value="' .
        $this->_getInputValue(
            $row
        ) . '"/>';
    }
}
