<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Gsoft\Webpos\Api;

/**
 * Tools interface.
 * @api
 */
interface ReservationsInterface
{


    /**
     * get reservations
     *
     * @param string $source
     * * @param int $page
     * * @param int $limit
     * @param int $notaccepted
     * @return mixed
     */
    public function reservations($source, $page=0, $limit=20, $notaccepted=0);

    /**
     * get reservations
     *
     * @param int $id
     * * @param string $name
     * @return mixed
     */
    public function acceptReservation($id,$name);

}
