<?php
/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace Magestore\Webpos\Api\Data\Directory\Country;

interface CountryResultInterface
{
    /**
     * Set countries list.
     *
     * @api
     * @param anyType $items
     * @return $this
     */
    public function setItems(array $items);

    /**
     * Get countries list.
     *
     * @api
     * @return anyType
     */
    public function getItems();

    /**
     * Set total count
     *
     * @param int $count
     * @return $this
     */
    public function setTotalCount($count);

    /**
     * Get total count
     *
     * @return int
     */
    public function getTotalCount();

}
