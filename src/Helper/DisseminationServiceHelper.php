<?php

namespace Drupal\arche_dashboard\Helper;

/**
 * Description of DisseminationServiceHelper
 *
 * @author nczirjak
 */
class DisseminationServiceHelper {

    private $config;
    private $repo;
    private $repodb;
    private $repoResDb;
    private $disservData = array();
    private $result = array();

    public function __construct() {
        $this->setConfig();
        $this->setRepo();
        $this->setRepoDb();
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

    private function setRepoDb(): void {
        $this->repodb = \acdhOeaw\acdhRepoLib\RepoDb::factory($this->config); 
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

        /**
     * get the available dissemination services
     * @return array
     */
    public function getDissServices(): array {
        
        $this->getRepoResouce();

        try {

            $this->disservData = $this->repoResDb->getDissServices();
            
            $i = 0;
            foreach ($this->disservData as $k => $v) {
                $this->result[$i]['key'] = $k;
                $this->result[$i]['uri'] = $this->getUri($v);
                $this->result[$i]['hasTitle'] = $this->getTitle($v, $k);
                $this->result[$i]['hasBinarySize'] = $this->getHasBinary($v);
                $this->result[$i]['hasDescription'] = $this->getHasDescription($v);
                $this->result[$i]['hasNumberOfItems'] = $this->getHasNumberOfItems($v);
                $this->result[$i]['hasReturnType'] = $this->getHasReturnType($v);
                $this->result[$i]['serviceLocation'] = $this->getServiceLocation($v);
                $i++;
            }

            return $this->result;
        } catch (Exception $ex) {
            return array();
        } catch (\GuzzleHttp\Exception\ServerException $ex) {
            return array();
        } catch (\acdhOeaw\acdhRepoLib\exception\RepoLibException $ex) {
            return array();
        }
    }

}
