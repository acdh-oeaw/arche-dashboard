<?php

namespace Drupal\arche_dashboard\Object; 

class CacheFile {
    
    private $path;
    private $filename;
    
    public function __construct(string $path, string $filename) {
        $this->path = $path;
        $this->filename = $filename;
    }
    
    /**
     * Get the saved file json content
     * @return string
     */
    public function getJsonContent(): string {
       return file_get_contents($this->path.$this->filename); 
    }
    
    /**
     * Check the file exists
     * @return bool
     */
    public function checkFileExists(): bool {
         if(!file_exists($this->path.$this->filename)) {
            $file = fopen($this->path.$this->filename, "w"); 
            fclose($file);
            return false;
        }
        return true;
    }
    
    /**
     * Compare the DB last modify date and the file creation date.
     * If the DB last modify is newer then we need to create a new file.
     * @param string $date
     * @return bool
     */
    public function compareDates(string $date): bool {
        if(strtotime($this->checkFileModificationTime()) < strtotime($date)) {
            return true;
        }
        return false;
    }
    
    /**
     * Get the File modification date
     * @return type
     */
    private function checkFileModificationTime() {
        return date ("Y-m-d H:i:s", filemtime($this->path.$this->filename));
    }
    
    public function getSize(): int {
        return filesize($this->path.$this->filename);
    }
    
    public function addContent(string $content) {
        $file = fopen($this->path.$this->filename, "w"); 
        fwrite($file, $content);
        fclose($file);
    }
}