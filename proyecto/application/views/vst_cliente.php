<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Modulo de Cliente</title>

	<style type="text/css">

	::selection { background-color: #E13300; color: white; }
	::-moz-selection { background-color: #E13300; color: white; }

	body {
		background-color: #fff;
		margin: 40px;
		font: 13px/20px normal Helvetica, Arial, sans-serif;
		color: #4F5155;
	}

	a {
		color: #003399;
		background-color: transparent;
		font-weight: normal;
	}

	.negativo{
		color:#F00;
	}

	h1 {
		color: #444;
		background-color: transparent;
		border-bottom: 1px solid #D0D0D0;
		font-size: 19px;
		font-weight: normal;
		margin: 0 0 14px 0;
		padding: 14px 15px 10px 15px;
	}

	code {
		font-family: Consolas, Monaco, Courier New, Courier, monospace;
		font-size: 12px;
		background-color: #f9f9f9;
		border: 1px solid #D0D0D0;
		color: #002166;
		display: block;
		margin: 14px 0 14px 0;
		padding: 12px 10px 12px 10px;
	}

	#body {
		margin: 0 15px 0 15px;
	}

	p.footer {
		text-align: right;
		font-size: 11px;
		border-top: 1px solid #D0D0D0;
		line-height: 32px;
		padding: 0 10px 0 10px;
		margin: 20px 0 0 0;
	}

	#container {
		margin: 10px;
		border: 1px solid #D0D0D0;
		box-shadow: 0 0 8px #D0D0D0;
	}
	</style>

	

	  
</head>

