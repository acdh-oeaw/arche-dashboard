<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Drupal\arche_dashboard\Helper;

/**
 * Description of DashboardHelper
 *
 * @author nczirjak
 */
class DashboardHelper
{
    
    /**
     * This function handle the # removing problem in the browser
     *
     * @param array $data
     * @return array
    */
    public function generatePropertyUrl(array &$data): array
    {
        foreach ($data as $k => $v) {
            if (isset($v->property)) {
                if (strpos($v->property, "#") !== false) {
                    $data[$k]->property = str_replace("#", "%23", $v->property);
                }
            }
        }
        return $data;
    }
}
