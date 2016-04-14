<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cnt_producto extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		$this->load->view('vst_producto');
	}

	public function getProduct(){
		$product=$this->mdl_producto->getProduct();
		echo $product;
        //echo json_encode($customer);
	}

	public function getCantidadZona($producto){
		$response=$this->mdl_producto->getCantidadZona($producto);
		$conteoCanal=$this->mdl_producto->getCountCanal();
		//$totalPorCanal=
		echo json_encode(array($response,count($response),$conteoCanal));
	}

	public function getCountCanal(){
		$conteoCanal=$this->mdl_producto->getCountCanal();
		echo json_encode($conteoCanal);
	}

	public function getNombreCanal($canal){
		$nombreCanal=$this->mdl_producto->getNombreCanal($canal);
		echo json_encode($nombreCanal);
	}
	

	public function getNetoZonasPorAno(){
		/*=$_REQUEST['producto'];
		=$_REQUEST['canal'];
		*/
		$anoActual=date("Y");
		$producto=$_POST['producto'];
		$canal=$_POST['canal'];
		$periodo=$this->mdl_producto->getDatosGraficaCanal($producto,$canal);
		$conteoZonas=$this->mdl_producto->getCountZonaPorCanal($canal);
		$zonas=$this->mdl_producto->getZonasPorCanal($canal);

		$result2=null;
		for($i=$anoActual-3;$i<=$anoActual;$i++){
			//--ano,linea
			$data=null;
			//for($j=1;$j<=$conteoZonas;$j++){
			foreach($zonas as $j=>$v){

				$valor=@$periodo[$i][$j]['diferenciaNeto'];//--ano,linea
				$valor=(empty($valor)?0:$valor);
				$data[]=$valor;

				if($i==$anoActual)
				$result2[]=array("name" => $periodo[$i][$j]['info']['nombreZona'],"y"=> $periodo[$i][$j]['porcentajeParticipacion']);
			}

			$result[]=array("_colorIndex"=> ($i-($anoActual-3)),"data" => $data,"name" => $i);
			
		}
		echo json_encode(array("0"=>$result,"1"=>$result2));
		//echo json_encode($result);
	}

	public function getNombresZonasPorCanal($canal){
		$response=$this->mdl_producto->getNombresZonasPorCanal($canal);
		echo json_encode($response);
	}

	public function getTopTenClientes($producto){
		$result=null;
		$arrayTopTen=@$this->mdl_producto->topTepClientes($producto);
		//echo "termino";
		//die($arrayTopTen);
		if(!empty($arrayTopTen)){
			$result=$this->mdl_producto->getInfoTopTenClientes($arrayTopTen,$producto);
		}
		echo json_encode($result);
	}
	/*Info Grafica de Barras

	getDatosGraficaCanal($producto,$canal)
	public function getFacturacionPorPeriodo($cliente){
		$periodo=$this->mdl_cliente->getFacturacionPorCliente($cliente);
	    $arrayGrafica=null;
	    $anoActual=date("Y");

	    for($i=$anoActual-3;$i<=$anoActual;$i++){
	        if(!empty($periodo[$i])){
	            $data=null;
	            for($j=1;$j<=12;$j++){
	                $valor=@$periodo[$i][str_repeat("0",(2-strlen($j))).$j];
	                $valor=(empty($valor)?0:$valor);
	                $data[]=$valor;
	            }
	            $result[]=array("data" => $data,"name" => $i);
	        }
	    }

	    //echo $arrayGrafica;
	    echo json_encode($result);
	}*/


	public function test(){
		
		$anoActual=date("Y");
		$producto=1010037;
		$canal=1;
		$periodo=$this->mdl_producto->getDatosGraficaCanal($producto,$canal);
		$conteoZonas=$this->mdl_producto->getCountZonaPorCanal($canal);
		$zonas=$this->mdl_producto->getZonasPorCanal($canal);

		$result2=null;
		for($i=$anoActual-3;$i<=$anoActual;$i++){
			//--ano,linea
			$data=null;

			foreach($zonas as $j=>$v){
				$valor=@$periodo[$i][$j]['diferenciaNeto'];//--ano,linea
				$valor=(empty($valor)?0:$valor);
				$data[]=$valor;

				if($i==$anoActual)
				$result2[]=array("name" => $periodo[$i][$j]['info']['nombreZona'],"y"=> $periodo[$i][$j]['porcentajeParticipacion']);
			}

			$result[]=array("data" => $data,"name" => $i);
			
		}
		echo json_encode(array($result,$result2));
	}

	public function getInfoProducto($producto){
		//--epa
		$response=$this->mdl_producto->getInfoProductos($producto);
		echo json_encode($response);
	}

	public function test2(){
		var_dump($this->mdl_producto->getDatosGraficaCanal(1010037,1));
	}
	
	/*
	public function getFacturacionPorPeriodo($cliente){
		$periodo=$this->mdl_cliente->getFacturacionPorCliente($cliente);
	    $arrayGrafica=null;
	    $anoActual=date("Y");

	    for($i=$anoActual-3;$i<=$anoActual;$i++){
	        if(!empty($periodo[$i])){
	            $data=null;
	            for($j=1;$j<=12;$j++){
	                $valor=@$periodo[$i][str_repeat("0",(2-strlen($j))).$j];
	                $valor=(empty($valor)?0:$valor);
	                $data[]=$valor;
	            }
	            $result[]=array("data" => $data,"name" => $i);
	        }
	    }

	    //echo $arrayGrafica;
	    echo json_encode($result);
	}

	public function getValoresLineasPorAno($cliente){
		$anoCategoria=$this->mdl_cliente->getTotalPorLineas($cliente);
		$anoActual=date("Y");
		$lineas=$this->mdl_cliente->getLineas();
		for($i=$anoActual-3;$i<=$anoActual;$i++){
			//--ano,linea
			$data=null;
			for($j=0;$j<count($lineas);$j++){
				$valor=@$anoCategoria[$i][$lineas[$j]];//--ano,linea
				$valor=(empty($valor)?0:$valor);
				$data[]=$valor;
			}

			$result[]=array("data" => $data,"name" => $i);
		}
		echo json_encode($result);
	}

	public function getLineas(){
		echo json_encode($this->mdl_cliente->getLineas());
	}

	public function getTopTenProductos($cliente){
		$arrayTopTen=$this->mdl_cliente->topTepProductos($cliente);
		$result=$this->mdl_cliente->getInfoTopTenProducts($arrayTopTen,$cliente);
		echo json_encode($result);
		//var_dump($this->mdl_cliente->getInfoTopTenProducts($arrayTopTen,$cliente));
	}

	public function getInfoCliente($cliente){
		$arrayInfoCliente=$this->mdl_cliente->getInfoCliente($cliente);
		$result=$arrayInfoCliente;
		echo json_encode($result);
	}

	public function getExhibidores($cliente){
		$productosExhibidores=$this->mdl_cliente->getExhibidores($cliente);
		echo json_encode($productosExhibidores);
	}

	public function test($cliente){
		$anoCategoria=$this->mdl_cliente->getExhibidores($cliente);
		var_dump($anoCategoria);
	}*/
}
