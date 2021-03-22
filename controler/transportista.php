<?php 
header('Content-Type: text/html; charset=UTF-8');
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

    public function obtenerTransportista($id_transportista){
        $id= (int) $id_transportista;
        if($_SERVER['REQUEST_METHOD'] != "GET"){
            $this->response($this->convertirJson($this->codigoError(0)), 405);
        }
        if($id > 0){
            $query = $this->_conn->prepare("SELECT id, nombre, ap_pat, ap_mat FROM transportista WHERE id=:id");
            $query->bindValue(":id", $id);
            $query->execute();
            $filas = $query->fetchAll(PDO::FETCH_ASSOC);
            $num = count($filas);
            if($num > 0){
                $respuesta["estado"] = 1.0;
                $respuesta["transportista"] = $filas;
                $this->response($this->convertirJson($respuesta), 200);
            }
        }
        $this->response($this->codigoError(2), 204);
    }

    public function guardarTransportista(){
        if($_SERVER['REQUEST_METHOD'] != "POST"){
            $this->response($this->convertirJson($this->codigoError(0)), 405);
        }
        if(isset($this->datosPeticion['empresa'], $this->datosPeticion['contacto'], $this->datosPeticion['tel_oficina'], 
        $this->datosPeticion['celular'], $this->datosPeticion['email'], $this->datosPeticion['pagina_web'], $this->datosPeticion['calle'],
        $this->datosPeticion['num_interior'], $this->datosPeticion['num_exterior'], $this->datosPeticion['estado'], 
        $this->datosPeticion['colonia'], $this->datosPeticion['codigo_postal'])){
            //DIRECCION
            $dir_calle = utf8_encode($this->datosPeticion['calle']);
            $dir_num_int = $this->datosPeticion['num_interior'];
            $dir_num_ext = $this->datosPeticion['num_exterior'];
            $dir_estado= utf8_encode($this->datosPeticion['estado']);
            $dir_colonia = utf8_encode($this->datosPeticion['colonia']);
            $dir_cp = utf8_encode($this->datosPeticion['codigo_postal']);
            
            //TRANSPORTISTA
            $empresa = utf8_encode($this->datosPeticion['empresa']);
            $contacto = utf8_encode($this->datosPeticion['contacto']);
            $tel_ofi = $this->datosPeticion['tel_oficina'];
            $celular = $this->datosPeticion['celular'];
            $correo = $this->datosPeticion['email'];
            $pag_web = $this->datosPeticion['pagina_web'];
            
            //CERTIFICACIONES
            $cerctpat = $cert_iso = $cer_oea = $cer_trans = 0;
            if($this->datosPeticion['certifi_ctpat'] == 1){
                $cerctpat= 1;
            }
            if($this->datosPeticion['certifi_iso'] == 1){
                $cert_iso = 1;
            }
            if($this->datosPeticion['certifi_oea'] == 1){
                $cer_oea = 1;
            }
            if($this->datosPeticion['certifi_trans'] == 1){
                $cer_trans = 1;
            }
            if($this->datosPeticion['certifi_other'] == 1){
                $cert_other_txt = $this->datosPeticion["cert_other_txt"];
            }

            //TRANSPORTE
            //caja refrigerada
            $camion_ref = $this->datosPeticion['camion_refri'];
            $estaquitas_ref = $this->datosPeticion['estaquitas_refri'];
            $full_ref = $this->datosPeticion['full_refri'];
            $rabon_ref = $this->datosPeticion['rabon_refri'];
            $torton_ref = $this->datosPeticion['torton_refri'];
            $ref_40 = $this->datosPeticion['refri_40'];
            $ref_48 = $this->datosPeticion['refri_48'];
            $ref_53 = $this->datosPeticion['refri_53'];
            //caja seca
            $camion_seca = $this->datosPeticion['camion_seca'];
            $estaquitas_seca = $this->datosPeticion['estaquitas_seca'];
            $full_seca = $this->datosPeticion['full_seca'];
            $rabon_seca = $this->datosPeticion['rabon_seca'];
            $torton_seca = $this->datosPeticion['torton_seca'];
            $seca_40 = $this->datosPeticion['seca_40'];
            $seca_48 = $this->datosPeticion['seca_48'];
            $seca_53 = $this->datosPeticion['seca_53'];
            //especializado
            $esp_granel = $this->datosPeticion['esp_granel'];
            $esp_pipa = $this->datosPeticion['esp_pipa'];
            $esp_asf = $this->datosPeticion['esp_asf'];
            $esp_red = $this->datosPeticion['esp_red'];
            $esp_jagr = $this->datosPeticion['esp_jagr'];
            $esp_jcor = $this->datosPeticion['esp_jcor'];
            $esp_jenlo = $this->datosPeticion['esp_jenlo'];
            $esp_jgana = $this->datosPeticion['esp_jgana'];
            $esp_low = $this->datosPeticion['esp_low'];
            $esp_mad = $this->datosPeticion['esp_mad'];
            $esp_pla = $this->datosPeticion['esp_pla'];
            $esp_step = $this->datosPeticion['esp_step'];
            $esp_tol = $this->datosPeticion['esp_tol'];
            //material peligroso
            $mat_pipa = $this->datosPeticion['mat_pipa'];
            $mat_cam = $this->datosPeticion['mat_cam'];
            $mat_esta = $this->datosPeticion['mat_esta'];
            $mat_full = $this->datosPeticion['mat_full'];
            $mat_rabon = $this->datosPeticion['mat_rabon'];
            $mat_torton = $this->datosPeticion['mat_torton'];
            $mat_40 = $this->datosPeticion['mat_40'];
            $mat_48 = $this->datosPeticion['mat_48'];
            $mat_53 = $this->datosPeticion['mat_53'];
            
            $query_trans = $this->_conn->prepare("INSERT INTO transportista (empresa, contacto, tel_oficina, celular, email, pagina_web, calle, num_exterior, 
            num_interior, estado, colonia, codigo_postal) VALUES ('".$empresa."', '".$contacto."', '".$tel_ofi."', ".$celular.", '".$correo."', '".$pag_web."', '".$dir_calle."', 
            ".$dir_num_ext.", ".$dir_num_int.", '".$dir_estado."', '".$dir_colonia."', ".$dir_cp.")");

            if($query_trans->execute()){
                $id = $this->_conn->insert_id;
                //certificacion insert
                $query_cert = $this->_conn->prepare("INSERT INTO certificaciones (ctpat, iso, oea, trans_limpio, otro, id_transportista) VALUES 
                (".((int) $cerctpat).", ".((int) $cert_iso).", ".((int) $cer_oea).", ".((int) $cer_trans).", '".$cert_other_txt."', ".$id.")");
                $query_cert->execute();
                //caja refrigerada insert
                $query_ref = $this->_conn->prepare("INSERT INTO refrigerada (camion, estaquita, full, rabon, torton, pies40, pies48, pies53, id_transportista) VALUES 
                (".$camion_ref.", ".$estaquitas_ref.", ".$full_ref.", ".$rabon_ref.", ".$torton_ref.", ".$ref_40.", ".$ref_48.", ".$ref_53.", ".$id.")");
                $query_ref->execute();
                //caja seca insert
                $query_seca = $this->_conn->prepare("INSERT INTO seca (camion, estaquita, full, rabon, torton, pies40, pies48, pies53, id_transportista) VALUES 
                (".$camion_seca.", ".$estaquitas_seca.", ".$full_seca.", ".$rabon_seca.", ".$torton_seca.", ".$seca_40.", ".$seca_48.", ".$seca_53.", ".$id.")");
                $query_seca->execute();
                //caja especializada insert
                $query_espe = $this->_conn->prepare("INSERT INTO especializado (granel, pipa, asfalto, redilas, j_granel, j_cortina, j_enlonada, j_ganadera, 
                low_boy, madrina, plataforma, step_deck, tolva, id_transportista) VALUES (".$esp_granel.", ".$esp_pipa.", ".$esp_asf.", ".$esp_red.", ".$esp_jagr.", ".$esp_jcor.", 
                ".$esp_jenlo.", ".$esp_jgana.", ".$esp_low.", ".$esp_mad.", ".$esp_pla.", ".$esp_step.", ".$esp_tol.", ".$id.")");
                $query_espe->execute();
                //caja peligroso insert
                $query_peli = $this->_conn->prepare("INSERT INTO peligroso (autotanque, camion, estaquita, full, rabon, torton, pies40, pies48, pies53, id_transportista)
                VALUES (".$mat_pipa.", ".$mat_cam.", ".$mat_esta.", ".$mat_full.", ".$mat_rabon.", ".$mat_torton.", ".$mat_40.", ".$mat_48.", ".$mat_53.", ".$id.")");
                $query_peli->execute();

                mysqli_close($this->_conn);
                $respuesta["estado"] = 1;
                $respuesta["msg"] = 'Transportista guardado correctamente';
                $respuesta["usuario"]["folio"] = $id;
                $respuesta["usuario"]["empresa"] = $empresa;
                $respuesta["usuario"]["contacto"] = $contacto;
                $respuesta["usuario"]["email"] = $correo;
                $this->response($this->convertirJson($respuesta), 200);
            }else{
                $this->response($this->convertirJson($this->codigoError(7)), 400);
            }
        }else{
            $this->response($this->convertirJson($this->codigoError(7)), 400);
        }
    }

    private function codigoError($id){
        $errores = array(
            array('estado' => "error", "msg" => "Petición no encontrada"),
            array('estado' => "error", "msg" => "Petición no aceptada"),
            array('estado' => "error", "msg" => "Petición sin contenido"),
            array('estado' => "error", "msg" => "Email o password incorrectos"),
            array('estado' => "error", "msg" => "Error borrando transportista"),
            array('estado' => "error", "msg" => "Error actualizando información"),
            array('estado' => "error", "msg" => "Error buscando transportista por correo"),
            array('estado' => "error", "msg" => "Error creando transportista"),
            array('estado' => "error", "msg" => "Transportista ya existe")
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