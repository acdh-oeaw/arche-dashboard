<?php

namespace Drupal\arche_dashboard\Helper;

use Drupal\file\Entity\File;
use Drupal\arche_dashboard\Object\CacheFile;

/**
 * Description of DashboardHelper
 *
 * @author nczirjak
 */
class DashboardHelper
{
    private $tableInfo = [
        'properties' => [
            'property' => ['name' => 'property' ],
            'count' => ['name' => 'count' ],
        ],
        'classesproperties' => [
            'class',
            'property',
            'cnt_distinct_value',
            'cnt'
        ],
        'classes' => [
            'class',
            'count',
        ],
        'topcollections' => [
            'ID',
            'Title',
            'Count',
            'Max.Relatives',
            'Sum.Size',
            'Sum.BinarySize',
        ],
        'formats' => [
            'Format',
            'Count Format',
            'Count Raw BinarySize',
            'Sum.Size',
        ],
        'formatspercollection' => [
            'Id',
            'Title',
            'Type',
            'Format',
            'Count',
            'Sum.Size',
            'Sum.Count',
        ]
    ];

    /**
     * This function handle the # removing problem in the browser
     *
     * @param array $data
     * @return array
     */
    public function generatePropertyUrl(array &$data, string $field = "property"): array
    {
        foreach ($data as $k => $v) {
            if (isset($v->{$field})) {
                if (strpos($v->{$field}, "#") !== false) {
                    $data[$k]->{$field} = str_replace("#", "%23", $v->{$field});
                }
            }
        }
        return $data;
    }
    
    /**
     * We have to change the field values for the server side generated tables case.
     * @param array $data
     * @param string $key
     * @return array
     */
    public function addUrlToTableData(array $data, string $key = "properties"): array
    {
        $keys = array_keys((array)$data[0]);

        if (isset($this->tableInfo[$key])) {
            for ($i = 0; $i < count($data); $i++) {
                $id = "";
                if (isset($data[$i]->id)) {
                    $id = $data[$i]->id;
                }
                
                foreach ($data[$i] as $k => $v) {
                    $fv = str_replace('#', '%23', $v);
                    if ($k == "property") {
                        $data[$i]->{$k} = '<a href="/browser/dashboard-property/'.$fv.'">'.$v.'</a>';
                    }

                    if ($k == "format") {
                        $data[$i]->{$k} = '<a href="/browser/dashboard-format-property/'.$fv.'">'.$v.'</a>';
                    }
                    
                    if ($k == "class") {
                        $data[$i]->{$k} = '<a href="/browser/dashboard-class-property/'.$fv.'">'.$v.'</a>';
                    }
                    
                    if ($k == "format") {
                        $data[$i]->{$k} = '<a href="/browser/dashboard-format-property/'.$fv.'">'.$v.'</a>';
                    }
                    if ($k == "title" && !empty($id)) {
                        $data[$i]->{$k} = '<a href="/browser/oeaw_detail/'.$id.'">'.$v.'</a>';
                    }
                    if ($k == "id") {
                        $data[$i]->{$k} = '<a href="/browser/oeaw_detail/'.$v.'">'.$v.'</a>';
                    }
                   
                    if (($k == 'count' || $k == 'cnt') && isset($data[$i]->type) && $data[$i]->type == "REL") {
                        $data[$i]->{$k} = '<a href="#" id="getAttributesView" data-property="'.$k.'" data-value="'.$k.'">'.$v.'</a>';
                    }
                    
                    if ($k == "sum_size" && $v != null) {
                        $data[$i]->{$k} = $this->formatNumberToGuiFriendlyFormat($v);
                    }
                    
                    if ($k == "binary_size"  && $v != null) {
                        $data[$i]->{$k} = $this->formatNumberToGuiFriendlyFormat($v);
                    }
                    
                    if ($k == "count_rawbinarysize"  && $v != null) {
                        $data[$i]->{$k} = $this->formatNumberToGuiFriendlyFormat($v);
                    }
                }
            }
        }
        
        return $data;
    }
    
    

    /**
     * Get the cached query files path
     * @return type
     */
    public function getCachedFilePath()
    {
        $extension_list = \Drupal::service('extension.list.module');
        return $extension_list->getPath('arche_dashboard') . '/cache/';
    }
    
    
    
    /**
     * Formalize the bytes to MB/GB/etc for the gui
     * @param int $size
     * @return string
     */
    private function formatNumberToGuiFriendlyFormat(int $size): string
    {
        $base = log($size) / log(1024);
        $suffix = array("", " kb", " MB", " GB", " TB")[floor($base)];
        $result = pow(1024, $base - floor($base));
        return round((float)$result, 2) . $suffix;
    }
    
    /**
     * Create array from the passed properties
     * @param string $params
     * @return array
     */
    public function processValuesByPropApiParamaters(string $params): array
    {
        $result = [];
        $params = explode("&", $params);
        $property = str_replace(':', '/', $params[0]);
        $result['property'] = str_replace('//', '://', $property);
        $rdf = str_replace(':', '/', $params[1]);
        $result['rdf'] = str_replace('//', '://', $rdf);
        
        if (isset($params[2])) {
            $kw = str_replace(':', '/', $params[2]);
            $result['keyword'] = str_replace('//', '://', $kw);
        }
        return $result;
    }
    
    public function dashboardValuesByPropertyTableFormat(array &$data, string $prop, array $rdf): array
    {
        $prop = str_replace("#", "%23", $prop);
        $rdf = implode("?", $rdf);
        $rdf = str_replace("#", "%23", $rdf);
                
        for ($x = 0; $x <= count($data); $x++) {
            if (isset($data[$x]->count)) {
                $data[$x]->count = '<a href="/browser/dashboard-vbp-detail/'.$prop.'&'.$rdf.'&'.str_replace("#", "%23", $data[$x]->key).'" >'.$data[$x]->count.'</a>';
            }
        }
        
        return $data;
    }
}
