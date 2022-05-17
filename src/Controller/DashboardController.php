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

    public function __construct()
    {
        $this->config = drupal_get_path('module', 'acdh_repo_gui') . '/config/config.yaml';
        $this->repo = \acdhOeaw\arche\lib\Repo::factory($this->config);
        //setup the dashboard model class
        $this->model = new \Drupal\arche_dashboard\Model\DashboardModel();
        $this->helper = new \Drupal\arche_dashboard\Helper\DashboardHelper();
    }

    /**
     * Dashboard property count view
     *
     * @return array
     */
    public function dashboard_detail(string $key = "properties"): array
    {
        //generate the view
        $data = $this->generateView($key);

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

        return [
            '#theme' => 'arche-dashboard-table',
            '#basic' => $data,
            '#key' => $key,
            '#cols' => $cols,
            '#detailPageUrl' => $detailPageUrl,
            '#cache' => ['max-age' => 0]
        ];
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
    public function generateView($key): array
    {

        //get the data from the DB
        $this->data = $this->model->getViewData($key);
        //pass the DB result to the Object generate functions

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
        /*
        
        return [
            '#theme' => 'arche-dashboard-table',
            '#basic' => $data,
            '#key' => $key,
            '#cols' => $cols,
            '#detailPageUrl' => $detailPageUrl,
            '#cache' => ['max-age' => 0]
        ];*/
    }
    
    
    public function getValuesByProperty() {
        
        $data = $this->model->getViewData();
        $data = $this->helper->generatePropertyUrl($data);
        return [
            '#theme' => 'arche-dashboard-values-by-property',
            '#data' => $data,
            '#cache' => ['max-age' => 0]
        ];
        
    }
    
    public function getValuesByPropertyApi(string $property): Response
    {
        $offset = (empty($_POST['start'])) ? 0 : $_POST['start'];
        $limit = (empty($_POST['length'])) ? 10 : $_POST['length'];
        $draw = (empty($_POST['draw'])) ? 0 : $_POST['draw'];
        $search = (empty($_POST['search']['value'])) ? "" : $_POST['search']['value'];
       
        $data = array();
        $data = $this->model->getValuesByPropertyApiData($property, $offset, $limit, $search);
      
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
