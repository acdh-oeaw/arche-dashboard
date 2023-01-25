<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Drupal\arche_dashboard\Helper;

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
    public function generatePropertyUrl(array &$data): array
    {
        foreach ($data as $k => $v) {
            if (isset($v->property)) {
                if (strpos($v->property, "#") !== false) {
                    $data[$k]->property = str_replace("#", "%23", $v->property);
                }
            }
        }
        return $data;
    }

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
                    if ($k == "property") {
                        $data[$i]->{$k} = '<a href="/browser/dashboard-property/'.$v.'">'.$v.'</a>';
                    }

                    if ($k == "format") {
                        $data[$i]->{$k} = '<a href="/browser/dashboard-format-property/'.$v.'">'.$v.'</a>';
                    }
                    
                    if ($k == "class") {
                        $data[$i]->{$k} = '<a href="/browser/dashboard-class-property/'.$v.'">'.$v.'</a>';
                    }
                    
                    if ($k == "format") {
                        $data[$i]->{$k} = '<a href="/browser/dashboard-format-property/'.$v.'">'.$v.'</a>';
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
    
    private function formatNumberToGuiFriendlyFormat(int $size): string
    {
        $base = log($size) / log(1024);
        $suffix = array("", " kb", " MB", " GB", " TB")[floor($base)];
        $result = pow(1024, $base - floor($base));
        return round((float)$result, 2) . $suffix;
    }
}
