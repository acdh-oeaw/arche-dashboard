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
            } elseif (strpos($names, 'http:/') === 0) {
                $names = str_replace('http:/', 'http://', $names);
            }
            
            $names = strtr(base64_encode($names), '+/=', '._-');
            return "/dashboard-property/$names";
        }
        
        if (strpos($path, '/dashboard-class-property/') === 0) {
            $names = preg_replace('|^\/dashboard-class-property\/|', '', $path);
            if (strpos($names, 'https:/') === 0) {
                $names = str_replace('https:/', 'https://', $names);
            } elseif (strpos($names, 'http:/') === 0) {
                $names = str_replace('http:/', 'http://', $names);
            }
            
            $names = strtr(base64_encode($names), '+/=', '._-');
            return "/dashboard-class-property/$names";
        }
        
        if (strpos($path, '/dashboard-format-property/') === 0) {
            $names = preg_replace('|^\/dashboard-format-property\/|', '', $path);
            if (strpos($names, 'https:/') === 0) {
                $names = str_replace('https:/', 'https://', $names);
            } elseif (strpos($names, 'http:/') === 0) {
                $names = str_replace('http:/', 'http://', $names);
            }
            
            $names = strtr(base64_encode($names), '+/=', '._-');
            return "/dashboard-format-property/$names";
        }
        
        if (strpos($path, '/dashboard-detail-api/') === 0) {
            $names = preg_replace('|^\/dashboard-detail-api\/|', '', $path);
            if (strpos($names, 'https:/') === 0) {
                $names = str_replace('https:/', 'https://', $names);
            } elseif (strpos($names, 'http:/') === 0) {
                $names = str_replace('http:/', 'http://', $names);
            }
            
            $names = strtr(base64_encode($names), '+/=', '._-');
            return "/dashboard-detail-api/$names";
        }
        
        if (strpos($path, '/dashboard-values-by-property-api/') === 0) {
            $names = preg_replace('|^\/dashboard-values-by-property-api\/|', '', $path);
            if (strpos($names, 'https:/') === 0) {
                $names = str_replace('https:/', 'https://', $names);
            } elseif (strpos($names, 'http:/') === 0) {
                $names = str_replace('http:/', 'http://', $names);
            }
            
            $names = strtr(base64_encode($names), '+/=', '._-');
            return "/dashboard-values-by-property-api/$names";
        }
        
        return $path;
    }
}
