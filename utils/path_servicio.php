<?php

class Rest{
    public $tipo = "application/json";
    public $datosPeticion = array();
    private $_codEstado;
    public function __construct(){
        $this->useRequest();
    }

    public function response($data, $estado){
        $this->_codEstado = ($estado) ? $estado : 500;
        $this->setCabecera();
        echo $data;
        exit;
    }

    private function setCabecera(){
        header("HTTP/1.1" . $this->_codEstado . " " . $this->getCodEstado());
        header("Content-Type:" . $this->tipo . ';charset=utf-8');
    }

    private function limpiarRequest($data){
        $request = array();
        if(is_array($data)){
            foreach($data as $key => $value){
                $request[$key] = $this->limpiarRequest($value);
            }
        }else{
            if(get_magic_quotes_gpc()){
                $data = trim(stripcslashes($data));
            }
            $data = strip_tags($data);
            $data = htmlentities($data);
            $request = trim($data);
        }
        return $request;
    }

    private function useRequest(){
        $metodo = $_SERVER['REQUEST_METHOD'];
        switch($metodo){
            case "GET":
                $this->datosPeticion = $this->limpiarRequest($_GET);
                break;
            case "POST":
                $this->datosPeticion = $this->limpiarRequest($_POST);
                break;
            default:
                $this->response('',404);
                break;
        }
    }

    private function getCodEstado(){
        $estado = array(
            200 => 'OK',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error'
        );

        return $estado[$this->_codEstado];
    }
}

?>