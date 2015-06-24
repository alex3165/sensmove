<?php

class WP_Installer_API{

    public static function get_product_installer_link($repository_id, $package_id = false){

        $menu_url = WP_Installer()->menu_url();

        $url = $menu_url . '#' . $repository_id;
        if($package_id){
            $url .= '/' . $package_id;
        }

        return $url;

    }

    public static function get_product_price($repository_id, $package_id, $product_id, $incl_discount = false){

        $price = WP_Installer()->get_product_price($repository_id, $package_id, $product_id, $incl_discount);

        return $price;
    }

}