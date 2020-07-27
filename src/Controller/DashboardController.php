<?php

namespace Drupal\arche_dashboard\Controller;

use Drupal\Core\Controller\ControllerBase;

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
        if($key == 'properties') {
            $data = $this->generatePropertyUrl($data);
        }
        
        // print_r ($cols); 
        return  [
            '#theme' => 'arche-dashboard-table',
            '#basic' => $data,
	    '#key' => $key,
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
        // print_r ($cols); 
        return  [
            '#theme' => 'arche-dashboard-table',
            '#basic' => $data,
	    '#key' => $property,
	    '#cols' => $cols,
            '#cache' => ['max-age' => 0]
	];
   }
 
    public function dashboard_overview(): array {
        
        return  [
            '#theme' => 'arche-dashboard-overview',
            '#cache' => ['max-age' => 0]
	];
   } 
    
    public function generateView($key): array {
        
        //get the data from the DB
        $this->data = $this->model->getViewData($key);
        //pass the DB result to the Object generate functions
        
        return $this->data;
    }

    public function generateHeaders($key): array {
        
        return $this->model->getHeaders($key);
    }
}
