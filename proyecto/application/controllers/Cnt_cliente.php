<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cnt_cliente extends CI_Controller {

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
		$this->load->view('vst_cliente');
	}

	public function microtime_float()
	{
		list($useg, $seg) = explode(" ", microtime());
		return ((float)$useg + (float)$seg);
	}

	public function getCustomer(){
		$customer=$this->mdl_cliente->getCustomer();
		echo $customer;
        //echo json_encode($customer);
	}

	public function getFacturacionPorPeriodo($cliente){
		$periodo=$this->mdl_cliente->getFacturacionPorCliente($cliente);
	    $arrayGrafica=null;
	    $anoActual=date("Y");
	    $result=null;
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

	public function getValoresLineasPorAno2($cliente){
		ini_set('max_execution_time', 0); 
    	ini_set('memory_limit','2048M');
		$anoCategoria=$this->mdl_cliente->getTotalPorLineas($cliente);
		$anoActual=date("Y");
		$lineas=$this->mdl_cliente->getLineas();
		$orden=null;
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

		
		//--Ordenamiento
		$order=null;

		for($i=0;$i<count($lineas);$i++)
			$order[]=$i;

		$a=$result[2]['data'];
		//var_dump($a);
		$resultOrder=null;
		$dataOrderActual=$this->insertionSort($a,$order);
		$lineasOrden=null;
		//var_dump($dataOrderActual);
		//echo "El conteo es".count($dataOrderActual);
		for($i=$anoActual-3;$i<=$anoActual;$i++){
			$dataOrder=null;
			for($j=0;$j<count($dataOrderActual);$j++){
				$valor=@$anoCategoria[$i][$lineas[$dataOrderActual[$j]]];//--ano,linea
				$valor=(empty($valor)?0:$valor);
				$dataOrder[]=$valor;
				if($i==$anoActual-3){
					$lineasOrden[]=$lineas[$dataOrderActual[$j]];
				}
			}
			$resultOrder[]=array("data" => $dataOrder,"name" => $i);
		}

		//var_dump($resultOrder);
		//die("epa!!");

		$r=array($resultOrder,$lineasOrden);

		echo json_encode($r);
	}

	public function insertionSort(array $array,$order) {
        $length=count($array);
        for ($i=1;$i<$length;$i++) {
            $element=$array[$i];
            $orderElement=$order[$i];
            $j=$i;
            while($j>0 && $array[$j-1]<$element) {
                //move value to right and key to previous smaller index
                $array[$j]=$array[$j-1];
                $order[$j]=$order[$j-1];
                $j=$j-1;
                }
            //put the element at index $j
            $array[$j]=$element;
            $order[$j]=$orderElement;
            }
        return $order;
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
		ini_set('max_execution_time', 0); 
    	ini_set('memory_limit','2048M');
		//echo "eeeee";
		$result=null;
		$arrayTopTen=@$this->mdl_cliente->topTepProductos($cliente);
		//echo "termino";
		//die($arrayTopTen);
		if(!empty($arrayTopTen)){
			$result=$this->mdl_cliente->getInfoTopTenProducts($arrayTopTen,$cliente);
		}
		echo json_encode($result);
	}

	public function getInfoCliente($cliente){
		$arrayInfoCliente=$this->mdl_cliente->getInfoCliente($cliente);
		$result=$arrayInfoCliente;
		echo json_encode($result);
	}

	public function getExhibidores($cliente){

		$productosExhibidores=@$this->mdl_cliente->getExhibidores($cliente);
		echo json_encode($productosExhibidores);
	}

	public function getNotasCredito($cliente){
		$motivos=$this->mdl_cliente->getMotivos();
		$anoActual=date("Y");
		$anoInicial=$anoActual-3;
		$result=null;

		$arrayNV=$this->mdl_cliente->getNotasCredito($cliente);
		foreach($motivos as $m=> $mot){
			$arrayNV[$m]['motivo']=$motivos[$m];
			for($i=$anoInicial;$i<=$anoActual;$i++){
				if(empty($arrayNV[$m][$i])){
					$arrayNV[$m][$i]=array(
						'motivo'=>$motivos[$m],
			            'totalValor'=>0,
			            'cantidadNV'=>0
			        );
				}
			}
		}

		arsort($arrayNV);
		$result=$arrayNV;
		echo json_encode($result);
	}


	public function test(){
		$cliente='86006928400'; //--Agrocampo
		//--80024210600 Sodimac 86006928400 Agrocampo
		$tiempoInicial=$this->microtime_float();
		
		//--1.Calculo de Lineas
		//$this->getLineas();

		//--2.Obtener Informacion del Cliente
		//$this->getInfoCliente($cliente);

		//--3.Obtener Exhibidores
		//$this->getExhibidores($cliente);

		//--3.ObtenerExhibidores **
		$this->getExhibidores($cliente);

		//--4. Obtener Facturacion por periodo mensual-anual
		//$this->getFacturacionPorPeriodo($cliente);

		//--5. Obtener Valores de Lineas Por Año **
		$this->getValoresLineasPorAno($cliente);

		//--6.Obtener Top ten de productos
		//$this->getTopTenProductos($cliente);

		$tiempoFinal=$this->microtime_float();
		echo "<br/>Tiempo empleado: " . ($tiempoFinal - $tiempoInicial);
	}

	public function test2(){
		$cliente='89090763823';
		$this->getNotasCredito($cliente);
	}
}
