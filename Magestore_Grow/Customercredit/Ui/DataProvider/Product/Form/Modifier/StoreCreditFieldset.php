<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Customercredit
 * @copyright   Copyright (c) 2017 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 *
 */

namespace Magestore\Customercredit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Field;

if(!class_exists('Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier')) {
    class StoreCreditFieldset{

    }
} else {
class StoreCreditFieldset extends AbstractModifier
{
    // Components indexes
    const CUSTOM_FIELDSET_INDEX = 'storecredit_fieldset';
    const CUSTOM_FIELDSET_CONTENT = 'storecredit_fieldset_content';
    const CONTAINER_HEADER_NAME = 'storecredit_fieldset_content_header';

    // Fields names
    const FIELD_PRICE_TYPE = 'storecredit_type';
    const FIELD_PRICE_RATE = 'storecredit_rate';
    const FIELD_PRICE_VALUE = 'storecredit_value';
    const FIELD_PRICE_VALUES = 'storecredit_dropdown';
    const FIELD_PRICE_VALUE_FROM = 'storecredit_from';
    const FIELD_PRICE_VALUE_TO = 'storecredit_to';

    //    Storecredit type
    const CREDIT_TYPE_NONE = 0;
    const CREDIT_TYPE_FIX = 1;
    const CREDIT_TYPE_RANGE = 2;
    const CREDIT_TYPE_DROPDOWN = 3;

    const CREDIT_FIELD_COMPONENT = 'Magestore_Customercredit/js/components/credit-field';

    /**
     * @var \Magento\Catalog\Model\Locator\LocatorInterface
     */
    protected $locator;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * @param LocatorInterface $locator
     * @param ArrayManager $arrayManager
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager,
        UrlInterface $urlBuilder
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Data modifier, does nothing in our example.
     *
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * Meta-data modifier: adds ours fieldset
     *
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;
        $product = $this->locator->getProduct();
        if($product->getTypeId() == 'customercredit'){
            $this->addCustomFieldset();
        }
        return $this->meta;
    }

    /**
     * Merge existing meta-data with our meta-data (do not overwrite it!)
     *
     * @return void
     */
    protected function addCustomFieldset()
    {
        if(array_key_exists('credit-prices-settings', $this->meta))
            unset($this->meta['credit-prices-settings']);

        $this->meta = array_merge_recursive(
            $this->meta,
            [
                static::CUSTOM_FIELDSET_INDEX => $this->getFieldsetConfig(),
            ]
        );
    }

    /**
     * Declare ours fieldset config
     *
     * @return array
     */
    protected function getFieldsetConfig()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Credit Prices Settings '),
                        'componentType' => Fieldset::NAME,
                        'dataScope' => static::DATA_SCOPE_PRODUCT, // save data in the product data
                        'provider' => static::DATA_SCOPE_PRODUCT . '_data_source',
                        'ns' => static::FORM_NAME,
                        'collapsible' => true,
                        'sortOrder' => 10,
                        'opened' => true,
                    ],
                ],
            ],
            'children' => [
                static::CONTAINER_HEADER_NAME => $this->getHeaderContainerConfig(10),
                static::FIELD_PRICE_TYPE => $this->getSelectFieldConfig(20),
                static::FIELD_PRICE_RATE => $this->getRateFieldConfig(30),
                static::FIELD_PRICE_VALUE => $this->getValueFieldConfig(35),
                static::FIELD_PRICE_VALUES => $this->getValuesFieldConfig(40),
                static::FIELD_PRICE_VALUE_FROM => $this->getValueFromFieldConfig(45),
                static::FIELD_PRICE_VALUE_TO => $this->getValueToFieldConfig(50),
            ],
        ];
    }

    /**
     * Get config for header container
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getHeaderContainerConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => null,
                        'formElement' => Container::NAME,
                        'componentType' => Container::NAME,
                        'template' => 'ui/form/components/complex',
                        'sortOrder' => $sortOrder,
                        'content' => __('Credit product let customers choose the price type they want.'),
                    ],
                ],
            ],
            'children' => [],
        ];
    }

    /**
     * Example select field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getSelectFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Type of Store Credit Value'),
                        'componentType' => Field::NAME,
                        'formElement' => Select::NAME,
                        'dataScope' => static::FIELD_PRICE_TYPE,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'options' => $this->_getOptions(),
                        'visible' => true,
                        'validation' => [
                            'required-entry' => 'true'
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Example text field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getRateFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Credit Rate'),
                        'component' => self::CREDIT_FIELD_COMPONENT,
                        'formElement' => Field::NAME,
                        'componentType' => Input::NAME,
                        'dataScope' => static::FIELD_PRICE_RATE,
                        'dataType' => Number::NAME,
                        'sortOrder' => $sortOrder,
                        'default' => 1.0,
                        'disabled' => true,
                        'validation' => [
                            'required-entry' => 'true',
                            'validate-greater-than-zero' => 'true',
                        ],
                        'valuesForEnable' => [
                            '1' => '1',
                            '2' => '2',
                            '3' => '3'
                        ],
                        'imports' => [
                            'toggleDisable' => '${$.parentName}.' . static::FIELD_PRICE_TYPE . ':value'
                        ],
                        'notice' => __('For example: 1.5'),
                    ],
                ],
            ],
        ];
    }

    /**
     * Example text field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getValueFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Store Credit Value'),
                        'component' => self::CREDIT_FIELD_COMPONENT,
                        'formElement' => Field::NAME,
                        'componentType' => Input::NAME,
                        'dataScope' => static::FIELD_PRICE_VALUE,
                        'dataType' => Number::NAME,
                        'sortOrder' => $sortOrder,
                        'disabled' => true,
                        'validation' => [
                            'required-entry' => 'true',
                            'validate-greater-than-zero' => 'true',
                        ],
                        'valuesForEnable' => [
                            '1' => '1'
                        ],
                        'imports' => [
                            'toggleDisable' => '${$.parentName}.' . static::FIELD_PRICE_TYPE . ':value',
                        ],
                        'addbefore' => '$'
                    ],
                ],
            ],
        ];
    }

    /**
     * Example text field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getValuesFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Store Credit Values'),
                        'component' => self::CREDIT_FIELD_COMPONENT,
                        'formElement' => Field::NAME,
                        'componentType' => Input::NAME,
                        'dataScope' => static::FIELD_PRICE_VALUES,
                        'dataType' => Number::NAME,
                        'sortOrder' => $sortOrder,
                        'disabled' => true,
                        'validation' => [
                            'required-entry' => 'true',
                        ],
                        'valuesForEnable' => [
                            '3' => '3'
                        ],
                        'imports' => [
                            'toggleDisable' => '${$.parentName}.' . static::FIELD_PRICE_TYPE . ':value',
                        ],
                        'addbefore' => '$',
                        'notice' => __('Seperated by comma, e.g. 10,20,30'),
                    ],
                ],
            ],
        ];
    }

    /**
     * Example text field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getValueFromFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Minimum Store Credit Value'),
                        'component' => self::CREDIT_FIELD_COMPONENT,
                        'formElement' => Field::NAME,
                        'componentType' => Input::NAME,
                        'dataScope' => static::FIELD_PRICE_VALUE_FROM,
                        'dataType' => Number::NAME,
                        'sortOrder' => $sortOrder,
                        'disabled' => true,
                        'validation' => [
                            'required-entry' => 'true',
                            'validate-greater-than-zero' => 'true',
                        ],
                        'valuesForEnable' => [
                            '2' => '2'
                        ],
                        'imports' => [
                            'toggleDisable' => '${$.parentName}.' . static::FIELD_PRICE_TYPE . ':value',
                            'handleChangeMin' => '${$.parentName}.' . static::FIELD_PRICE_VALUE_TO . ':value'
                        ],
                        'addbefore' => '$'
                    ],
                ],
            ],
        ];
    }

    /**
     * Example text field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getValueToFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Maximum Store Credit Value'),
                        'component' => self::CREDIT_FIELD_COMPONENT,
                        'formElement' => Field::NAME,
                        'componentType' => Input::NAME,
                        'dataScope' => static::FIELD_PRICE_VALUE_TO,
                        'dataType' => Number::NAME,
                        'sortOrder' => $sortOrder,
                        'disabled' => true,
                        'validation' => [
                            'required-entry' => 'true',
                            'validate-greater-than-zero' => 'true'
                        ],
                        'valuesForEnable' => [
                            '2' => '2'
                        ],
                        'imports' => [
                            'toggleDisable' => '${$.parentName}.' . static::FIELD_PRICE_TYPE . ':value',
                            'handleChangeMax' => '${$.parentName}.' . static::FIELD_PRICE_VALUE_FROM . ':value'
                        ],
                        'addbefore' => '$'
                    ],
                ],
            ],
        ];
    }

    /**
     * Get example options as an option array:
     *      [
     *          label => string,
     *          value => option_id
     *      ]
     *
     * @return array
     */
    protected function _getOptions()
    {
        $options = [
            [
                'label' => __('Select'),
                'value' => self::CREDIT_TYPE_NONE
            ],
            [
                'label' => __('Fixed value'),
                'value' => self::CREDIT_TYPE_FIX
            ],
            [
                'label' => __('Range of values'),
                'value' => self::CREDIT_TYPE_RANGE
            ],
            [
                'label' => __('Dropdown values'),
                'value' => self::CREDIT_TYPE_DROPDOWN
            ]
        ];

        return $options;
    }
}

}