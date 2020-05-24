<?php

namespace Drupal\arche_dashboard\Model;

/**
 * Description of DashboardModel
 *
 * @author norbertczirjak
 */
class DashboardModel  {
    
    private $repodb;

    private $queries=array("properties"=> "
            SELECT
                property, count(*) as cnt
            from public.metadata
            group by property",
            "classes"=>"select value as class, count(*) as cnt
		from public.metadata
		where property = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type'
		group by value",
            "classesproperties"=>"select t_class.value as class, tp.property, count(distinct tp.value) as cnt_distinct_value, count(*) as cnt
		from 
			(select id, value
			from public.metadata 
			where property = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type'
			) t_class 
		inner join public.metadata tp on t_class.id =tp.id
		group by t_class.value, tp.property"
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
    public function getViewData($key="properties"): array {
     
      if(array_key_exists($key, $this->queries)) {
	$queryStr = $this->queries[$key];
      } else { # default query, but better to return empty result, or error message
        $queryStr = "
            SELECT 
                property as key, count(*) as cnt
            from public.metadata 
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
    public function getFacet($property): array {
     
        $queryStr = "
            SELECT 
                value as key, count(*) as cnt
            from public.metadata 
		where property = '" & $property & "'" &
		" group by value ";
      
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
 
    public function changeBackDBConnection()
    {
        \Drupal\Core\Database\Database::setActiveConnection();
    }
    
}
