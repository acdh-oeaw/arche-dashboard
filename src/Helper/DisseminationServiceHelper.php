<?php

namespace Drupal\arche_dashboard\Helper;

/**
 * Description of DisseminationServiceHelper
 *
 * @author nczirjak
 */
class DisseminationServiceHelper
{
    use \Drupal\arche_dashboard\Traits\DisseminationServiceTrait;
    
    // <editor-fold defaultstate="collapsed" desc="getter">
    
    private function getUri(object &$v): string
    {
        if ($v->getGraph()->getUri()) {
            return $v->getGraph()->getUri();
        }
        return "";
    }

    // </editor-fold>

    public function getDissServResourcesById(int $dissId): object
    {
        return $this->getDisseminationServicesById((int)$dissId);
    }
    
    /**
     * get the available dissemination services
     * @return array
    */
    public function getDissServices(): array
    {
        return $this->getDisseminationServices();
    }
}
