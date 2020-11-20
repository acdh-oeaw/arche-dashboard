<?php

namespace Drupal\arche_dashboard\Object;

/**
 * Description of DisseminationService
 *
 * @author nczirjak
 */
class DisseminationService {
    
    private $obj;
    public $url;
    public $title;
    public $location;
    public $formats;
    public $loadParams;
    public $description;    
    public $numberOfItems;
    public $returnType;
    public $matchesProp;       
    public $matchesValue;    
    public $isPartOf;    
    public $serviceRevProxy; 
    public $count;
    public $id;
    
    public function __construct(\acdhOeaw\arche\disserv\dissemination\Service $obj) {
        $this->obj = $obj;        
    }
    // <editor-fold defaultstate="collapsed" desc="getters">

    public function getUrl() {
        return $this->url;
    }
    
    public function getId() {
        return $this->id;
    }

    public function getTitle() {
        return $this->title;
    }
    
    public function getFormats() {
        return $this->formats;
    }

    public function getLocation() {
        return $this->location;
    }

    public function getLoadParams() {
        return $this->loadParams;
    }
    
    public function getDescription() {
        return $this->description;
    }
    
    public function getNumberOfItems() {
        return $this->numberOfItems;
    }

    public function getReturnType() {
        return $this->returnType;
    }
    
    public function getMatchesProp() {
        return $this->matchesProp;
    }

    public function getMatchesValue() {
        return $this->matchesValue;
    }

    public function getIsPartOf() {
        return $this->isPartOf;
    }

    public function getServiceRevProxy() {
        return $this->serviceRevProxy;
    }

     public function getCount() {
        return $this->count;
    }

// </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="setters">
    
    private function setUrl(): void {
        if ($this->obj->getUri() == null) {
            $this->url = "url missing";
        }
        $this->url = $this->obj->getUri();
    }

    private function setTitle(): void {
        $this->title = $this->getLiteral('https://vocabs.acdh.oeaw.ac.at/schema#hasTitle');
    }


    private function setDescription(): void {
        $this->description = $this->getLiteral('https://vocabs.acdh.oeaw.ac.at/schema#hasDescription');
    }

    private function setLocation(): void {
        if ($this->obj->getLocation() == null) {
            $this->location = "location missing";

        }
        $this->location = $this->obj->getLocation();
    }

    private function setFormats(): void {
        if ($this->obj->getFormats() == null) {
            $this->formats = "formats missing";
        }
        $this->formats = implode(";", $this->obj->getFormats());
    }

    private function setLoadParams(): void {
        if ($this->obj->loadParametersFromMetadata() == null) {
            $this->loadParams = "loadparams missing";
        }
        $this->loadParams = $this->obj->loadParametersFromMetadata();
    }

    private function setNumberOfItems(): void {
        $this->numberOfItems = $this->getLiteral('https://vocabs.acdh.oeaw.ac.at/schema#hasNumberOfItems');
    }

    private function setReturnType(): void {
        $this->returnType = $this->getLiteral('https://vocabs.acdh.oeaw.ac.at/schema#hasReturnType');
    }

    private function setMatchesProp(): void {
        $this->matchesProp = $this->getLiteral('https://vocabs.acdh.oeaw.ac.at/schema#matchesProp');
    }

    private function setMatchesValue(): void {
        $this->matchesValue = $this->getLiteral('https://vocabs.acdh.oeaw.ac.at/schema#matchesValue');
    }

    private function setIsPartOf(): void {
        $this->isPartOf = $this->getLiteral('https://vocabs.acdh.oeaw.ac.at/schema#isPartOf');
    }

    private function setServiceRevProxy(): void {
        $this->serviceRevProxy = $this->getLiteral('https://vocabs.acdh.oeaw.ac.at/schema#serviceRevProxy');
    }
    
    private function setCount(int $count): void {
        $this->count = $count;
    }
    
    private function setId(): void {
        $this->id = $this->getIdFromUri($this->obj->getUri());
    }

    // </editor-fold>

    private function getIdFromUri(string $uri): int
    {
        return (int) substr($uri, strrpos($uri, '/') + 1);
    }

    private function getLiteral(string $property): string {
        if(isset($this->obj->getMetadata()->all($property)[0])) {
            return $this->obj->getMetadata()->all($property)[0]->__toString();  
        }
        return "";
    }
    
    /**
     * Set the values based on the passed dissemination service object
     * @param \acdhOeaw\acdhRepoLib\Schema $schema
     */
    public function setValues(\acdhOeaw\acdhRepoLib\Schema $schema) {
        $this->setTitle();
        $this->setId();
        $this->setUrl();
        $this->setFormats();
        $this->setLoadParams();
        $this->setLocation();
        $this->setDescription();
        $this->setNumberOfItems();
        $this->setReturnType();
        $this->setMatchesProp();
        $this->setMatchesValue();
        $this->setIsPartOf();
        $this->setServiceRevProxy();
        $this->countAllMatchingResource($schema);
    }
    
    /**
     * Get the selected dissemination services matching results uri
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getMatchingResources(int $limit, int $offset): array {
        
        $result = array();
        $obj = new \stdClass();
        $obj = $this->obj->getMatchingResources($limit, $offset);
        
        foreach ($obj as $v) {
            $url = $v->getUri();
            $title = "no_title";
            $id = $this->getIdFromUri($v->getUri());
            if($v->getMetadata()->get('https://vocabs.acdh.oeaw.ac.at/schema#hasTitle') != null) {
                $title =$v->getMetadata()->get('https://vocabs.acdh.oeaw.ac.at/schema#hasTitle')->__toString();
            }
            $result[] = array("id" => $id, "url" => $url, "title" => $title);
        }        
        return $result;        
    }
    
    private function countAllMatchingResource(\acdhOeaw\acdhRepoLib\Schema $schema): void {

        $query = $this->obj::getMatchQuery($this->getId(), \acdhOeaw\arche\disserv\dissemination\ServiceInterface::QUERY_RES, $schema);
        $query->query = "SELECT count(*) FROM ($query->query) t";
        $db = new \Drupal\arche_dashboard\Model\DashboardModel();
        $this->setCount($db->countAllMatchingResourcesForDisseminationService($query));
    }
}
