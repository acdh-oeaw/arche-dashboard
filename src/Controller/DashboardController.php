<?php

namespace Drupal\arche_dashboard\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class DashboardController extends ControllerBase
{
    private $data = array();
    private $repo;
    private $model;
    private $helper;
    private static $cacheTypes = ['formatspercollection'];

    public function __construct()
    {
        $this->config = drupal_get_path('module', 'acdh_repo_gui') . '/config/config.yaml';
        $this->repo = \acdhOeaw\arche\lib\Repo::factory($this->config);
        //setup the dashboard model class
        $this->model = new \Drupal\arche_dashboard\Model\DashboardModel();
        $this->helper = new \Drupal\arche_dashboard\Helper\DashboardHelper();
    }
    
    /**
     * AJAX related table main template - latest
     * @param string $key
     * @return array
     */
    public function dashboardDetailAjax(string $key = "properties"): array
    {
        return [
            '#theme' => 'arche-dashboard-ajax',
            '#key' => $key,
            '#cache' => ['max-age' => 0],
            '#attached' => [
                'library' => [
                    'arche_dashboard/arche-ds-detailajax-css-and-js',
                ]
            ]
        ];
    }
    
    /**
     * Ajax related table main API call - latest
     * @param string $key
     * @return Response
     */
    public function dashboardDetailAjaxApi(string $key): Response
    {
        $offset = (empty($_POST['start'])) ? 0 : $_POST['start'];
        $limit = (empty($_POST['length'])) ? 10 : $_POST['length'];
        $draw = (empty($_POST['draw'])) ? 0 : $_POST['draw'];
        $search = (empty($_POST['search']['value'])) ? "" : $_POST['search']['value'];
        //datatable start columns from 0 but in db we have to start it from 1
        $orderby = (empty($_POST['order'][0]['column'])) ? 1 : (int)$_POST['order'][0]['column'] + 1;
        $order = (empty($_POST['order'][0]['dir'])) ? 'asc' : $_POST['order'][0]['dir'];
        
        $data = array();
        /*
        if(in_array($key, $this::$cacheTypes)) {
            $lastmodify = $this->model->getDBLastModificationDate();
            $cfPath = $this->helper->getCachedFilePath();
            $cf = new \Drupal\arche_dashboard\Object\CacheFile($cfPath, $key.'.json');
            $dbCall = false;

            if(!$cf->checkFileExists() || $cf->getSize() === 0 ) {
                $dbCall = true;
            }

            if($cf->compareDates($lastmodify)) {
                $dbCall = true;
            }

            if($dbCall) {
                $data = $this->generateView($key, 0, 10000, $search, $orderby, $order);
                $cf->addContent(json_encode($data));
            }
            $data = json_decode($cf->getJsonContent(), true);

        } else {
            $data = $this->generateView($key, $offset, $limit, $search, $orderby, $order);
        }
        */
        $data = $this->generateView($key, $offset, $limit, $search, $orderby, $order);
       
        $cols = [];
        if (count($data) > 0) {
            $cols = array_keys((array)$data[0]);
        }
   
        
        $response = new Response();
        $response->setContent(
            json_encode(
                array(
                    "aaData" => $data,
                    "iTotalRecords" => ($data[0]->sumcount) ?  $data[0]->sumcount : 0,
                    "iTotalDisplayRecords" => ($data[0]->sumcount) ?  $data[0]->sumcount : 0,
                    "draw" => intval($draw),
                    "cols" =>  $cols
                )
            )
        );
        $response->headers->set('Content-Type', 'application/json');
        
        return $response;
    }
    
