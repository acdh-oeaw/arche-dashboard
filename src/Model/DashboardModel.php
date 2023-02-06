<?php

namespace Drupal\arche_dashboard\Model;

/**
 * Description of DashboardModel
 *
 * @author norbertczirjak
 */
class DashboardModel
{
    private $repodb;

    private $queries = array(
        "properties"            =>  "SELECT * FROM gui.dash_properties_func() ",
        "classes"               =>  "SELECT * FROM gui.dash_classes_func() ",
        "classesproperties"     =>  "SELECT * FROM gui.dash_classes_properties_func() ",
        "topcollections"        =>  "SELECT * FROM gui.dash_topcollections_func() ",
        "formats"               =>  "SELECT * FROM gui.dash_formats_func() ",
        "formatspercollection"  =>  "SELECT * FROM gui.dash_formatspercollection_func() "
    );
    
    private static $queryKeys = array(
        "properties"            =>  ["property"],
        "classes"               =>  ["class"],
        "classesproperties"     =>  ["class", "property"],
        "topcollections"        =>  ["title"],
        "formats"               =>  ["format"],
        "formatspercollection"  =>  ["title", "type", "format"]
    );
   
    public function __construct()
    {
        //set up the DB connections
        \Drupal\Core\Database\Database::setActiveConnection('repo');
        $this->repodb = \Drupal\Core\Database\Database::getConnection('repo');
    }
    
