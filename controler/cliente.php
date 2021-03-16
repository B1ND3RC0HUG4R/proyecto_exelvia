<?php 
include '../model/conexion.php';
require_once("../utils/path_servicio.php");

class Api extends Rest{
    private $_metodo;
    private $_argumentos;
    private $_conn;

    public function __construct(){
        parent::__construct();
        $db= new Conexion();
        $this->_conn = $db->getConexion();
    }    

    public function procesarLlamada(){
        if(isset($_REQUEST['url'])){
            $url = explode('/', trim($_REQUEST['url']));
            $url = array_filter($url);
            $this->_metodo = strtolower(array_shift($url));
            $this->_argumentos = $url;
            $func = $this->_metodo;
            if((int) method_exists($this, $func) > 0){
                if(count($this->_argumentos) > 0){
                    call_user_func_array(array($this, $this->_metodo), $this->_argumentos);
                }else{
                    call_user_func(array($this, $this->_metodo));
                }
            }else{
                $this->response($this->convertirJson($this->codigoError(0)), 404);
            }
            $this->response($this->convertirJson($this->codigoError(0)), 404);
        }
    }

    public function obtenerCliente($id_cliente){
        $id= (int) $id_cliente;
        if($_SERVER['REQUEST_METHOD'] != "GET"){
            $this->response($this->convertirJson($this->codigoError(0)), 405);
        }
        if($id > 0){
            $query = $this->_conn->prepare("SELECT id, nombre, ap_pat, ap_mat FROM cliente WHERE id=:id");
            $query->bindValue(":id", $id);
            $query->execute();
            $filas = $query->fetchAll(PDO::FETCH_ASSOC);
            $num = count($filas);
            if($num > 0){
                $respuesta["estado"] = 1.0;
                $respuesta["cliente"] = $filas;
                $this->response($this->convertirJson($respuesta), 200);
            }
        }
        $this->response($this->codigoError(2), 204);
    }

    public function guardarCliente(){
        if($_SERVER['REQUEST_METHOD'] != "POST"){
            $this->response($this->convertirJson($this->codigoError(0)), 405);
        }
        if(isset($this->datosPeticion['nombre'], $this->datosPeticion['ap_pat'], $this->datosPeticion['ap_mat'])){
            $nombre=$this->datosPeticion['nombre'];
            $ap_pat=$this->datosPeticion['ap_pat'];
            $ap_mat=$this->datosPeticion['ap_mat'];
            
            $query = $this->_conn->prepare("INSERT INTO cliente (nombre,ap_pat, ap_mat) VALUES (:nombre, :ap_pat, :ap_mat)");
            $query->bindValue(":nombre", $nombre);
            $query->bindValue(":ap_pat", $ap_pat);
            $query->bindValue(":ap_mat", $ap_mat);
            $query->execute();
            if($query->rowCount() == 1){
                $id = $this->_conn->lastInsertId();
                $respuesta["estado"] = 1;
                $respuesta["msg"] = 'Cliente guardado correctamente';
                $respuesta["usuario"]["id"] = $id;
                $respuesta["usuario"]["nombre"] = $nombre;
                $respuesta["usuario"]["ap_pat"] = $ap_pat;
                $respuesta["usuario"]["ap_mat"] = $ap_mat;
                $this->response($this->convertirJson($respuesta), 200);
            }else{
                $this->response($this->convertirJson($this->codigoError(7), 400));
            }
        }else{
            $this->response($this->convertirJson($this->codigoError(7), 400));
        }
    }


    private function codigoError($id){
        $errores = array(
            array('estado' => "error", "msg" => "Petición no encontrada"),
            array('estado' => "error", "msg" => "Petición no aceptada"),
            array('estado' => "error", "msg" => "Petición sin contenido"),
            array('estado' => "error", "msg" => "Email o password incorrectos"),
            array('estado' => "error", "msg" => "Error borrando Cliente"),
            array('estado' => "error", "msg" => "Error actualizando información"),
            array('estado' => "error", "msg" => "Error buscando Cliente por correo"),
            array('estado' => "error", "msg" => "Error creando Cliente"),
            array('estado' => "error", "msg" => "Cliente ya existe")
        );
        return $errores[$id];
    }

    private function convertirJson($data){
        return json_encode($data);
    }
}

$api = new Api();
$api->procesarLlamada();
?>