<?php

namespace Drupal\arche_dashboard\Helper;

use acdhOeaw\acdhRepoLib\RepoDb;
use acdhOeaw\acdhRepoLib\SearchTerm;
use acdhOeaw\acdhRepoLib\SearchConfig;
use acdhOeaw\acdhRepoLib\RepoResourceInterface;
use zozlak\RdfConstants;

/**
 * Description of DisseminationServiceHelper
 *
 * @author nczirjak
 */
class DisseminationServiceHelper {

    private $config;
    private $repo;
    private $searchTerm;
    private $searchCfg;
    private $repodb;
    private $repoResDb;
    private $disservData = array();
    private $result = array();
    private $dissServices = array();

    public function __construct() {
        $this->setConfig();
        $this->setRepo();
        $this->setRepoDb();
        $this->setSearchTerm();
        $this->setSearchConfig();
    }

    // <editor-fold defaultstate="collapsed" desc="setter">
    private function setConfig(): void {
        if (!(\Drupal::service('extension.list.module')->getPath('acdh_repo_gui') . '/config/config.yaml')) {
            throwException(t('No config file found!'));
        }
        $this->config = \Drupal::service('extension.list.module')->getPath('acdh_repo_gui') . '/config/config.yaml';
    }

    private function setRepo(): void {
        $this->repo = \acdhOeaw\acdhRepoLib\Repo::factory($this->config);
    }
    
    private function setSearchTerm(): void {
       $this->searchTerm = new \acdhOeaw\acdhRepoLib\SearchTerm(RdfConstants::RDF_TYPE, $this->repodb->getSchema()->__get('dissService')->class); 
    }    

    private function setRepoDb(): void {
        $this->repodb = \acdhOeaw\acdhRepoLib\RepoDb::factory($this->config); 
    }
    
    private function setSearchConfig(): void {
        $this->searchCfg = new \acdhOeaw\acdhRepoLib\SearchConfig();
        $this->searchCfg->class = '\acdhOeaw\arche\disserv\dissemination\Service';
        $this->searchCfg->metadataMode = RepoResourceInterface::META_RESOURCE;
    }
    
// </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="getter">
    private function getRepoResouce(): void {
        $this->repoResDb = new \acdhOeaw\arche\disserv\RepoResourceDb($this->repo->getBaseUrl(), $this->repodb);
    }

    private function getTitle(object &$v, string &$k): string {
        if ($v->getGraph()->get('https://vocabs.acdh.oeaw.ac.at/schema#hasTitle')->__toString()) {
            return $v->getGraph()->get('https://vocabs.acdh.oeaw.ac.at/schema#hasTitle')->__toString();
        } else {
            return $k;
        }
        return "";
    }

    private function getHasBinary(object &$v): string {
        if ($v->getGraph()->get('https://vocabs.acdh.oeaw.ac.at/schema#hasBinarySize')->__toString()) {
            return $v->getGraph()->get('https://vocabs.acdh.oeaw.ac.at/schema#hasBinarySize')->__toString();
        }
        return "";
    }

    private function getHasDescription(object &$v): string {
        if ($v->getGraph()->get('https://vocabs.acdh.oeaw.ac.at/schema#hasDescription')->__toString()) {
            return $v->getGraph()->get('https://vocabs.acdh.oeaw.ac.at/schema#hasDescription')->__toString();
        }
        return "";
    }

    private function getHasNumberOfItems(object &$v): string {
        if ($v->getGraph()->get('https://vocabs.acdh.oeaw.ac.at/schema#hasNumberOfItems')->__toString()) {
            return $v->getGraph()->get('https://vocabs.acdh.oeaw.ac.at/schema#hasNumberOfItems')->__toString();
        }
        return "";
    }

    private function getHasReturnType(object &$v): string {
        if (count($v->getGraph()->all('https://vocabs.acdh.oeaw.ac.at/schema#hasReturnType')) > 0) {
            $result = "";
            $data = $v->getGraph()->all('https://vocabs.acdh.oeaw.ac.at/schema#hasReturnType');
            for ($index = 0; $index < count($data); $index++) {
                $result .= $data[$index]->__toString();
                if ($index < count($data) - 1) {
                    $result .= ", ";
                }
            }
            return $result;
        }
        return "";
    }

    private function getServiceLocation(object &$v): string {
        if ($v->getGraph()->get('https://vocabs.acdh.oeaw.ac.at/schema#serviceLocation')->__toString()) {
            return $v->getGraph()->get('https://vocabs.acdh.oeaw.ac.at/schema#serviceLocation')->__toString();
        }
        return "";
    }

    private function getUri(object &$v): string {
        if ($v->getGraph()->getUri()) {
            return $v->getGraph()->getUri();
        }
        return "";
    }

// </editor-fold>

    private function getDisseminationServices(): void {
        $this->dissServices = $this->repodb->getResourcesBySearchTerms([$this->searchTerm], $this->searchCfg);
    }
    
    public function createDissServObj(\acdhOeaw\arche\disserv\dissemination\Service $d): \Drupal\arche_dashboard\Object\DisseminationService {
        $obj = new \Drupal\arche_dashboard\Object\DisseminationService($d);
        $obj->setValues($this->repodb->getSchema());
        return $obj;
    }
    
    public function getDissServResourcesById(string $dissId): array {
        
    }
    
    /**
     * get the available dissemination services
     * @return array
    */
    public function getDissServices(): array {
        
        $this->getDisseminationServices();
        
        if(count($this->dissServices) == 0) {
            return array();
        }
        
        foreach($this->dissServices as $d) {
            $obj = $this->createDissServObj($d);           
            $this->result[] = $obj;
        }
        return $this->result;
    }
}
