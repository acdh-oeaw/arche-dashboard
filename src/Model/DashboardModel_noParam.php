<?php

namespace Drupal\arche_dashboard\Model;

/**
 * Description of DashboardModel
 *
 * @author norbertczirjak
 */
class DashboardModel extends ArcheModel {
    
    private $repodb;

    private $queries=array("properties"=> "
            SELECT
                property, count(*) as cnt
            from public.metadata
            group by property",
            "classes"=>"select value, count(*) as cnt
		from public.metadata
		where property = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type'
		group by value",
            "classesproperties"=>"select t_class.value as cl, tp.property, count(distinct tp.value) as cnt_distinct_value, count(*) as cnt
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
        (isset($_SESSION['language'])) ? $this->siteLang = strtolower($_SESSION['language'])  : $this->siteLang = "en";
    }
    
    /**
     * Generate the sql data
     * @return array
    */
    public function getViewData(): array {
     
        $queryStr = "
            SELECT 
                property, count(*) as cnt
            from public.metadata 
            group by property";

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
    
}
