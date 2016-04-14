<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mdl_producto extends CI_Model {
    function __construct(){
        parent::__construct();
       //-- $this->load->database();
    }
    
    public function getProduct(){

        $page=(empty($_POST['page'])?0:$_POST['page']);
        $page = max($page, 1);
        $criteria=(empty($_POST['criteria'])?"":$_POST['criteria']);
        
        $customers=false;

        $this->db->select('id_producto,nombre');
        $this->db->from('producto');
        $this->db->where("id_producto LIKE '%$criteria%' OR nombre LIKE '%$criteria%'");

        $query = $this->db->get();

        $total=$query->num_rows();

        if($query->num_rows() > 0)
        {
            foreach ($query->result() as $row)
            {
                $customers[] = array('id'=> $row->id_producto, 'text'=> $row->nombre);
            }
        }
        return json_encode(array(
          'results' => $customers,
          'total' => $total
        )); 
    }

    public function getInfoProducto($producto){
        $lineas=$this->getLineasSublineas();
        $consulta="
        SELECT id_producto,nombre,id_sublinea FROM `producto` 
        WHERE id_producto ='$producto'";

        $query = $this->db->query($consulta);

        foreach($query->result() as $row){
            $datos= array(
                'id_producto'=>$row->id_producto,
                'nombre'=>(empty($row->nombre)?"[N/R]":$row->nombre),
                'id_sublinea'=>(empty($row->id_sublinea)?"[N/R]":$row->id_sublinea),
                'nombreSublinea'=>(empty($row->id_sublinea)?"[N/R]":$lineas[$row->id_sublinea]['sublinea']),
                'id_linea'=>(empty($row->id_sublinea)?"[N/R]":$lineas[$row->id_sublinea]['id_linea']),
                'nombreLinea'=>(empty($row->id_sublinea)?"[N/R]":$lineas[$row->id_sublinea]['nombre_linea']),
            );
            $response[]=$datos;
        }
        return $response;
    }

    public function getLineasSublineas(){
        $consulta="
        SELECT A.id AS id_sublinea,
            A.nombre AS sublinea,
            B.nombre AS nombre_linea,
            A.id AS id_linea
        FROM `sublinea` A
        INNER JOIN linea B ON B.id=A.id_linea";

        $query = $this->db->query($consulta);

        foreach($query->result() as $row){
            $datos= array(
                'id_sublinea'=>$row->id_sublinea,
                'sublinea'=>$row->sublinea,
                'nombre_linea'=>$row->nombre_linea,
                'id_linea'=>$row->id_linea
            );

            $response[$row->id_sublinea]=$datos;
        }
        return $response;
    }

    public function getZonaCanal(){
        $consulta="
        SELECT A.id AS id_zona,
       A.nombre AS nombreZona,
       B.id AS id_canal,
       B.nombre AS nombreCanal
        FROM zona A
        INNER JOIN canal B ON B.id=A.id_canal";

        $query = $this->db->query($consulta);

        foreach($query->result() as $row){
            $datos= array(
                'id_zona'=>$row->id_zona,
                'nombreZona'=>$row->nombreZona,
                'nombreCanal'=>$row->nombreCanal,
                'id_canal'=>$row->id_canal
            );

            $response[$row->id_zona]=$datos;
        }
        return $response;
    }

    public function getZonasPorCanal($canal){
        $consulta="
        SELECT A.id AS id_zona,
       A.nombre AS nombreZona,
       B.id AS id_canal,
       B.nombre AS nombreCanal
        FROM zona A
        INNER JOIN canal B ON B.id=A.id_canal
        WHERE B.id=$canal";

        $query = $this->db->query($consulta);

        foreach($query->result() as $row){
            $datos= array(
                'id_zona'=>$row->id_zona,
                'nombreZona'=>$row->nombreZona,
                'nombreCanal'=>$row->nombreCanal,
                'id_canal'=>$row->id_canal
            );

            $response[$row->id_zona]=$datos;
        }
        return $response;
    }

    public function getNombresZonasPorCanal($canal){
        $consulta="
        SELECT 
        A.nombre AS nombreZona
        FROM zona A
        WHERE A.id_canal=$canal";

        $query = $this->db->query($consulta);

        foreach($query->result() as $row){
            $datos[]= $row->nombreZona;

            $response=$datos;
        }
        return $response;
    }

    public function getNombreCanal($canal){
        $consulta="
        SELECT 
        A.nombre AS nombreCanal
        FROM canal A
        WHERE A.id=$canal";

        $query = $this->db->query($consulta);

        foreach($query->result() as $row){
            $datos[]= $row->nombreCanal;

            $response=$datos;
        }
        return $response;
    }

    public function getCantidadZona($producto){
        ini_set('max_execution_time', 0); 
        ini_set('memory_limit','2048M');
        $zona=null;
        $anoActual=date("Y");
        $totalCanal=null;
        $zonaCanal=$this->getZonaCanal();

        $consulta="
        SELECT 
        SUBSTRING(E.fecha_documento,1,4) AS ano,
        E.tipo_documento as tipoDocumento,
        SUM(A.cantidad*a.valor) as totalZona,
        SUM(A.cantidad) as cantidad,
        C.nombre AS nombreCanal,
        C.id AS id_canal,
        Z.nombre AS nombreZona,
        Z.id AS id_zona
        FROM detalle_documento A
        INNER JOIN documento E ON E.id=A.id_documento
        INNER JOIN canal C ON C.id=E.id_canal
        INNER JOIN zona Z ON Z.id=E.id_zona

        WHERE
                    E.fecha_documento >= '".($anoActual-3)."0101' AND
                    E.fecha_documento <= '".($anoActual)."1231' AND
                    A.id_producto = '$producto'
                    
        GROUP BY SUBSTRING(E.fecha_documento,1,4),E.id_zona,E.tipo_documento";

        $query = $this->db->query($consulta);

        foreach($query->result() as $row){
            $datos= array(
                'ano'=>$row->ano,
                'tipoDocumento'=>$row->tipoDocumento,
                'totalZona'=>$row->totalZona,
                'cantidad'=>$row->cantidad,
                'nombreCanal'=>$row->nombreCanal,
                'nombreZona'=>$row->nombreZona,
                'id_zona'=>$row->id_zona,
                'id_canal'=>$row->id_canal
            );

            if(empty($productos[$row->id_zona]['info']))
                $zona[$row->ano][$row->id_zona]['info']=$datos;

            $zona[$row->ano][$row->id_zona][trim($row->tipoDocumento)]=array('totalZona'=>$row->totalZona,
                                                                     'cantidad'=>$row->cantidad);
        }

        $anosAnteriores=$anoActual-3;
        for($i=0;$i<=3;$i++){
            //$anosAnteriores+$i
            if(!empty($zona[$anosAnteriores+$i])){
                foreach($zona[$anosAnteriores+$i] as $clave =>$valor){
                    $factura=@$valor['FF']['totalZona'];
                    $nota=@$valor['NV']['totalZona'];

                    $f=(empty($factura)?0:$factura);
                    $n=(empty($nota)?0:$nota);

                    $f=explode('.', $f);
                    $n=explode('.', $n);
                    $ff=$f[0];
                    $nv=$n[0];
                    $diferencia=$ff-$nv;

                    $cantidad=@$valor['FF']['cantidad'];
                    $cantidadDescontada=@$valor['NV']['cantidad'];
                    $f1=(empty($cantidad)?0:$cantidad);
                    $n1=(empty($cantidadDescontada)?0:$cantidadDescontada);

                    $f1=explode('.', $f1);
                    $n1=explode('.', $n1);

                    $ff1=$f1[0];
                    $nv1=$n1[0];
                    $diferenciaCantidad=$ff1-$nv1;
                   
                    $canalAno[$clave][$anosAnteriores+$i]=array(/*'nombreCanal'=>$zonaCanal[$clave]['nombreCanal'],
                                                                            'nombreZona'=>$zonaCanal[$clave]['nombreZona'],*/
                                                                            'diferenciaNeto'=>$diferencia,
                                                                            'diferenciaCantidad'=>$diferenciaCantidad);
                    /*@carmabol*/
                    if(empty($canalAno[$clave]['sumatoriaProducto']))
                        $canalAno[$clave]['sumatoriaProducto']=0;

                    $canalAno[$clave]['sumatoriaProducto']+=$diferenciaCantidad;
                    
                    if(empty($totalCanal[$valor['info']['id_canal']]))
                        $totalCanal[$valor['info']['id_canal']]=0;

                    $totalCanal[$valor['info']['id_canal']]+=$diferenciaCantidad;
                    


                    /*
                    $canalAno[$clave]=array('nombreCanal'=>$zonaCanal[$clave]['nombreCanal'],
                                            'nombreZona'=>$zonaCanal[$clave]['nombreZona']);*/

                }
            }

            foreach($zonaCanal as $id => $v){
                $canalAno[$id]['id_canal']=$zonaCanal[$id]['id_canal'];
                $canalAno[$id]['nombreCanal']=$zonaCanal[$id]['nombreCanal'];
                $canalAno[$id]['id_zona']=$zonaCanal[$id]['id_zona'];
                $canalAno[$id]['nombreZona']=$zonaCanal[$id]['nombreZona'];


                if(empty($canalAno[$id][$anosAnteriores+$i])){
                    $canalAno[$id][$anosAnteriores+$i]=array(/*'nombreCanal'=>$zonaCanal[$id]['nombreCanal'],
                                                                'nombreZona'=>$zonaCanal[$id]['nombreZona'],*/
                                                                'diferenciaNeto'=>0,
                                                                'diferenciaCantidad'=>0);                                  
                }
            }
        }
        return $canalAno;
    }

    public function getCountCanal(){
        $consulta="
        SELECT COUNT(*) AS conteo FROM canal";

        $query = $this->db->query($consulta);

        foreach($query->result() as $row){
            $conteo=$row->conteo;
        }
        return $conteo;

    }

    public function getCountZonaPorCanal($canal){
        $consulta="
        SELECT COUNT(*) AS conteo FROM zona WHERE id_canal=$canal";

        $query = $this->db->query($consulta);

        foreach($query->result() as $row){
            $conteo=$row->conteo;
        }
        return $conteo;

    }

     public function getDatosGraficaCanal($producto,$canal){
        ini_set('max_execution_time', 0); 
        ini_set('memory_limit','2048M');
        $zona=null;
            $anoActual=date("Y");

            $zonaCanal=$this->getZonaCanal();
            $totalCanal=0;
            $consulta="
            SELECT 
            SUBSTRING(E.fecha_documento,1,4) AS ano,
            E.tipo_documento as tipoDocumento,
            SUM(A.cantidad*a.valor) as totalZona,
            SUM(A.cantidad) as cantidad,
            C.nombre AS nombreCanal,
            Z.nombre AS nombreZona,
            Z.id AS id_zona
            FROM detalle_documento A
            INNER JOIN documento E ON E.id=A.id_documento
            INNER JOIN canal C ON C.id=E.id_canal
            INNER JOIN zona Z ON Z.id=E.id_zona

            WHERE
                        E.fecha_documento >= '".($anoActual-3)."0101' AND
                        E.fecha_documento <= '".($anoActual)."1231' AND
                        A.id_producto = '$producto'
                        AND Z.id_canal='$canal'
                        
            GROUP BY SUBSTRING(E.fecha_documento,1,4),E.id_zona,E.tipo_documento";

            $query = $this->db->query($consulta);

            foreach($query->result() as $row){
                $datos= array(
                    'ano'=>$row->ano,
                    'tipoDocumento'=>$row->tipoDocumento,
                    'totalZona'=>$row->totalZona,
                    'cantidad'=>$row->cantidad,
                    'nombreCanal'=>$row->nombreCanal,
                    'nombreZona'=>$row->nombreZona,
                    'id_zona'=>$row->id_zona
                );
                if(empty($productos[$row->id_zona]['info']))
                    $zona[$row->ano][$row->id_zona]['info']=$datos;

                $zona[$row->ano][$row->id_zona][trim($row->tipoDocumento)]=array('totalZona'=>$row->totalZona,
                                                                         'cantidad'=>$row->cantidad);
            }

            $anosAnteriores=$anoActual-3;
            for($i=0;$i<=3;$i++){
                //$anosAnteriores+$i
                if(!empty($zona[$anosAnteriores+$i])){
                    foreach($zona[$anosAnteriores+$i] as $clave =>$valor){
                        $factura=@$valor['FF']['totalZona'];
                        $nota=@$valor['NV']['totalZona'];

                        $f=(empty($factura)?0:$factura);
                        $n=(empty($nota)?0:$nota);

                        $f=explode('.', $f);
                        $n=explode('.', $n);
                        $ff=$f[0];
                        $nv=$n[0];
                        $diferencia=$ff-$nv;

                        $cantidad=@$valor['FF']['cantidad'];
                        $cantidadDescontada=@$valor['NV']['cantidad'];
                        $f1=(empty($cantidad)?0:$cantidad);
                        $n1=(empty($cantidadDescontada)?0:$cantidadDescontada);

                        $f1=explode('.', $f1);
                        $n1=explode('.', $n1);

                        $ff1=$f1[0];
                        $nv1=$n1[0];
                        $diferenciaCantidad=$ff1-$nv1;
                       
                        $canalAno[$anosAnteriores+$i][$clave]=array(/*'nombreCanal'=>$zonaCanal[$clave]['nombreCanal'],
                                                                                'nombreZona'=>$zonaCanal[$clave]['nombreZona'],*/
                                                                                'diferenciaNeto'=>$diferencia,
                                                                                'diferenciaCantidad'=>$diferenciaCantidad
                                                                                );
                        if($anosAnteriores+$i==$anoActual)
                            $totalCanal+=$diferencia;
                        /*
                        $canalAno[$clave]=array('nombreCanal'=>$zonaCanal[$clave]['nombreCanal'],
                                                'nombreZona'=>$zonaCanal[$clave]['nombreZona']);*/

                    }
                }
                $zonasPorCanal=$this->getZonasPorCanal($canal);

                foreach($zonasPorCanal as $id => $v){
                    if(empty($canalAno[$anosAnteriores+$i][$id]['diferenciaNeto'])){
                        $canalAno[$anosAnteriores+$i][$id]=array('diferenciaNeto'=>0,
                                                                 'diferenciaCantidad'=>0);                                  
                    }

                    $canalAno[$anosAnteriores+$i][$id]['info']['id_canal']=$zonasPorCanal[$id]['id_canal'];
                    $canalAno[$anosAnteriores+$i][$id]['info']['nombreCanal']=$zonasPorCanal[$id]['nombreCanal'];
                    $canalAno[$anosAnteriores+$i][$id]['info']['id_zona']=$zonasPorCanal[$id]['id_zona'];
                    $canalAno[$anosAnteriores+$i][$id]['info']['nombreZona']=$zonasPorCanal[$id]['nombreZona'];

                    

                    if($totalCanal==0)
                        $canalAno[$anosAnteriores+$i][$id]['porcentajeParticipacion']=0;
                    else{
                        if($anosAnteriores+$i==$anoActual)
                        $canalAno[$anosAnteriores+$i][$id]['porcentajeParticipacion']=round(($canalAno[$anosAnteriores+$i][$id]['diferenciaNeto']/$totalCanal)*100,2);
                    }
                }
            }

            return $canalAno;
     }

    public function topTepClientes($producto){
        ini_set('max_execution_time', 0); 
        ini_set('memory_limit','2048M');
        $anoActual=date("Y");
        $response=null;
        $consulta="
        SELECT 
            E.tipo_documento as tipoDocumento,
            E.id_cliente AS id_cliente,
            SUM(A.cantidad*A.valor) as totalValor
            FROM detalle_documento A
            INNER JOIN documento E ON E.id=A.id_documento
            
            WHERE
                        E.fecha_documento >= '".($anoActual-1)."0101' AND
                        E.fecha_documento <= '".($anoActual-1)."1231' AND
                        A.id_producto= '$producto'
                        
            GROUP BY SUBSTRING(E.fecha_documento,1,4),E.id_cliente,E.tipo_documento
            ";
        //    $response=$consulta;
        
        $query = $this->db->query($consulta);
        
        
        foreach($query->result() as $row){
            $datos= array(
                'tipoDocumento'=>$row->tipoDocumento,
                'id_cliente'=>$row->id_cliente,
                'totalValor'=>$row->totalValor
            );
            $clientes[trim($row->id_cliente)][trim($row->tipoDocumento)]=$datos;
        }

        if(!empty($clientes)){
            foreach($clientes AS $id => $v){
                $factura=@$v['FF']['totalValor'];
                $nota=@$v['NV']['totalValor'];

                $f=(empty($factura)?0:$factura);
                $n=(empty($nota)?0:$nota);

                $f=explode('.', $f);
                $n=explode('.', $n);

                $ff=$f[0];
                $nv=$n[0];
                $diferencia=$ff-$nv;

                $p[$id]=$diferencia;//--ano,linea
            }
            $response=null;
            arsort($p);

            $cantidadClientes=0;

            foreach ($p as $key => $val){
                $response[$key]=array("id_cliente"=>$key,"orden"=>$cantidadClientes);
                $cantidadClientes++;
                if($cantidadClientes==10)
                    break;
            }
        }
        
        return $response;
    }


    public function getInfoTopTenClientes($arrayTopTen,$producto){
        ini_set('max_execution_time', 0); 
        ini_set('memory_limit','2048M');
        $topTen="";
        $sublineas=$this->getLineasSublineas();

        foreach($arrayTopTen as $key => $valor){
            $topTen.=$valor['id_cliente'].",";
        }
        $topTen=substr($topTen,0,strlen($topTen)-1);

        $anoActual=date("Y");
        $consulta="
            SELECT 
            SUBSTRING(E.fecha_documento,1,4) AS ano,
            E.tipo_documento as tipoDocumento,
            E.id_cliente AS id_cliente,
            B.nombre AS nombreCliente,
            SUM(A.cantidad) AS cantidad,
            SUM(A.cantidad*A.valor) as totalValor
            FROM detalle_documento A
            RIGHT JOIN documento E ON E.id=A.id_documento
            RIGHT JOIN cliente B ON E.id_cliente=B.id
            WHERE
                        E.fecha_documento >= '".($anoActual-3)."0101' AND
                        E.fecha_documento <= '".($anoActual)."1231' AND
                        A.id_producto= '$producto' AND
                        E.id_cliente IN ($topTen)
                        
            GROUP BY SUBSTRING(E.fecha_documento,1,4),E.id_cliente,E.tipo_documento
            ";
        $query = $this->db->query($consulta);
        $totalClienteAno=null;

        foreach($query->result() as $row){
            $datos= array(
                'ano'=>$row->ano,
                'tipoDocumento'=>$row->tipoDocumento,
                'nombreCliente'=>$row->nombreCliente,
                'id_cliente'=>$row->id_cliente,
                'cantidad'=>$row->cantidad,
                'totalValor'=>$row->totalValor
            );
            $totalClientesAno[trim($row->ano)][trim($row->id_cliente)][trim($row->tipoDocumento)]=$datos;
            $totalClientesAno[trim($row->ano)][trim($row->id_cliente)]['datos']=$datos;
        }

        $p=null;
        $clientesAno=null;
        $orden=0;
        foreach ($totalClientesAno as $clave => $valor){

            foreach($valor as $c => $v){
                $factura=@$v['FF']['totalValor'];
                $nota=@$v['NV']['totalValor'];
                $cantidad=@$v['FF']['cantidad'];
                $cantidadDescontada=@$v['NV']['cantidad'];
                $datos=@$v['datos'];

                $f=(empty($factura)?0:$factura);
                $n=(empty($nota)?0:$nota);

                $f=explode('.', $f);
                $n=explode('.', $n);

                $ff=$f[0];
                $nv=$n[0];
                $diferenciaTotal=$ff-$nv;

                $f1=(empty($cantidad)?0:$cantidad);
                $n1=(empty($cantidadDescontada)?0:$cantidadDescontada);

                $f1=explode('.', $f1);
                $n1=explode('.', $n1);

                $ff1=$f1[0];
                $nv1=$n1[0];
                $diferenciaCantidad=$ff1-$nv1;


                if(empty($clientesAno[$arrayTopTen[$datos['id_cliente']]['orden']]['info'])){
                    $clientesAno[$arrayTopTen[$datos['id_cliente']]['orden']]['info']=array('id_cliente'=>$datos['id_cliente'],
                                        'nombreCliente'=>$datos['nombreCliente']
                                       
                    );

                }
                
                $clientesAno[$arrayTopTen[$datos['id_cliente']]['orden']][$clave] = array(
                                                                'cantidad'=>$diferenciaCantidad,
                                                                'totalValor'=>$diferenciaTotal);
                
                //$ano[$clave][$c]=$diferencia;//--ano,linea
            }$orden++;
        }

        return $clientesAno;
    }

    public function getInfoProductos($producto){

        $consulta="
            SELECT P.`id_producto` as id_producto,P.`nombre` as nombre, s.nombre as sublinea, l.nombre as linea FROM `producto` P
            INNER JOIN sublinea s ON P.`id_sublinea`=s.id
            INNER JOIN linea l ON L.id=S.id_linea
            where P.id_producto='$producto'
            ";

        $query = $this->db->query($consulta);

        foreach($query->result() as $row){
            $datos= array(
                'id_producto'=>$row->id_producto,
                'nombre'=>(empty($row->nombre)?"[N/R]":$row->nombre),
                'sublinea'=>(empty($row->sublinea)?"[N/R]":$row->sublinea),
                'linea'=>(empty($row->linea)?"[N/R]":$row->linea)
            );
        }
        $response[]=$datos;

        return $response;
    }
}
