<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mdl_cliente extends CI_Model {
    function __construct(){
        parent::__construct();
       //-- $this->load->database();
    }
    /*
     *Funciones a utilizar en el Modelo
     * INSERTAR
     * $this->db->insert('tabla',array('campo1'=> $dato1,..,'campon'=> $datoN,))
     */
    function getCustomer(){
        /*$this->db->where('login',$login);
        $this->db->where('password',$password);
        $query=$this->db->get('is_user');*/

        $page=(empty($_POST['page'])?0:$_POST['page']);
        $page = max($page, 1);
        $criteria=(empty($_POST['criteria'])?"":$_POST['criteria']);
        
        $customers=false;

        $this->db->select('id,nombre,nit,sucursal');
        $this->db->from('cliente');
        $this->db->where("id LIKE '%$criteria%' OR nombre LIKE '%$criteria%'");

        $query = $this->db->get();

        $total=$query->num_rows();

        if($query->num_rows() > 0)
        {
            foreach ($query->result() as $row)
            {
                $customers[] = array('id'=> $row->id, 'text'=> "[".$row->nit."-".$row->sucursal."] ".$row->nombre);
            }
        }
        return json_encode(array(
          'results' => $customers,
          'total' => $total
        )); 
    }

public function getFacturacionPorCliente($cliente){
    $anoActual=date("Y");
    $mesActual=date("m");
    $anosAnteriores=$anoActual-3;

    $nit=substr($cliente,0,strlen($cliente)-2);
    $sucursal=substr($cliente, -2);

    $consulta="
        SELECT
            
            SUBSTRING(fecha_documento,1,6) AS periodo,
            tipo_documento as tipoDocumento,
            SUM((valor_documento)) as totalDocumento
        FROM
            documento
        WHERE
            fecha_documento >= '".$anosAnteriores."0101' AND
            fecha_documento <= '".$anoActual."1231' AND
            id_cliente like '$nit".($sucursal!='00'?"$sucursal' ":"__' ")."

            GROUP BY SUBSTRING(fecha_documento,1,6),tipoDocumento
        ";

    $query = $this->db->query($consulta);
    $totalPeriodos=null;
    $periodo=null;

    foreach($query->result() as $row){
        $datos= array(
            'periodo'=>$row->periodo,
            'tipoDocumento'=>$row->tipoDocumento,
            'totalDocumento'=>$row->totalDocumento
        );

        $totalPeriodos[trim($row->periodo)][trim($row->tipoDocumento)]=$datos;
    }

    if(!empty($totalPeriodos)){
        foreach ($totalPeriodos as $clave => $valor) {
            $factura=@$valor['FF']['totalDocumento'];
            $nota=@$valor['NV']['totalDocumento'];

            $f=(empty($factura)?0:$factura);
            $n=(empty($nota)?0:$nota);

            $f=explode('.', $f);
            $n=explode('.', $n);

            $ff=$f[0];
            $nv=$n[0];
            $diferencia=$ff-$nv;
            $periodo[substr($clave,0,4)][substr($clave,4,2)]=$diferencia;
        }
    }        


    return $periodo;
}

public function getTotalPorLineas($cliente){
    ini_set('max_execution_time', 0); 
    ini_set('memory_limit','2048M');
    $anoActual=date("Y");
    $mesActual=date("m");
    $anosAnteriores=$anoActual-3;
    $lineas=$this->getLineasSublineas();
    $ano=null;
    
    $nit=substr($cliente,0,strlen($cliente)-2);
    $sucursal=substr($cliente, -2);

    $consulta="
        SELECT 
        SUBSTRING(E.fecha_documento,1,4) AS ano,
        E.tipo_documento as tipoDocumento,
        SUM(A.cantidad*a.valor) as totalSublinea,
        B.id_sublinea AS id_sublinea
        FROM detalle_documento A
        INNER JOIN documento E ON E.id=A.id_documento
        INNER JOIN producto B ON A.id_producto=B.id_producto

        WHERE
                    E.fecha_documento >= '".$anosAnteriores."0101' AND
                    E.fecha_documento <= '".$anoActual."1231' AND
                    E.id_cliente like '$nit".($sucursal!='00'?"$sucursal' ":"__' ")."
                    
        GROUP BY SUBSTRING(E.fecha_documento,1,4),B.id_sublinea,E.tipo_documento
        ORDER BY SUBSTRING(E.fecha_documento,1,4),B.id_sublinea,E.tipo_documento
        ";
    $query = $this->db->query($consulta);

    foreach($query->result() as $row){
        $datos= array(
            'ano'=>$row->ano,
            'tipoDocumento'=>$row->tipoDocumento,
            'sublinea'=>$row->id_sublinea,
            'totalSublinea'=>$row->totalSublinea
        );
        $totalAno[trim($row->ano)][trim($row->id_sublinea)][trim($row->tipoDocumento)]=$datos;
    }

    if(!empty($totalAno)){
        for($i=0;$i<=3;$i++){
            if(!empty($totalAno[$anosAnteriores+$i])){
                foreach($totalAno[$anosAnteriores+$i] as $clave =>$valor){
                    $factura=@$valor['FF']['totalSublinea'];
                    $nota=@$valor['NV']['totalSublinea'];

                    $f=(empty($factura)?0:$factura);
                    $n=(empty($nota)?0:$nota);

                    $f=explode('.', $f);
                    $n=explode('.', $n);
                    $ff=$f[0];
                    $nv=$n[0];
                    $diferencia=$ff-$nv;

                    if(empty($ano[$anosAnteriores+$i][$lineas[$clave]['nombre_linea']])){
                        $ano[$anosAnteriores+$i][$lineas[$clave]['nombre_linea']]=0;
                    }

                    $ano[$anosAnteriores+$i][$lineas[$clave]['nombre_linea']]+=$diferencia;
                }
            }
        }
    }
    
    return $ano;
}

public function getLineas(){
    $lineas=null;
    $consulta="SELECT * FROM Linea";
    $query = $this->db->query($consulta);

    foreach($query->result() as $row){
        $lineas[]=$row->nombre;
    }

    return $lineas;
}


public function topTepProductos($cliente){
    $anoActual=date("Y");
    $response=null;

    $nit=substr($cliente,0,strlen($cliente)-2);
    $sucursal=substr($cliente, -2);

    $consulta="
        SELECT 
        E.tipo_documento as tipoDocumento,
        A.id_producto AS id_producto,
        SUM(A.cantidad*A.valor) as totalValor
        FROM detalle_documento A
        INNER JOIN documento E ON E.id=A.id_documento
        
        WHERE
                    E.fecha_documento >= '".($anoActual-1)."0101' AND
                    E.fecha_documento <= '".($anoActual-1)."1231' AND
                    E.id_cliente like '$nit".($sucursal!='00'?"$sucursal' ":"__' ")."
                    
        GROUP BY SUBSTRING(E.fecha_documento,1,4),A.id_producto,E.tipo_documento
        ";
    //    $response=$consulta;
    
    $query = $this->db->query($consulta);
    
    
    foreach($query->result() as $row){
        $datos= array(
            'tipoDocumento'=>$row->tipoDocumento,
            'id_producto'=>$row->id_producto,
            'totalValor'=>$row->totalValor
        );
        $productos[trim($row->id_producto)][trim($row->tipoDocumento)]=$datos;
    }

    if(!empty($productos)){
        foreach($productos AS $id => $v){
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

        $cantidadProductos=0;

        foreach ($p as $key => $val){
            $response[$key]=array("id_producto"=>$key,"orden"=>$cantidadProductos);
            $cantidadProductos++;
            if($cantidadProductos==10)
                break;
        }
    }
    
    return $response;
}


public function getNotasCredito($cliente){
    $anoActual=date("Y");
    $response=null;

    $nit=substr($cliente,0,strlen($cliente)-2);
    $sucursal=substr($cliente, -2);


    $motivos=$this->getMotivos();



    $consulta="
        SELECT 
        SUBSTRING(fecha_documento,1,4) AS ano, 
        `motivo` as motivo, 
        sum(valor_documento) as totalValor,
        count(*) as cantidadValor
        FROM `documento` 
        WHERE 
        tipo_documento='NV' AND
        fecha_documento >= '".($anoActual-3)."0101' AND
        fecha_documento <= '".($anoActual)."1231' AND
        id_cliente like '$nit".($sucursal!='00'?"$sucursal' ":"__' ")."
        GROUP BY motivo,SUBSTRING(fecha_documento,1,4)
        ";
    
    $query = $this->db->query($consulta);
    
    
    foreach($query->result() as $row){
        $datos= array(
            'totalValor'=>$row->totalValor,
            'cantidadNV'=>$row->cantidadValor,
            'motivo'=>$motivos[$row->motivo]
        );

        $response[trim($row->motivo)][trim($row->ano)]=$datos;
    }

    return $response;
}

public function getMotivos(){
    $consulta="
        SELECT * FROM `motivo`
    ";

    $query = $this->db->query($consulta);

    foreach($query->result() as $row){
        $datos= array(
            'id'=>$row->id,
            'nombre'=>$row->nombre
        );

        $response[trim($row->id)]=$row->nombre;
    }

    return $response;
}



public function getInfoTopTenProducts($arrayTopTen,$cliente){
    $topTen="";
    $sublineas=$this->getLineasSublineas();

    $nit=substr($cliente,0,strlen($cliente)-2);
    $sucursal=substr($cliente, -2);

    foreach($arrayTopTen as $key => $valor){
        $topTen.=$valor['id_producto'].",";
    }
    $topTen=substr($topTen,0,strlen($topTen)-1);

    $anoActual=date("Y");
    $consulta="
        SELECT 
        SUBSTRING(E.fecha_documento,1,4) AS ano,
        E.tipo_documento as tipoDocumento,
        A.id_producto AS id_producto,
        B.nombre AS nombreProducto,
        B.id_sublinea AS id_sublinea,
        SUM(A.cantidad) AS cantidad,
        SUM(A.cantidad*A.valor) as totalValor
        FROM detalle_documento A
        RIGHT JOIN documento E ON E.id=A.id_documento
        RIGHT JOIN producto B ON A.id_producto=B.id_producto
        WHERE
                    E.fecha_documento >= '".($anoActual-3)."0101' AND
                    E.fecha_documento <= '".($anoActual)."1231' AND
                    E.id_cliente like '$nit".($sucursal!='00'?"$sucursal' ":"__' ")." AND
                    A.id_producto IN ($topTen)
                    
        GROUP BY SUBSTRING(E.fecha_documento,1,4),A.id_producto,E.tipo_documento
        ";
    $query = $this->db->query($consulta);
    $totalProductosAno=null;

    foreach($query->result() as $row){
        $datos= array(
            'ano'=>$row->ano,
            'tipoDocumento'=>$row->tipoDocumento,
            'nombreProducto'=>$row->nombreProducto,
            'id_producto'=>$row->id_producto,
            'id_sublinea'=> $row->id_sublinea,
            'cantidad'=>$row->cantidad,
            'totalValor'=>$row->totalValor
        );
        $totalProductosAno[trim($row->ano)][trim($row->id_producto)][trim($row->tipoDocumento)]=$datos;
        $totalProductosAno[trim($row->ano)][trim($row->id_producto)]['datos']=$datos;
    }

    $p=null;
    $productosAno=null;
    $orden=0;
    foreach ($totalProductosAno as $clave => $valor){

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


            if(empty($productosAno[$arrayTopTen[$datos['id_producto']]['orden']]['info'])){
                $productosAno[$arrayTopTen[$datos['id_producto']]['orden']]['info']=array('id_producto'=>$datos['id_producto'],
                                    'nombreProducto'=>$datos['nombreProducto'],
                                    'sublinea'=>$datos['id_sublinea'],
                                    'linea'=>$sublineas[$datos['id_sublinea']]['nombre_linea']
                                   
                );

            }
            
            $productosAno[$arrayTopTen[$datos['id_producto']]['orden']][$clave] = array(
                                                            'cantidad'=>$diferenciaCantidad,
                                                            'totalValor'=>$diferenciaTotal);
            
            //$ano[$clave][$c]=$diferencia;//--ano,linea
        }$orden++;
    }

    return $productosAno;
}


public function getLineasSublineas(){

    $consulta="
    SELECT A.id AS id_sublinea,
        A.nombre AS sublinea,
        B.nombre AS nombre_linea,
        A.id_linea AS id_linea
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

public function getFromVendedor($id_vendedor){
    $consulta="
    SELECT V.nombre as vendedor, Z.nombre as zona,C.nombre as canal FROM `documento` D INNER JOIN vendedor V ON V.id_vendedor=D.
    id_vendedor iNNER JOIN zona Z ON z.id=D.id_zona INNER JOIN canal C ON C.id=D.id_canal WHERE D.id_vendedor = '$id_vendedor'
    order by D.id desc limit 1";

    $query = $this->db->query($consulta);

    foreach($query->result() as $row){
        $datos= array(
            'vendedor'=>$row->vendedor,
            'canal'=>$row->canal,
            'zona'=>$row->zona
        );

        $response=$datos;
    }
    return $response;

}

public function getInfoCliente($cliente){

    $consulta="
    SELECT id, nit, sucursal, nombre, establecimiento, barrio, contacto, fechaCreacion, ciudad_Tercero, departamento, forma_pago, cond_pago, direccion, telefono1, telefono2, observaciones, cupo_credito,dias_gracia,vendedor 
    FROM cliente
    WHERE id= '$cliente'";

    $query = $this->db->query($consulta);

    foreach($query->result() as $row){
        $datos= array(
            'id'=>$row->id,
            'sucursal'=>(empty($row->sucursal)?"[N/R]":$row->sucursal),
            'nit'=>(empty($row->nit)?"[N/R]":$row->nit),
            'nombre'=>(empty($row->nombre)?"[N/R]":$row->nombre),
            'establecimiento'=>(empty($row->establecimiento)?"[N/R]":$row->establecimiento),
            'barrio'=>(empty($row->barrio)?"[N/R]":$row->barrio),
            'contacto'=>(empty($row->contacto)?"[N/R]":$row->contacto),
            'fechaCreacion'=>(empty($row->fechaCreacion)?"[N/R]":$row->fechaCreacion),
            'ciudad_Tercero'=>(empty($row->ciudad_Tercero)?"[N/R]":$row->ciudad_Tercero),
            'departamento'=>(empty($row->departamento)?"[N/R]":$row->departamento),
            'forma_pago'=>(empty($row->forma_pago)?"[N/R]":$row->forma_pago),
            'cond_pago'=>(empty($row->cond_pago)?"[N/R]":$row->cond_pago),
            'direccion'=>(empty($row->direccion)?"[N/R]":$row->direccion),
            'telefono1'=>(empty($row->telefono1)?"[N/R]":$row->telefono1),
            'telefono2'=>(empty($row->telefono2)?"[N/R]":$row->telefono2),
            'observaciones'=>(empty($row->observaciones)?"[N/R]":$row->observaciones),
            'cupo_credito'=>(empty($row->cupo_credito)?"[N/R]":$row->cupo_credito),
            'dias_gracia'=>(empty($row->dias_gracia)?"[N/R]":$row->dias_gracia),
            'id_vendedor'=>(empty($row->vendedor)?"[N/R]":$row->vendedor)
        );
    }

    $datosVendedor=$this->getFromVendedor($datos['id_vendedor']);
    $datos['vendedor']=(empty($datosVendedor['vendedor'])?"[N/R]":$datosVendedor['vendedor']);
    $datos['zona']=(empty($datosVendedor['zona'])?"[N/R]":$datosVendedor['zona']);
    $datos['canal']=(empty($datosVendedor['canal'])?"[N/R]":$datosVendedor['canal']);

    $response[]=$datos;

    return $response;
}

public function getExhibidores($cliente){
    ini_set('max_execution_time', 0); 
    ini_set('memory_limit','2048M');

    $productos=null;
    $productosExhibidores=null;
    $anoActual=date("Y");

    $nit=substr($cliente,0,strlen($cliente)-2);
    $sucursal=substr($cliente, -2);

    $consulta="
    SELECT 
        SUBSTRING(E.fecha_documento,1,4) AS ano,
        E.tipo_documento as tipoDocumento,
        A.id_producto AS id_producto,
        B.nombre AS nombreProducto,
        SUM(A.cantidad) AS cantidad,
        SUM(A.cantidad*A.valor) as totalValor
        FROM detalle_documento A
        inner JOIN documento E ON E.id=A.id_documento
        inner JOIN producto B ON A.id_producto=B.id_producto
        WHERE
                    E.fecha_documento >= '".($anoActual-3)."0101' AND
                    E.fecha_documento <= '".($anoActual)."1231' AND
                    E.id_cliente like '$nit".($sucursal!='00'?"$sucursal' ":"__' ")." AND
                    B.id_sublinea IN (SELECT id FROM sublinea WHERE id_linea='1')
                    
        GROUP BY E.tipo_documento,A.id_producto";

    $query = $this->db->query($consulta);

    foreach($query->result() as $row){
        $datos= array(
            'tipoDocumento'=>$row->tipoDocumento,
            'nombreProducto'=>$row->nombreProducto,
            'id_producto'=>$row->id_producto
        );
        if(empty($productos[$row->id_producto]['info']))
            $productos[$row->id_producto]['info']=$datos;

        $productos[$row->id_producto][trim($row->tipoDocumento)]=array('totalValor'=>$row->totalValor,
                                                                       'cantidad'=>$row->cantidad);

    }
    
    
    if(!empty($productos)){
        foreach($productos AS $id => $v){
            $factura=@$v['FF']['totalValor'];
            $nota=@$v['NV']['totalValor'];

            $f=(empty($factura)?0:$factura);
            $n=(empty($nota)?0:$nota);

            $f=explode('.', $f);
            $n=explode('.', $n);

            $ff=$f[0];
            $nv=$n[0];
            $diferencia=$ff-$nv;

            $cantidad=@$v['FF']['cantidad'];
            $cantidadDescontada=@$v['NV']['cantidad'];
            $f1=(empty($cantidad)?0:$cantidad);
            $n1=(empty($cantidadDescontada)?0:$cantidadDescontada);

            $f1=explode('.', $f1);
            $n1=explode('.', $n1);

            $ff1=$f1[0];
            $nv1=$n1[0];
            $diferenciaCantidad=$ff1-$nv1;

            $productosExhibidores[]=array('id_producto'=>$v['info']['id_producto'],
                                             'nombreProducto'=>$v['info']['nombreProducto'],
                                             'diferenciaCantidad'=>$diferenciaCantidad,
                                             'diferenciaNeto'=>$diferencia);//--ano,linea
        }
    }
    return $productosExhibidores;
}
}