<body>
		<!-- <script src="https://code.jquery.com/jquery.js"></script>  -->
		<script src="<?= base_url("components/js/jquery.js")?>"></script> 

		<!--<script src="https://code.highcharts.com/highcharts.js"></script>-->
		<script src="<?= base_url("components/js/highcharts.js")?>"></script>

		<!-- <script src="https://code.highcharts.com/modules/exporting.js"></script> -->
		<script src="<?= base_url("components/js/exporting.js")?>"></script>

		<link href="<?= base_url("components/css/select2.css")?>" rel="stylesheet">
		<link href="<?= base_url("components/css/select2-bootstrap.css")?>" rel="stylesheet">
		<script src="<?= base_url("components/js/select2.min.js")?>"></script>
		<script src="<?= base_url("components/js/select2_locale_es.js")?>"></script>

		<!-- <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css"> -->
		<link rel="stylesheet" href="<?= base_url("components/css336/bootstrap.min.css")?>">

		 <!--<script src="<?= base_url("components/js/select2/dist/js/select2.min.js")?>"></script>
		 <script src="<?= base_url("components/js/select2/dist/js/i18n/es.js")?>"></script>-->

	<script>
			$( document ).ready(function() {
				var formatNumber = {
				 separador: ".", // separador para los miles
				 sepDecimal: ',', // separador para los decimales
				 formatear:function (num){
				  num +='';
				  var splitStr = num.split('.');
				  var splitLeft = splitStr[0];
				  var splitRight = splitStr.length > 1 ? this.sepDecimal + splitStr[1] : '';
				  var regx = /(\d+)(\d{3})/;
				  while (regx.test(splitLeft)) {
				  splitLeft = splitLeft.replace(regx, '$1' + this.separador + '$2');
				  }
				  return this.simbol + splitLeft  +splitRight;
				 },
				 new:function(num, simbol){
				  this.simbol = simbol ||'';
				  return this.formatear(num);
				 }
				}

				var cliente = null;
			    console.log( "ready!" );
			    var lineas;
			    console.log( "1.Obtencion de Lineas" );
			    $.ajax({
						  url: "http://localhost/proyecto/index.php/cnt_cliente/getLineas",
			          	  success: function( msg ) {
						    var arr=JSON.parse(msg);
						    lineas = $.map(arr, function(el) { return el });
						    console.log( "1.Final" );
						}});

				$('#sel_customer').select2({
			        placeholder: "Seleccione cliente",
			        allowClear: true,
			        minimumInputLength: 4,
			        ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
			          url: "http://localhost/proyecto/index.php/cnt_cliente/getCustomer",
			          dataType: 'json',
			          type: 'POST',
			          data: function(term, page) {
			            return {
			              criteria: term,
			              page: page,
			            };
			          },
			          results: function(data, page) {
			            var more = (page * 20) < data.total; // whether or not there are more results available
			            return {
			              results: data.results,
			              more: more
			            };
			          },
			          escapeMarkup: function(m) {
			              return m;
			            } // we do not want to escape markup since we are displaying html in results
			        }
			    });

			    $('#sel_customer').on('change', function(e) {

			        cliente = ""+ ($('#sel_customer').val().length ? $('#sel_customer').val() : null);
			        console.log(cliente);
			        var datosGrafica;

			        if(cliente=="null"){
			        	$("#main4").html("No se encontraron resultados");
			        	$("#main6").html("No se encontraron resultados");
			        	$("#main5").html("No se encontraron resultados");
			        	$("#main").html("No se encontraron resultados");
			        	$("#main2").html("No se encontraron resultados");
			        	$("#main3").html("No se encontraron resultados");
			        }
			        else{
			        	console.log("2.Obtener Informacion de Cliente");
			        	$.ajax({
						  url: "http://localhost/proyecto/index.php/cnt_cliente/getInfoCliente/"+cliente,
			          	  success: function( msg ) {
			          	  	var arr=JSON.parse(msg);
						    infoCliente = $.map(arr, function(el) { return el });
						    console.log(infoCliente);

			          	  	var tabla="<table class='table table-condensed'><thead><tr>";
							tabla+="<th>Cabecera</th>";
							tabla+="<th>Info</th>";
							tabla+="</tr>";
							tabla+="</thead>";
							tabla+="<tbody>";
							tabla+="<tr><td>ID</td><td>"+infoCliente[0].id+"</td></tr>";
							tabla+="<tr><td>NIT</td><td>"+infoCliente[0].nit+"</td></tr>";
							tabla+="<tr><td>SUCURSAL</td><td>"+infoCliente[0].sucursal+"</td></tr>";
							tabla+="<tr><td>RAZON SOCIAL</td><td>"+infoCliente[0].nombre+"</td></tr>";

							tabla+="<tr><td>VENDEDOR</td><td>"+infoCliente[0].vendedor+"</td></tr>";
							tabla+="<tr><td>ZONA</td><td>"+infoCliente[0].zona+"</td></tr>";
							tabla+="<tr><td>CANAL</td><td>"+infoCliente[0].canal+"</td></tr>";

							tabla+="<tr><td>ESTABLECIMIENTO</td><td>"+infoCliente[0].establecimiento+"</td></tr>";
							tabla+="<tr><td>BARRIO</td><td>"+infoCliente[0].barrio+"</td></tr>";
							tabla+="<tr><td>CONTACTO</td><td>"+infoCliente[0].contacto+"</td></tr>";
							tabla+="<tr><td>FECHA CREACION</td><td>"+infoCliente[0].fechaCreacion+"</td></tr>";
							tabla+="<tr><td>CIUDAD</td><td>"+infoCliente[0].ciudad_Tercero+"</td></tr>";
							tabla+="<tr><td>DEPARTAMENTO</td><td>"+infoCliente[0].departamento+"</td></tr>";
							
							tabla+="<tr><td>DIRECCION</td><td>"+infoCliente[0].direccion+"</td></tr>";
							tabla+="<tr><td>TELEFONO 1</td><td>"+infoCliente[0].telefono1+"</td></tr>";
							tabla+="<tr><td>TELEFONO 2</td><td>"+infoCliente[0].telefono2+"</td></tr>";
							tabla+="<tr><td>OBSERVACIONES</td><td>"+infoCliente[0].observaciones+"</td></tr>";
							
							tabla+="</tbody>";
							tabla+="</table>";

							tablaCartera="<table class='table table-condensed'><thead><tr>";
							tablaCartera+="<th>Cabecera</th>";
							tablaCartera+="<th>Info</th>";
							tablaCartera+="</tr>";
							tablaCartera+="</thead>";
							tablaCartera+="<tbody>";
							tablaCartera+="<tr><td>**DIAS PROMEDIO PAGO</td><td>"+"**DPP"+"</td></tr>";
							tablaCartera+="<tr><td>**CUPO CREDITO</td><td>"+formatNumber.new(infoCliente[0].cupo_credito, "$")+"</td></tr>";
							tablaCartera+="<tr><td>**DIAS GRACIA</td><td>"+infoCliente[0].dias_gracia+"</td></tr>";
							tablaCartera+="<tr><td>**FORMA PAGO</td><td>"+infoCliente[0].forma_pago+"</td></tr>";
							tablaCartera+="<tr><td>**CONDICION PAGO</td><td>"+infoCliente[0].cond_pago+"</td></tr>";
							tablaCartera+="<tr><td>**FACTURAS CORRIENTES</td><td>"+"**FC"+"</td></tr>";
							tablaCartera+="<tr><td>**FACTURAS VENCIDAS</td><td>"+"**FV"+"</td></tr>";
							tablaCartera+="</tbody>";
							tablaCartera+="</table>";

							$('#main4').html(tabla+"<BR>"+tablaCartera);
							console.log("2.Final");
			          	  }
			          	});	
			        	
			        	console.log("3.Obtener los exhibidores");
			        	$.ajax({
						  url: "http://localhost/proyecto/index.php/cnt_cliente/getExhibidores/"+cliente,
			          	  success: function( msg ) {
			          	  	var arr;
			          	  	arr=JSON.parse(msg);
			          	  	console.log(arr);
			          	  	if(arr!=null){
		          	  			//console.log("dasdsadsdsasdasdsad");
							    exhibidores = $.map(arr, function(el) { return el });
							    console.log(exhibidores);
							    var tabla="<table class='table table-condensed'><thead>";
							    tabla+="<tr><th colspan=4>PRODUCTOS EXHIBIDORES</th></tr>"
								tabla+="<tr><th>ID</th><th>NOMBRE PRODUCTO</th><th>CANTIDAD</th><th>COSTO TOTAL</th>";
								tabla+="</tr>";
								tabla+="</thead>";
								tabla+="<tbody>";
	
								//COMPLETAR LA TABLA DE EXHIBIDORES Y PROBAR CON DATOS
								for(i=0;i<exhibidores.length;i++){
									exhibidores[i].name
									tabla+="<tr>";
									tabla+="<td>"+exhibidores[i].id_producto+"</td>";
									tabla+="<td>"+exhibidores[i].nombreProducto+"</td>";
									tabla+="<td>"+exhibidores[i].diferenciaCantidad+"</td>";
									tabla+="<td>"+formatNumber.new(exhibidores[i].diferenciaNeto, "$")+"</td>";
									tabla+="</tr>";
								}
	
								tabla+="</tbody>";
								tabla+="</table>";

								$('#main5').html(tabla);
			          	  	}else{
			          	  		$('#main5').html("No han obtenido EXHIBIDORES este año");
			          	  	}
			          	  	console.log("3.Final");
			          	  }
			          	});	

			        $.ajax({
						  url: "http://localhost/proyecto/index.php/cnt_cliente/getNotasCredito/"+cliente,
			          	  success: function( msg ) {
			          	  	var arr;
			          	  	arr=JSON.parse(msg);
			          	  	console.log(arr);
			          	  	var fecha = new Date();
							var anoActual = fecha.getFullYear();
			          	  	if(arr!=null){
		          	  			//console.log("dasdsadsdsasdasdsad");
							    notasCredito = $.map(arr, function(el) { return el });
							    console.log("notas Credito niggaaaaaaaa!!");
							    console.log(notasCredito);
							    console.log(notasCredito.length);
							    var tabla="<table class='table table-condensed'><thead>";
							    tabla+="<tr><th colspan=4>NOTAS CREDITO</th></tr>"
								tabla+="<tr><th>Motivo</th>";
								for(j=anoActual-3;j<=anoActual;j++)
									tabla+="<th>Cantidad "+j+"</th>"+"<th>Valor "+j+"</th>";
								tabla+="</tr>";
								tabla+="</thead>";
								tabla+="<tbody>";

							    for(i=0;i<notasCredito.length;i++){//--Motivos
							    	tabla+="<tr><td>"+notasCredito[i]["motivo"]+"</td>";
							    	for(j=anoActual-3;j<=anoActual;j++){
							    		tabla+="<td>"+(notasCredito[i][j]["cantidadNV"]>0?notasCredito[i][j]["cantidadNV"]:"-")+"</td>";
							    		tabla+="<td>"+(notasCredito[i][j]["totalValor"]>0?formatNumber.new(notasCredito[i][j]["totalValor"], "$"):"-")+"</td>";
							    	}
							    	tabla+="</tr>";
							    }
							    tabla+="</tbody>";
							    tabla+="</table>";
							    $('#main6').html(tabla);


							    /*
							    var tabla="<table class='table table-condensed'><thead>";
							    tabla+="<tr><th colspan=4>PRODUCTOS EXHIBIDORES</th></tr>"
								tabla+="<tr><th>ID</th><th>NOMBRE PRODUCTO</th><th>CANTIDAD</th><th>COSTO TOTAL</th>";
								tabla+="</tr>";
								tabla+="</thead>";
								tabla+="<tbody>";
	
								//COMPLETAR LA TABLA DE EXHIBIDORES Y PROBAR CON DATOS
								for(i=0;i<exhibidores.length;i++){
									exhibidores[i].name
									tabla+="<tr>";
									tabla+="<td>"+exhibidores[i].id_producto+"</td>";
									tabla+="<td>"+exhibidores[i].nombreProducto+"</td>";
									tabla+="<td>"+exhibidores[i].diferenciaCantidad+"</td>";
									tabla+="<td>"+formatNumber.new(exhibidores[i].diferenciaNeto, "$")+"</td>";
									tabla+="</tr>";
								}
	
								tabla+="</tbody>";
								tabla+="</table>";

								$('#main5').html(tabla);*/
			          	  	}else{
			          	  		$('#main6').html("No hay datos de notas credito");
			          	  	}
			          	  	console.log("3.Final");
			          	  }
			          	});	

			        //--inicio ajax getFacturacion
			        	console.log("4. Obtener Facturacion por periodo mensual-anual");
			        	$.ajax({
						  url: "http://localhost/proyecto/index.php/cnt_cliente/getFacturacionPorPeriodo/"+cliente,
			          	  success: function( msg ) {
						    var arr=JSON.parse(msg);
						    if(arr==null){
						    	 $("#main").html("NO HAY INFORMACION DE FACTURACION")
						    }
						    else{
						    	datosGrafica = $.map(arr, function(el) { return el });
							    console.log(datosGrafica);
							    console.log(datosGrafica.length);

							    $("#main").html("<div id='container' style='min-width: 310px; margin: 0 auto'></div>");
							    

							    $('#container').highcharts({
							        title: {
							            text: 'Informacion del Cliente',
							            x: -20 //center
							        },
							        subtitle: {
							            text: 'Facturación Mensual Periodo de 3 años',
							            x: -20
							        },
							        xAxis: {
							            categories: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun',
							                'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic']
							        },
							        yAxis: {
							            title: {
							                text: 'Pesos ($)'
							            },
							            plotLines: [{
							                value: 0,
							                width: 1,
							                color: '#808080'
							            }]
							        },
							        tooltip: {
							           headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
								            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
								                '<td style="padding:0"><b>{point.y:,.0f} $</b></td></tr>',
								            footerFormat: '</table>',
								            shared: true,
								            useHTML: true
							        },
							        legend: {
							            layout: 'vertical',
							            align: 'right',
							            verticalAlign: 'middle',
							            borderWidth: 0
							        },
							        series: datosGrafica
							    });

								var tabla="<table class='table table-condensed'><thead><tr>";
								tabla+="<th>&nbsp;</th>";
								tabla+="<th>Enero</th>";
								tabla+="<th>Febrero</th>";
								tabla+="<th>Marzo</th>";
								tabla+="<th>Abril</th>";
								tabla+="<th>Mayo</th>";
								tabla+="<th>Junio</th>";
								tabla+="<th>Julio</th>";
								tabla+="<th>Agosto</th>";
								tabla+="<th>Septiembre</th>";
								tabla+="<th>Octubre</th>";
								tabla+="<th>Noviembre</th>";
								tabla+="<th>Diciembre</th>";
								tabla+="</tr>";
								tabla+="</thead>";
								tabla+="<tbody>";

								var totalPorAno=[];

								for(i=0;i<datosGrafica.length;i++){
									datosGrafica[i].name
									tabla+="<tr>";
									tabla+="<td>"+datosGrafica[i].name+"</td>";
									var consolidado=0;
									for(j=0;j<datosGrafica[i].data.length;j++){
										tabla+="<td>"+formatNumber.new(datosGrafica[i].data[j], "$")+"</td>";
										consolidado+=datosGrafica[i].data[j];
									}
									totalPorAno[datosGrafica[i].name]=consolidado;

									tabla+="</tr>";
									
								}
								tabla+="</tbody>";
								tabla+="</table>";

								$('#container').append(tabla);

								
								//console.log(totalPorAno);

								var tabla2="<br /><table class='table table-condensed'><thead>";
								tabla2+="<tr><th colspan=3>Comportamiento Venta</th>";
								tabla2+="<tr><th>Año</th>";
								tabla2+="<th>Total Venta</th>";
								tabla2+="<th>Crecimiento</th>";
								tabla2+="</tr>";
								tabla2+="</thead>";
								tabla2+="<tbody>";
								var fecha = new Date();
								var anoInicial = fecha.getFullYear()-3;
								for(i=0;i<=3;i++){
									tabla2+="<td>"+(anoInicial+i)+"</td>";

									if(totalPorAno[(anoInicial+i)]!=null)
										tabla2+="<td>"+formatNumber.new(totalPorAno[(anoInicial+i)], "$")+"</td>";
									else
										tabla2+="<td>-</td>";

									if(anoInicial+i==anoInicial){
										tabla2+="<td>-</td></tr>";
									}
									else{
										if(isNaN((((totalPorAno[(anoInicial+i)]-totalPorAno[(anoInicial+i)-1])/totalPorAno[(anoInicial+i)-1])*100)))
											tabla2+="<td>-</td></tr>";
											else{
												if(i!=3){
													porcentaje=parseFloat( (((totalPorAno[(anoInicial+i)]-totalPorAno[(anoInicial+i)-1])/totalPorAno[(anoInicial+i)-1])*100)).toFixed(1);
													tabla2+="<td "+(porcentaje<0?"class='negativo'":"")+">"+porcentaje+"%</td></tr>";
												}else{
													$mesesTranscurridos=fecha.getMonth()+1;
													$totalAnoAnterior=(totalPorAno[(anoInicial+i)-1]/12)*$mesesTranscurridos;

													porcentaje=parseFloat( (((totalPorAno[(anoInicial+i)]-$totalAnoAnterior)/$totalAnoAnterior)*100)).toFixed(1);

													tabla2+="<td "+(porcentaje<0?"class='negativo'":"")+">"+porcentaje+"%</td></tr>";
												}

												

											}
										

									}
								}
								tabla2+="</tbody>";
								tabla2+="</table>";
								$('#container').append(tabla2);
							}
							console.log("4.Final");
						}
					});//fin ajax getFacturacion
					
//--final			
						var datosGrafica2;
						console.log("5. Obtener Valores de Lineas Por Año");
						$.ajax({
						  url: "http://localhost/proyecto/index.php/cnt_cliente/getValoresLineasPorAno2/"+cliente,
						  beforeSend: function( xhr ) {
						    $("#main2").html("<div style='min-width: 310px; margin: 0 auto'><img src='http://localhost/proyecto/components/images/loading.gif'></div>");
						  },
			          	  success: function( msg ) {
						    var arr=JSON.parse(msg);

						    datosGrafica2 = $.map(arr[0], function(el) { return el });
						    lineasO=$.map(arr[1], function(el) { return el });
						    //$.map(arr[1], function(el) { return el });


						    console.log("......................datosGrafica2");
						    console.log(datosGrafica2);
						    console.log(datosGrafica2.length);


						    $("#main2").html("<div id='container2' style='min-width: 310px; margin: 0 auto'></div>");

						    $('#container2').highcharts({
					        chart: {
					            type: 'column'
					        },
					        title: {
					            text: 'INFORMACIÓN POR LINEAS'
					        },
					        subtitle: {
					            text: 'FERCON'
					        },
					        xAxis: {
					            categories: lineasO,
					            crosshair: true
					        },
					        yAxis: {
					            min: 0,
					            title: {
					                text: 'Valor Neto ($)'
					            }
					        },
					        tooltip: {
					            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
					            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
					                '<td style="padding:0"><b>{point.y:,.0f} $</b></td></tr>',
					            footerFormat: '</table>',
					            shared: true,
					            useHTML: true
					        },
					        plotOptions: {
					            column: {
					                pointPadding: 0.2,
					                borderWidth: 0
					            }
					        },
					        series: datosGrafica2
					    });
						var fecha = new Date();
						var anoActual = fecha.getFullYear();
						var tablaLineas="<table class='table table-condensed'><thead><tr>";
							tablaLineas+="<th>Linea</th>";
							tablaLineas+="<th>"+(anoActual-3)+"</th>";
							tablaLineas+="<th>"+(anoActual-2)+"</th><th>%</th>";
							tablaLineas+="<th>"+(anoActual-1)+"</th><th>%</th>";
							tablaLineas+="<th>"+(anoActual)+"</th><th>%</th>";
							tablaLineas+="<th>Sumatoria</th><th>Participacion</th>";
							tablaLineas+="</tr>";
							tablaLineas+="</thead>";
							tablaLineas+="<tbody>";

						var total=0;
							var sumatoria=0;
							for(i=0;i<lineasO.length;i++){
								console.log(lineasO[i]);
								tablaLineas+="<tr>";
								tablaLineas+="<td>"+lineasO[i]+"</td>";
								sumatoria=0;
								
								for(j=0;j<=3;j++){
									tablaLineas+="<td>"+formatNumber.new(datosGrafica2[j].data[i], "$")+"</td>";
									sumatoria+=datosGrafica2[j].data[i];
									total+=datosGrafica2[j].data[i];
									if(j!=0){
										//--parseFloat( (((totalPorAno[i]-totalPorAno[i-1])/totalPorAno[i-1])*100)).toFixed(1)+"%
										if(datosGrafica2[j-1].data[i]==0){
											if(datosGrafica2[j].data[i]==0)
												tablaLineas+="<td>-</td>";
											else
												tablaLineas+="<td>100%</td>";
										}else{
											if(datosGrafica2[j].data[i]==0)
												tablaLineas+="<td class='negativo'>-100%</font></td>";
											else{
												if(j==3){
													datosAnoAnterior=(datosGrafica2[j-1].data[i]/12)*(fecha.getMonth()+1);
													porcentaje=parseFloat(((datosGrafica2[j].data[i]-datosAnoAnterior)/datosAnoAnterior)*100).toFixed(1);
													tablaLineas+="<td "+(porcentaje<0?"class='negativo'":"")+">"+porcentaje+"%</td>";
												}else{
													porcentaje=parseFloat(((datosGrafica2[j].data[i]-datosGrafica2[j-1].data[i])/datosGrafica2[j-1].data[i])*100).toFixed(1);
													tablaLineas+="<td "+(porcentaje<0?"class='negativo'":"")+">"+porcentaje+"%</td>";
												}
											}
										}
										
									}

								}
								tablaLineas+="<td id='p"+i+"'>"+/*formatNumber.new(sumatoria, "$")*/sumatoria+"</td><td class='part' id='"+i+"'>%%%%</td>";
								tablaLineas+="</td>";
							}
							tablaLineas+="</tbody>";
							tablaLineas+="</table>";

							$("#main2").append(tablaLineas);
							console.log("LOS PARTICIPACIONES");
							$( ".part" ).each(function( i ) {
								console.log($(this).attr("id")+"=>"+$("#p"+$(this).attr("id")).text());
								$(this).text( (($("#p"+$(this).attr("id")).text()/total)*100).toFixed(1)+"%");
								$("#p"+$(this).attr("id")).text(formatNumber.new($("#p"+$(this).attr("id")).text(), "$"));
							  });
							console.log("5.Final");
						}});

						console.log("6.Obtener Top ten de productos");
						$.ajax({
						  url: "http://localhost/proyecto/index.php/cnt_cliente/getTopTenProductos/"+cliente,
			          	  success: function( msg ) {
						    var arr=JSON.parse(msg);
						    console.log(arr);

						    if(""+msg=="null"){
						    	$("#main3").html("NO HAY DATOS PARA MOSTRAR");
						    }
						    else{
						    	datosTopTen = $.map(arr, function(el) { return el });
							    console.log("comoasi que como fue");
							    console.log(datosTopTen);
							    var fecha = new Date();
								var anoActual = fecha.getFullYear();
							    $("#main3").html("<div id='container3' style='min-width: 310px; margin: 0 auto'></div>");
							    var tabla="";
							    tabla="<table class='table table-condensed'><thead><tr>";
							    tabla+="<th colspan='11'>TOP 10 PRODUCTOS MAS COMPRADOS</th></tr><tr>";
								tabla+="<th>Referencia Item</th>";
								tabla+="<th>Nombre Item</th>";
								tabla+="<th>Nombre Linea</th>";
								for(i=3;i>=0;i--){
									//console.log((anoActual-i));
									tabla+="<th>Cantidad (Año "+(anoActual-i)+")</th>";
									tabla+="<th>Valor Neto (Año "+(anoActual-i)+")</th>";
								}
								tabla+="</tr>";
								for(j=0;j<datosTopTen.length;j++){
									tabla+="<tr><td>"+datosTopTen[j]['info']["id_producto"]+"</td>";
									tabla+="<td>"+datosTopTen[j]['info']["nombreProducto"]+"</td>";
									tabla+="<td>"+datosTopTen[j]['info']["linea"]+"</td>";

									for(i=3;i>=0;i--){

										if(datosTopTen[j][anoActual-i]==null){
											tabla+="<td>-</td>";
											tabla+="<td>-</td>";
										}
										else{
											tabla+="<td>"+datosTopTen[j][anoActual-i]['cantidad']+"</td>";
											tabla+="<td>"+formatNumber.new(datosTopTen[j][anoActual-i]['totalValor'], "$")+"</td>";
										}

									}
									tabla+="</tr>";
								}
								tabla+="</tr>";
								tabla+="</thead>";
								tabla+="<tbody>";

								$('#container3').append(tabla);
						    }
						console.log("6.Final");
						}
					});//fin ajax getFacturacion
				}
			});//--fin onchange
	      });

	</script>

<div align="center">
<div class="row">
  <div class="col-md-1">&nbsp;</div>
  <div class="col-md-5">
    <div id="div_customer">
      <input type="hidden" id="sel_customer" pattern=".+" required="" placeholder="Seleccione un Cliente"> </div>
  </div>
</div>
<div id="main4" style="min-width: 310px;  margin: 0 auto"></div>
<div id="main" style="min-width: 310px; margin: 0 auto"></div>
<div id="main6" style="min-width: 310px;  margin: 0 auto"></div>
<div id="main5" style="min-width: 310px;  margin: 0 auto"></div>
<div id="main2" style="min-width: 310px; margin: 0 auto"></div>
<div id="main3" style="min-width: 310px; margin: 0 auto"></div>

<br/>
</body>
</html>