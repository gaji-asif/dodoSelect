<?php

namespace App\Traits\Inventory;

trait PlatformTrait
{
    /**
     * Get the tag used to determine "platform no".
     */
    private function getTagForShopeePlatform()
    {
        return "shopee";
    }


    /**
     * Get the tag used to determine "platform no".
     */
    private function getTagForLazadaPlatform()
    {
        return "lazada";
    }


    /**
     * Get the tag used to determine "platform no".
     */
    private function getTagForWooCommercePlatform()
    {
        return "woo_commerce";
    }


    /**
     * Get platform form no.
     * "shopee" is 1,
     * "lazada" is 2,
     * "woo_commerce" is 3
     * 
     * NOTE:
     * This is done for checking in database.
     * 
     * @param string $for
     * @return integer
     */
    private function getPlatformNo($for="")
    {
        if (!empty($for)) {
            if ($for == $this->getTagForShopeePlatform()) {
                return 1;
            } else if ($for == $this->getTagForLazadaPlatform()) {
                return 2;
            } else if ($for == $this->getTagForWooCommercePlatform()) {
                return 3;
            }
        }
        return -1;
    }


    /**
     * Get platform name using platform no.
     * 
     * @param integer $num
     * @return string
     */
    private function getPlatformNoWisePlatformName($num="")
    {
        if ($num == 1) {
            return $this->getTagForShopeePlatform();
        } else if ($num == 2) {
            return $this->getTagForLazadaPlatform();
        } else if ($num == 3) {
            return $this->getTagForWooCommercePlatform();
        }
        return "";
    }
}