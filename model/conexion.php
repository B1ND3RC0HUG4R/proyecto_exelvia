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

        $this->conexion = new mysqli($db['host'], $db['username'], $db['password'],$db['db']) or 
        die("Connect failed: %s\n". $conn -> error);
        $this->conexion->set_charset("UTF8");
    }

    public function getConexion(){
        return $this->conexion;
    }
}

?>