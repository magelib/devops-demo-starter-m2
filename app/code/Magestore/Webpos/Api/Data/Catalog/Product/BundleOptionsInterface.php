<?php

/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace Magestore\Webpos\Api\Data\Catalog\Product;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface ConfigOptionsInterface
 */
interface BundleOptionsInterface extends ExtensibleDataInterface
{
    /**#@+
     * Config Options object data keys
     */

    const KEY_CONFIG_OPTIONS = 'bundle_options';

    /**
     * Gets product items of bundle options
     *
     * @return \Magestore\Webpos\Api\Data\Catalog\Product\BundleOptionsInterface
     */
    public function getBundleOptions();

    /**
     * Sets product items of bundle options
     *
     * @param \Magestore\Webpos\Api\Data\Catalog\Product\BundleOptionsInterface $bundleOptions
     * @return $this
     */
    public function setBundleOptions(\Magestore\Webpos\Api\Data\Catalog\Product\BundleOptionsInterface $bundleOptions);
}
