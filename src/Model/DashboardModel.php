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
        "properties"            =>  "SELECT * FROM gui.dash_properties_func();",
        "classes"               =>  "SELECT * FROM gui.dash_classes_func();",
        "classesproperties"     =>  "SELECT * FROM gui.dash_classes_properties_func();",
        "topcollections"        =>  "SELECT * FROM gui.dash_topcollections_func();",
        "formats"               =>  "SELECT * FROM gui.dash_formats_func();",
    "formatspercollection"  =>  "SELECT * FROM gui.dash_formatspercollection_func();"
    );
   
    public function __construct()
    {
        //set up the DB connections
        \Drupal\Core\Database\Database::setActiveConnection('repo');
        $this->repodb = \Drupal\Core\Database\Database::getConnection('repo');
    }
    
    /**
     * Generate the sql data
     * @param string $key
     * @return array
     */
    public function getViewData(string $key="properties"): array
    {
        if (array_key_exists($key, $this->queries)) {
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
     * Get the values by property data
     * @param string $property
     * @return array
     */
    public function getValuesByPropertyApiData(string $property, int $offset, int $limit, string $search = ""): array
    {
        $property = str_replace(':', '/', $property);
        $property = str_replace('//', '://', $property);
     
        try {
            $query = $this->repodb->query(
                "select * from gui.dash_get_facet_by_property_func(:property) where LOWER(key) like  LOWER('%' || :search || '%') limit :limit offset :offset;",
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
    
    public function changeBackDBConnection()
    {
        \Drupal\Core\Database\Database::setActiveConnection();
    }
}
