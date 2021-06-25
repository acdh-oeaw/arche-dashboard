<?php

namespace Drupal\arche_dashboard\Traits;

use acdhOeaw\arche\lib\RepoResourceInterface;
use zozlak\RdfConstants;

/**
 * Description of DisseminationServiceInterface
 *
 * @author nczirjak
 */
trait DisseminationServiceTrait {

    private $config;
    private $repo;
    private $searchTerm;
    private $searchCfg;
    private $repodb;
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
        $this->repo = \acdhOeaw\arche\lib\Repo::factory($this->config);
    }

    private function setSearchTerm(): void {
        $this->searchTerm = new \acdhOeaw\arche\lib\SearchTerm(\zozlak\RdfConstants::RDF_TYPE, $this->repodb->getSchema()->__get('dissService')->class);
    }

    private function setRepoDb(): void {
        $this->repodb = \acdhOeaw\arche\lib\RepoDb::factory($this->config);
    }

    private function setSearchConfig(): void {
        $this->searchCfg = new \acdhOeaw\arche\lib\SearchConfig();
        $this->searchCfg->class = '\acdhOeaw\arche\lib\disserv\dissemination\Service';
        $this->searchCfg->metadataMode = \acdhOeaw\arche\lib\RepoResourceInterface::META_NEIGHBORS;
        $this->searchCfg->metadataParentProperty = $this->repodb->getSchema()->dissService->parent;
    }

// </editor-fold>

    private function getDisseminationServicesData(): void {
        $this->dissServices = iterator_to_array($this->repo->getResourcesBySearchTerms([$this->searchTerm], $this->searchCfg));
    }

    private function createDissServObj(\acdhOeaw\arche\lib\disserv\dissemination\Service &$d, array $params): \Drupal\arche_dashboard\Object\DisseminationService {

        $obj = new \Drupal\arche_dashboard\Object\DisseminationService($d, $params, $this->repo->getSchema());
        $obj->setValues($this->repodb->getSchema());
        return $obj;
    }

    /**
     * Get the actual dissemination servcices in an array where each 
     * array is a dashboard dissemination service object
     * @return array
     */
    public function getDisseminationServices(): array {
        $this->getDisseminationServicesData();

        if (count($this->dissServices) == 0) {
            return array();
        }

        $this->result = array();


        foreach ($this->dissServices as $d) {
            $params = array();
            $params = $d->getParameters();
            $obj = $this->createDissServObj($d, $params);
            $this->result[] = $obj;
        }
        return $this->result;
    }

    public function getDisseminationServicesById(int $id): object {
        if (count($this->result) == 0) {
            $this->result = $this->getDisseminationServices();
        }

        foreach ($this->result as $value) {

            if ($value->getId() == $id) {
                return $value;
            }
        }
        return new \stdClass();
    }

}
