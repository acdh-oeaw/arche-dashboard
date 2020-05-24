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
		group by t_class.value, tp.property",
		"topcollections"=>"select rootids.rootid, min(m_title.value), count(rel.id), max(rel.n), sum(m_rawsize.value_n ),
				(select value_n from metadata where property = 'https://vocabs.acdh.oeaw.ac.at/schema#hasBinarySize' and id = rootids.rootid) bsize
				from (select DISTINCT(r.id) as rootid
					from metadata as m
					left join relations as r on r.id = m.id
					where
					m.property = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' 
					and m.value = 'https://vocabs.acdh.oeaw.ac.at/schema#Collection'
					and r.property != 'https://vocabs.acdh.oeaw.ac.at/schema#isPartOf'
					and r.id NOT IN ( 
						SELECT DISTINCT(r.id) from metadata as m left join relations as r on r.id = m.id
						where
							m.property = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' 
							and m.value = 'https://vocabs.acdh.oeaw.ac.at/schema#Collection'
							and r.property = 'https://vocabs.acdh.oeaw.ac.at/schema#isPartOf'
					)
				) as rootids, public.get_relatives(rootid,'https://vocabs.acdh.oeaw.ac.at/schema#isPartOf') rel
				left join metadata m_title on m_title.id = rel.id
					and m_title.property = 'https://vocabs.acdh.oeaw.ac.at/schema#hasTitle'
				left join metadata m_rawsize on m_rawsize.id = rel.id
					and m_rawsize.property = 'https://vocabs.acdh.oeaw.ac.at/schema#hasRawBinarySize'
				group by rootids.rootid"

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
    
    public function changeBackDBConnection()
    {
        \Drupal\Core\Database\Database::setActiveConnection();
    }
    
}