    public function dashboard_format_property_detail(string $property): array
    {
        $property = base64_decode($property);
        $data = $this->model->getFacetDetail('https://vocabs.acdh.oeaw.ac.at/schema#hasFormat', $property);

        if (count($data) > 0) {
            $cols = get_object_vars($data[0]);
        } else {
            $cols = array();
        }

        // print_r ($cols);
        return [
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
    public function dashboard_class_property_detail(string $property): array
    {
        $property = base64_decode($property);
        $data = $this->model->getFacetDetail('http://www.w3.org/1999/02/22-rdf-syntax-ns#type', $property);

        if (count($data) > 0) {
            $cols = get_object_vars($data[0]);
        } else {
            $cols = array();
        }

        // print_r ($cols);
        return [
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
    public function dashboard_property_detail(string $property): array
    {
        $property = base64_decode($property);
        $data = $this->model->getFacet($property);

        if (count($data) > 0) {
            $cols = get_object_vars($data[0]);
        } else {
            $cols = array();
        }
      
        return [
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
    public function dashboard_overview(): array
    {
        return [
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
    public function generateView(string $key, int $offset = 0, int $limit = 10, string $search = "", int $orderby = 1, string $order = "asc"): array
    {

        //get the data from the DB
        $this->data = $this->model->getViewData($key, $offset, $limit, $search, $orderby, $order);
        //pass the DB result to the Object generate functions
        $this->data = $this->helper->addUrlToTableData($this->data, $key);
        return $this->data;
    }

    public function generateHeaders($key): array
    {
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
        $property = str_replace('/' . $value, '', $property);

       
        $data = $this->model->getFacetDetail($property, $value);

        if (count($data) > 0) {
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
        return [
            '#theme' => 'arche-dashboard-disserv-table',
            '#cache' => ['max-age' => 0]
        ];
    }

    /**
     * Dissemination services list api call for the datatable
     * @return Response
     */
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
    
    /**
     * Dissemination service detail page with the basic infos
     * @param string $id
     * @return type
     */
    public function dashboard_dissemination_services_detail(string $id)
    {
        $disservHelper = new \Drupal\arche_dashboard\Helper\DisseminationServiceHelper();
        $data = $disservHelper->getDissServResourcesById((int)$id);
      
        return [
            '#theme' => 'arche-dashboard-disserv-detail',
            '#data' => $data,
            '#cache' => ['max-age' => 0]
        ];
    }
    
    /**
     * The matching resource api call for the dissemination service detail datatable
     * @param string $id
     * @param int $limit
     * @param int $offset
     * @return Response
     */
    public function getDisseminationServiceMatchingResourcesApi(string $id): Response
    {
        $offset = (empty($_POST['start'])) ? 0 : $_POST['start'];
        $limit = (empty($_POST['length'])) ? 10 : $_POST['length'];
        $draw = (empty($_POST['draw'])) ? 0 : $_POST['draw'];
        
        $data = array();
        $disservHelper = new \Drupal\arche_dashboard\Helper\DisseminationServiceHelper();
        $data = $disservHelper->getDissServResourcesById((int)$id);
        $matching = $data->getMatchingResources((int)$limit, (int)$offset);
       
        $response = new Response();
        $response->setContent(
            json_encode(
                array(
                    "aaData" => $matching,
                    "iTotalRecords" => $data->getCount(),
                    "iTotalDisplayRecords" => $data->getCount(),
                    "draw" => intval($draw),
                )
            )
        );
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    
   
    /**
     *
     * @param string $key
     * @return array
     */
    public function dashboard_detail_api(string $key = "properties"): array
    {
        $offset = (empty($_POST['start'])) ? 0 : $_POST['start'];
        $limit = (empty($_POST['length'])) ? 10 : $_POST['length'];
        $draw = (empty($_POST['draw'])) ? 0 : $_POST['draw'];
        
        //generate the view
        $data = $this->generateView($key, (int)$limit, (int)$offset);

        if (count($data) > 0) {
            $cols = get_object_vars($data[0]);
        } else {
            $cols = array();
        }

        /* if the key is the properties then we need to change the # in the url */
        if ($key == 'properties' || $key == 'classes' || $key == 'classesproperties') {
            $data = $this->helper->generatePropertyUrl($data);
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

        
        $response = new Response();
        $response->setContent(
            json_encode(
                array(
                    "aaData" => $matching,
                    "iTotalRecords" => $data->getCount(),
                    "iTotalDisplayRecords" => $data->getCount(),
                    "draw" => intval($draw),
                )
            )
        );
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    
    
    /**
     *
     * @return type
     */
    public function getValuesByProperty()
    {
        $data = $this->model->getValuesByProperty();
        $data = $this->helper->generatePropertyUrl($data);
      
        return [
            '#theme' => 'arche-dashboard-values-by-property',
            '#data' => $data,
            '#cache' => ['max-age' => 0]
        ];
    }
    
    
    /**
     *
     * @param string $property
     * @return Response
     */
    public function getValuesByPropertyApi(string $property): Response
    {
        $offset = (empty($_POST['start'])) ? 0 : $_POST['start'];
        $limit = (empty($_POST['length'])) ? 10 : $_POST['length'];
        $draw = (empty($_POST['draw'])) ? 0 : $_POST['draw'];
        $search = (empty($_POST['search']['value'])) ? "" : $_POST['search']['value'];
        //datatable start columns from 0 but in db we have to start it from 1
        $orderby = (empty($_POST['order'][0]['column'])) ? 1 : (int)$_POST['order'][0]['column'] + 1;
        $order = (empty($_POST['order'][0]['dir'])) ? 'asc' : $_POST['order'][0]['dir'];
        $data = array();
        
        
        
        
        $data = $this->model->getValuesByPropertyApiData($property, $offset, $limit, $search, $orderby, $order);
      
        $response = new Response();
        $response->setContent(
            json_encode(
                array(
                    "aaData" => $data,
                    "iTotalRecords" => ($data[0]->sumcount) ?  $data[0]->sumcount : 0,
                    "iTotalDisplayRecords" => ($data[0]->sumcount) ?  $data[0]->sumcount : 0,
                    "draw" => intval($draw),
                )
            )
        );
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    
    /**
     * The properties menu template generation
     *
     * @param string $property
     * @return type
     */
    public function getProperty(string $property)
    {
        $property = base64_decode($property);
      
        return [
            '#theme' => 'arche-dashboard-property',
            '#property' => str_replace("#", "%23", $property),
            '#propertyTitle' => $property,
            '#cache' => ['max-age' => 0],
            '#attached' => [
                'library' => [
                    'arche_dashboard/arche-ds-property-css-and-js',
                ]
            ]
        ];
    }
    
    /**
     * The properties menu API call backend for the table data generation
     *
     * @param string $property
     * @return Response
     */
    public function getPropertyApi(string $property): Response
    {
        $offset = (empty($_POST['start'])) ? 0 : $_POST['start'];
        $limit = (empty($_POST['length'])) ? 10 : $_POST['length'];
        $draw = (empty($_POST['draw'])) ? 0 : $_POST['draw'];
        $search = (empty($_POST['search']['value'])) ? "" : $_POST['search']['value'];
        //datatable start columns from 0 but in db we have to start it from 1
        $orderby = (empty($_POST['order'][0]['column'])) ? 1 : (int)$_POST['order'][0]['column'] + 1;
        $order = (empty($_POST['order'][0]['dir'])) ? 'asc' : $_POST['order'][0]['dir'];
        $data = array();
        
        $data = $this->model->getPropertyApi($property, $offset, $limit, $search, $orderby, $order);
     
        $response = new Response();
        $response->setContent(
            json_encode(
                array(
                    "aaData" => $data,
                    "iTotalRecords" => ($data[0]->sumcount) ?  $data[0]->sumcount : 0,
                    "iTotalDisplayRecords" => ($data[0]->sumcount) ?  $data[0]->sumcount : 0,
                    "draw" => intval($draw),
                )
            )
        );
        $response->headers->set('Content-Type', 'application/json');
        
        return $response;
    }
}