    /**
     * Get all of the properties for the dropdown menu
     * @return array
     */
    public function getValuesByProperty(): array
    {
        try {
            $query = $this->repodb->query(
                " SELECT 
                property, count(*) as cnt
            from public.metadata_view 
            group by property order by property;"
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
    
    
    
    /**
     * Get the ACDH RDf:TYPE-s for the dropdown list
     * @return array
     */
    public function getAcdhTypes(): array
    {
        try {
            $query = $this->repodb->query(
                "SELECT 
                    DISTINCT(value)
                from public.metadata_view 
                where property = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type'
                order by value;"
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
    
    /**
     * Generate the sql data
     * @param string $key
     * @return array
     */
    public function getViewData(string $key, int $offset, int $limit, string $search, int $orderby, string $order): array
    {
        if (array_key_exists($key, $this->queries)) {
            $queryStr = $this->queries[$key];
        } else { # default query, but better to return empty result, or error message
            $queryStr = "
            SELECT 
                property as key, count(*) as cnt
            from public.metadata_view 
            group by property order by property;";
        }
        
        try {
            $searchText = (!empty($search)) ? "WHERE ".$this->setUpSearch($this::$queryKeys[$key], $search) : "";

            $query = $this->repodb->query(
                $queryStr." ".$searchText
                    . "order by $orderby $order "
                    . " limit :limit offset :offset;",
                array(
                    
                     ':limit' => $limit,
                    ':offset' => $offset,
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
       
    /**
     * We have to setup the sql for the datatable search fields
     * @param array $keys
     * @param string $value
     * @return string
     */
    private function setUpSearch(array $keys, string $value): string
    {
        $str = "";
        
        $count = count($keys);
        $i = 0;
        foreach ($keys as $k) {
            $str .= "(LOWER($k) LIKE LOWER('%$value%'))";
            if ($count > 1) {
                $str .=  ' OR ';
                $count--;
            }
        }
        return $str;
    }
    
    /**
     * Retrieve the data: faceting: distinct values of a property
     * @param string $property
     * @return array
     */
    public function getFacet(string $property): array
    {
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
    
    /**
     * Retrieve the faceting detail data
     * @param string $property
     * @param string $value
     * @return array
     */
    public function getFacetDetail(string $property, string $value): array
    {
        try {
            $query = $this->repodb->query(
                "select mv.id, 
                (select mv2.value from metadata_view as mv2 where mv2.id = mv.id and mv2.property = 'https://vocabs.acdh.oeaw.ac.at/schema#hasTitle' limit 1) as title,
                (select mv2.value from metadata_view as mv2 where mv2.id = mv.id and mv2.property = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' limit 1) as type
                from metadata_view as mv
                where 
                mv.value = :value and mv.property = :property;
                ",
                array(
                    ':value' => $value,
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
    
    
    /**
     * Count the selected dissemination service matching resources
     * @param object $sql
     * @return int
     */
    public function countAllMatchingResourcesForDisseminationService(object $sql): int
    {
        try {
            $query = $this->repodb->query(
                $sql->query,
                $sql->param
            );
            
            $return = $query->fetchObject();
            $this->changeBackDBConnection();
            return (int)$return->count;
        } catch (Exception $ex) {
            \Drupal::logger('arche_dashboard')->notice($ex->getMessage());
            return 0;
        } catch (\Drupal\Core\Database\DatabaseExceptionWrapper $ex) {
            \Drupal::logger('arche_dashboard')->notice($ex->getMessage());
            return 0;
        }
    }
    
    /**
     * SQL for the property menu table
     *
     * @param string $property
     * @param int $offset
     * @param int $limit
     * @param string $search
     * @param int $orderby
     * @param string $order
     * @return array
     */
    public function getPropertyApi(string $property, int $offset, int $limit, string $search = "", int $orderby = 1, string $order = 'asc'): array
    {
        $property = str_replace(':', '/', $property);
        $property = str_replace('//', '://', $property);
        
        try {
            $query = $this->repodb->query(
                "select  * from gui.dash_get_facet_func(:property) where LOWER(key) like  LOWER('%' || :search || '%') "
                    . "order by $orderby $order "
                    . " limit :limit offset :offset;",
                array(
                    ':property' => $property,
                    ':limit' => $limit,
                    ':offset' => $offset,
                    ':search' => $search
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
 
    /**
     * Get the values by property data
     * @param string $property
     * @return array
     */
    public function getValuesByPropertyApiData(string $property, array $types, int $offset, int $limit, string $search = "", int $orderby = 1, string $order = 'asc'): array
    {
        $typeStr = $this->formatTypeArray($types);
        
        try {
            $query = $this->repodb->query(
                "select * from gui.dash_get_facet_by_property_func(:property, $typeStr) where LOWER(key) like  LOWER('%' || :search || '%') "
                    . "order by $orderby $order "
                    . " limit :limit offset :offset;",
                array(
                    ':property' => $property,
                    ':limit' => $limit,
                    ':offset' => $offset,
                    ':search' => $search
                ),
                ['allow_delimiter_in_query' => true, 'allow_square_brackets' => true]
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
    
    
    private function formatTypeArray(array $types): string
    {
        $typeStr = 'ARRAY [ ';
        $count = count($types);
        $i = 0;
        foreach ($types as $t) {
            $typeStr .= "'$t'";
            if ($count - 1 != $i) {
                $typeStr .= ', ';
            } else {
                $typeStr .= ' ]';
            }
            $i++;
        }
        return $typeStr;
    }
    
    /**
     * Get the latest modification date
     * @return string
     */
    public function getDBLastModificationDate(): string
    {
        try {
            $query = $this->repodb->query(
                "select MAX(date) from public.metadata_history"
            );
            
            $return = $query->fetchObject();

            $this->changeBackDBConnection();
            return $return->max;
        } catch (Exception $ex) {
            \Drupal::logger('arche_dashboard')->notice($ex->getMessage());
            return "";
        } catch (\Drupal\Core\Database\DatabaseExceptionWrapper $ex) {
            \Drupal::logger('arche_dashboard')->notice($ex->getMessage());
            return "";
        }
    }
    
    
    public function getValuesByPropertyDetailData(array $params, int $offset, int $limit, string $search = "", int $orderby = 1, string $order = 'asc'): array
    {
        try {
            $query = $this->repodb->query(
                "WITH query_data as (
                    select mv2.id, 
                    COALESCE(
                    (select mv3.value from metadata_view as mv3 where mv3.id = mv2.id  and mv3.property = 'https://vocabs.acdh.oeaw.ac.at/schema#hasTitle' and mv3.lang = 'en' LIMIT 1),
                    (select mv3.value from metadata_view as mv3 where mv3.id = mv2.id  and mv3.property = 'https://vocabs.acdh.oeaw.ac.at/schema#hasTitle' and mv3.lang = 'de' LIMIT 1),
                    (select mv3.value from metadata_view as mv3 where mv3.id = mv2.id  and mv3.property = 'https://vocabs.acdh.oeaw.ac.at/schema#hasTitle' and mv3.lang = 'und' LIMIT 1),
                    (select mv3.value from metadata_view as mv3 where mv3.id = mv2.id  and mv3.property = 'https://vocabs.acdh.oeaw.ac.at/schema#hasTitle' LIMIT 1)
                    ) as title
                    from public.metadata_view as mv
                    left join metadata_view as mv2 on mv.id = mv2.id
                    where mv.property = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type'
			and mv.value = :rdf
                    and mv2.value = :keyword and mv2.property = :property)
                    Select qd.id, qd.title, (select count(qd2.*) from query_data as qd2) as sumcount from query_data as qd  where LOWER(title ) like  LOWER('%' || :search || '%') 
                    "
                    . "order by $orderby $order "
                    . " limit :limit offset :offset;",
                array(
                    ':property' => $params['property'],
                    ':keyword' => $params['keyword'],
                    ':rdf' => $params['rdf'],
                    ':limit' => $limit,
                    ':offset' => $offset,
                    ':search' => $search
                ),
                ['allow_delimiter_in_query' => true, 'allow_square_brackets' => true]
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
}
