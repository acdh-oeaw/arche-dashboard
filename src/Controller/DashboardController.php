<?php

namespace Drupal\arche_dashboard\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class DashboardController extends ControllerBase {
    
    private $data = array();
    private $repo;
    private $model;
    
    public function __construct() {
        $this->config = drupal_get_path('module', 'acdh_repo_gui').'/config/config.yaml';
        $this->repo = \acdhOeaw\acdhRepoLib\Repo::factory($this->config);
        //setup the dashboard model class
        $this->model = new \Drupal\arche_dashboard\Model\DashboardModel();
    }
    
     /**
     * This function handle the # removing problem in the browser
     * 
     * @param array $data
     * @return array
     */
    private function generatePropertyUrl(array $data): array {
        foreach($data as $k => $v) {
            if(isset($v->property)) {
                if (strpos($v->property, "#") !== false) {
                    $data[$k]->property = str_replace("#", "%23", $v->property);
                }
            }
        }
        return $data;
    }
    
     /**
     * Dashboard property count view
     * 
     * @return array
     */
    public function dashboard_detail(string $key="properties"): array {
        //generate the view
        $data = $this->generateView($key);
       
	if (count($data) > 0 ) {
		$cols = get_object_vars($data[0]);
	} else {
		$cols = array();
	}
       
        /* if the key is the properties then we need to change the # in the url */
        if($key == 'properties' || $key == 'classes' || $key == 'classesproperties') {
            $data = $this->generatePropertyUrl($data);
        }
        
        switch ($key) {
            case 'classes':
                $detailPageUrl = 'dashboard-class-property';
                break;
            case 'formats':
                $detailPageUrl = 'dashboard-format-property';
                break;

            default:
                $detailPageUrl = 'dashboard-property';
                break;
        }
        
        return  [
            '#theme' => 'arche-dashboard-table',
            '#basic' => $data,
	    '#key' => $key,
	    '#cols' => $cols,
            '#detailPageUrl' => $detailPageUrl,
            '#cache' => ['max-age' => 0]
        ]; 
    }
    
    
    public function dashboard_format_property_detail(string $property): array {
        $property = base64_decode($property);
        $data = $this->model->getFacetDetail('https://vocabs.acdh.oeaw.ac.at/schema#hasFormat', $property);
        
        if (count($data) > 0 ) {
		$cols = get_object_vars($data[0]);
	} else {
		$cols = array();
	}
        
        // print_r ($cols); 
        return  [
            '#theme' => 'arche-dashboard-table',
            '#basic' => $data,
	    '#key' => $property,
	    '#cols' => $cols,
            '#cache' => ['max-age' => 0]
	];
    }
    
    /**
     * The rdf:type class properties detail view
     * 
     * @param string $property
     * @return array
     */
    public function dashboard_class_property_detail(string $property): array {
        $property = base64_decode($property);
        $data = $this->model->getFacetDetail('http://www.w3.org/1999/02/22-rdf-syntax-ns#type', $property);
        
        if (count($data) > 0 ) {
		$cols = get_object_vars($data[0]);
	} else {
		$cols = array();
	}
        
        // print_r ($cols); 
        return  [
            '#theme' => 'arche-dashboard-table',
            '#basic' => $data,
	    '#key' => $property,
	    '#cols' => $cols,
            '#cache' => ['max-age' => 0]
	];
    }
    
     /**
     * Dashboard property count distinct values  view
     * 
     * @return array
     */
    public function dashboard_property_detail(string $property): array {
        
        $property = base64_decode($property);
        $data = $this->model->getFacet($property);
        
        if (count($data) > 0 ) {
		$cols = get_object_vars($data[0]);
	} else {
		$cols = array();
	}
         
        return  [
            '#theme' => 'arche-dashboard-table',
            '#basic' => $data,
	    '#key' => $property,
	    '#cols' => $cols,
            '#cache' => ['max-age' => 0]
	];
   }
 
   
    /**
     * The Dashboard Main Menu View
     * 
     * @return array
     */
    public function dashboard_overview(): array {
        
        return  [
            '#theme' => 'arche-dashboard-overview',
            '#cache' => ['max-age' => 0]
	];
    } 
    
    /**
     * The basic view generation function, which will handle the sql queries based 
     * on the passed property
     * 
     * @param type $key
     * @return array
     */
    public function generateView($key): array {
        
        //get the data from the DB
        $this->data = $this->model->getViewData($key);
        //pass the DB result to the Object generate functions
        
        return $this->data;
    }

    public function generateHeaders($key): array {
        
        return $this->model->getHeaders($key);
    }
    
    /**
     * The properties deatil view
     * 
     * @param string $property
     * @return Response
     */
    public function dashboard_property_detail_api(string $property): Response
    {
        
        $property = base64_decode($property);
        //get the value the value after the last /
        $value = substr($property, strrpos($property, '/') + 1);
        $property = str_replace('/'.$value, '', $property);
        
        $data = $this->model->getFacetDetail($property, $value);
        
        if (count($data) > 0 ) {
		$cols = get_object_vars($data[0]);
	} else {
		$cols = array();
	}
        
        $build = [
            '#theme' => 'arche-dashboard-table-detail',
            '#basic' => $data,
	    '#key' => $property,
            '#keyValue' => $value,
	    '#cols' => $cols,
            '#cache' => ['max-age' => 0]
        ];
        
        return new Response(render($build));
    }
    
    /**
     * The properties deatil view
     * 
     * @param string $property
     * @return Response
     */
    public function dashboard_dissemination_services_list()
    {
        $disservHelper = new \Drupal\arche_dashboard\Helper\DisseminationServiceHelper();
        $data = $disservHelper->getDissServices();
        return  [
            '#theme' => 'arche-dashboard-disserv-table',
            '#cache' => ['max-age' => 0]
        ];
    }
    
    public function getDisseminationServiceApi(): Response 
    {
        $data = array();
        
        $disservHelper = new \Drupal\arche_dashboard\Helper\DisseminationServiceHelper();
        $data = $disservHelper->getDissServices();
      
        $response = new Response();
        $response->setContent(json_encode(array("data" => $data)));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}

