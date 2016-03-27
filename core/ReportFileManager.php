<?php
require_once("vendor/faisalman/simple-excel-php/src/SimpleExcel/SimpleExcel.php");

class ReportFileManager{


    private $headers;

    private $rows;

    private $footers;

    private $service;

    private $filename;

    private $format;


    public function __construct($filename, $format = "csv")
    {
        $this->filename = $filename;
        $this->format = $format;
        $this->headers  = [];
        $this->rows     = [];
        $this->footers  = [];
        $this->service = new \SimpleExcel\SimpleExcel($format);
    }


    public function getCountColumn(){
        return count($this->headers);
    }


    public function setHeader($headers){
        $this->headers = $headers;
    }

    public function getRows(){
        return $this->rows;
    }

    public function addRow($row){
        $this->rows[] = $row;
    }


    /**
     * @return null|string
    */
    public function generateFile(){
        if (!$this->service) return null;

        $data = [];
        if ($this->headers) $data[0] = $this->headers;

        foreach($this->rows as $k=>$row){
            $dataRow = [];
            foreach($row as $kfield=>$field){
                if (is_array($field)){
                    foreach($row as $ksubfield=>$subfield){
                        $dataRow[]  = $subfield;
                    }
                }else
                    $dataRow[] = $field;
            }
            $data[] = $dataRow;
        }

        $this->service->writer->setData($data);
        $this->service->writer->saveFile($this->filename);

        return $this->filename;
    }
}