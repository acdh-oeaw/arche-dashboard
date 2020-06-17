<?php

namespace Drupal\arche_dashboard\PathProcessor;

use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Symfony\Component\HttpFoundation\Request;

class ArcheDashboardPathProcessor implements InboundPathProcessorInterface
{
    public function processInbound($path, Request $request)
    {
        
        if (strpos($path, '/dashboard-property/') === 0) {
            $names = preg_replace('|^\/dashboard-property\/|', '', $path);
            if (strpos($names, 'https:/') === 0) {
                $names = str_replace('https:/', 'https://', $names);
            } else if (strpos($names, 'http:/') === 0) {
                $names = str_replace('http:/', 'http://', $names);
            }
            
            $names = strtr(base64_encode($names), '+/=', '._-');
            return "/dashboard-property/$names";
        }
        
        return $path;
    }
    
}
