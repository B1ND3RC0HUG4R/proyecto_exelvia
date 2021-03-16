<?php

class Conexion{
    private $conexion;

    public function __construct(){
        $db= array(
            'host' => '127.0.0.1',
            'username' => 'userexelvia',
            'password' => 'exelvia123',
            'db' => 'exelvia'
        );
        $options = array(
            PDO::ATTR_EMULATE_PREPARES => FALSE,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        );

        try{
            $dns= 'mysql:host='.$db['host'].';dbname='.$db['db'].';charset=utf8';
            $this->conexion = new PDO($dns, $db['username'], $db['password'], $options);
        }catch(PDOException $e){
            echo "Fallo la conexion: ".$e->getMessage();
            exit;
        }
    }

    public function getConexion(){
        return $this->conexion;
    }
}

?>