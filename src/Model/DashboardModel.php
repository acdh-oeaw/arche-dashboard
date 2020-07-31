<?php

namespace Drupal\arche_dashboard\Model;

/**
 * Description of DashboardModel
 *
 * @author norbertczirjak
 */
class DashboardModel  {
    
    private $repodb;

    private $queries = array(
        "properties"            =>  "SELECT * FROM gui.dash_properties_func();",
        "classes"               =>  "SELECT * FROM gui.dash_classes_func();",
        "classesproperties"     =>  "SELECT * FROM gui.dash_classes_properties_func();",        
        "topcollections"        =>  "SELECT * FROM gui.dash_topcollections_func();",        
        "formats"               =>  "SELECT * FROM gui.dash_formats_func();",
	"formatspercollection"  =>  "SELECT * FROM gui.dash_formatspercollection_func();"
    );
   
    public function __construct() {
        //set up the DB connections
        \Drupal\Core\Database\Database::setActiveConnection('repo');
        $this->repodb = \Drupal\Core\Database\Database::getConnection('repo');
    }
    
    /**
     * Generate the sql data
     * @return array
    */
    public function getViewData(string $key="properties"): array {
     
      if(array_key_exists($key, $this->queries)) {
	$queryStr = $this->queries[$key];
      } else { # default query, but better to return empty result, or error message
        $queryStr = "
            SELECT 
                property as key, count(*) as cnt
            from public.metadata_view 
            group by property";
      }
        try {
            $query = $this->repodb->query($queryStr);
            $return = $query->fetchAll();
            
            $this->changeBackDBConnection();
            return $return;
        } catch (Exception $ex) {
            \Drupal::logger('arche_dashboard')->notice($ex->getMessage());
            return array();
        } catch (\Drupal\Core\Database\DatabaseExceptionWrapper $ex) {
            \Drupal::logger('arche_dashboard')->notice($ex->getMessage());
            return array();
        }
        
    }
   
    
    /**
     * Retrieve the data: faceting: distinct values of a property
     * @return array
    */
    public function getFacet(string $property): array {
      
        try {
            
           $query = $this->repodb->query(
                "SELECT * FROM gui.dash_get_facet_func(:property);
                ",
                array(
                    ':property' => $property
                )
            );
            $return = $query->fetchAll();
            
            $this->changeBackDBConnection();
            return $return;
        } catch (Exception $ex) {
            \Drupal::logger('arche_dashboard')->notice($ex->getMessage());
            return array();
        } catch (\Drupal\Core\Database\DatabaseExceptionWrapper $ex) {
            \Drupal::logger('arche_dashboard')->notice($ex->getMessage());
            return array();
        }
    }  
 
    public function changeBackDBConnection()
    {
        \Drupal\Core\Database\Database::setActiveConnection();
    }
    
}
