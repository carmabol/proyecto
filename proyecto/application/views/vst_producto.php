<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Modulo de Producto</title>

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

	h1 {
		color: #444;
		background-color: transparent;
		border-bottom: 1px solid #D0D0D0;
		font-size: 19px;
		font-weight: normal;
		margin: 0 0 14px 0;
		padding: 14px 15px 10px 15px;
	}

	.negativo{
		color:#F00;
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
		<script src="https://code.jquery.com/jquery.js"></script> 
		<script src="https://code.highcharts.com/highcharts.js"></script>
		<script src="https://code.highcharts.com/modules/exporting.js"></script>
		<link href="<?= base_url("components/css/select2.css")?>" rel="stylesheet">
		<link href="<?= base_url("components/css/select2-bootstrap.css")?>" rel="stylesheet">
		<script src="<?= base_url("components/js/select2.min.js")?>"></script>
		<script src="<?= base_url("components/js/select2_locale_es.js")?>"></script>
		<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
		<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">

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

			console.log(formatNumber.new(123456779.18, "$"));


				var producto = null;
			    console.log( "ready!");
			    var conteoCanales=0;
			    
				$('#sel_product').select2({
			        placeholder: "Seleccione un producto",
			        allowClear: true,
			        minimumInputLength: 4,
			        ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
			          url: "http://localhost/proyecto/index.php/cnt_producto/getProduct/",
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

			    $('#sel_product').on('change', function(e){
			    	console.log("cambio");
			    	producto = ""+ ($('#sel_product').val().length ? $('#sel_product').val() : null);
			    	var fecha = new Date();
					var anoActual = fecha.getFullYear();
			        
			        var datosGrafica;
			    	if(producto=="null"){
			    		console.log("es null... borreme todo");
			        	$("#main4").html("No se encontraron resultados");
			        	$("#main1").html("No se encontraron resultados");
			        	$("#main").html("No se encontraron resultados");
			        	$("#main2").html("No se encontraron resultados");
			        	$("#main3").html("No se encontraron resultados");
			        	$("#mainTC").html("No se encontraron resultados");
			        	$("#mainT").html("No se encontraron resultados");
			        }
			        else{
			        	console.log("0.Producto:"+producto);
						$.ajax({
							  url: "http://localhost/proyecto/index.php/cnt_producto/getCountCanal/",
							  async:false,
				          	  success: function( msg ) {
							    var arr=JSON.parse(msg);
							    conteoCanales=arr;
							    console.log("1. Numero de Canales: "+conteoCanales);
							}});

						$.ajax({
							  url: "http://localhost/proyecto/index.php/cnt_producto/getInfoProducto/"+producto,
							  method: "POST",
							  async:false,
				          	  success: function(msg){
						    var arr=JSON.parse(msg);
						    console.log(arr);

						    if(""+msg=="null"){
						    	$("#mainT").html("NO HAY DATOS PARA MOSTRAR");
						    }
						    else{
						    	datos = $.map(arr, function(el) { return el });
							    console.log("comoasi que como fue");
							    
							    var tabla="";
							    tabla="<table class='table table-condensed'><thead><tr>";
							    tabla+="<th colspan='2'>INFORMACION PRODUCTO</th></tr><tr>";
								tabla+="<th>Cabecera</th>";
								tabla+="<th>Informacion</th>";
								tabla+="</tr>";
								tabla+="</thead>";
								tabla+="<tbody>";
								tabla+="<tr><td>Codigo</td><td>"+datos[0]['id_producto']+"</td></tr>";
								tabla+="<tr><td>Descripcion</td><td>"+datos[0]['nombre']+"</td></tr>";
								tabla+="<tr><td>Linea</td><td>"+datos[0]['linea']+"</td></tr>";
								tabla+="<tr><td>Sublinea</td><td>"+datos[0]['sublinea']+"</td></tr>";
								$('#mainT').html(tabla);
						    }
						}
				        });


			        	$.ajax({
						  url: "http://localhost/proyecto/index.php/cnt_producto/getCantidadZona/"+producto,
						  async:false,
						  beforeSend: function( xhr ) {
						    $("#main").html("<div style='min-width: 310px; margin: 0 auto'><img src='http://localhost/proyecto/components/images/loading.gif'></div>");
						  },
			          	  success: function( msg ) {
			          	  	var arr=JSON.parse(msg);
						    var infoProductoZona = $.map(arr, function(el) { return el });
						    console.log(infoProductoZona);
						    conteoCanales=infoProductoZona[2];
						    console.log("2.Recaptura Numero de Canales desde calculos"+conteoCanales);
			          	  	var tabla="<table class='table table-condensed'><thead><tr>";
							
							tabla+="<tr><th>CANAL</th>";
							tabla+="<th>ZONA</th>";

							for(i=3;i>=0;i--){
								tabla+="<th>Cantidad "+(anoActual-i)+"</th><th>Valor Neto "+(anoActual-i)+"</th><th>%</th>";
							}

							tabla+="</tr>";
							tabla+="</thead>";
							tabla+="<tbody>";

							var valoresCanal=[];
							for(i=0;i<conteoCanales;i++){
								valoresCanal[i]="";
							}

							for(i=0;i<infoProductoZona[1];i++){
								var canal="";
								canal+="<tr>";
								canal+="<td>"+infoProductoZona[0][i+1].nombreCanal+"</td>";
								canal+="<td>"+infoProductoZona[0][i+1].nombreZona+"</td>";

								for(j=3;j>=0;j--){
									var porcentaje=0;
									canal+="<td>"+infoProductoZona[0][i+1][(anoActual-j)]['diferenciaCantidad']+"</td>";
									canal+="<td>"+formatNumber.new(infoProductoZona[0][i+1][(anoActual-j)]['diferenciaNeto'], "$")+"</td>";
									if(j!=3){
										if(infoProductoZona[0][i+1][(anoActual-j)]['diferenciaNeto']==0 && infoProductoZona[0][i+1][(anoActual-j-1)]['diferenciaNeto']==0){
												a="-"
										}
										else{
											if(infoProductoZona[0][i+1][(anoActual-j-1)]['diferenciaNeto']==0){
												porcentaje=100;
												a="100%";
											}
											else{
												if(j==0){
													valorAnoAnterior=(infoProductoZona[0][i+1][(anoActual-j-1)]['diferenciaNeto']/12)*(fecha.getMonth()+1);
													porcentaje=parseFloat(((infoProductoZona[0][i+1][(anoActual-j)]['diferenciaNeto']-valorAnoAnterior)/valorAnoAnterior)*100).toFixed(2);
												}
												
												else{
													porcentaje=parseFloat(((infoProductoZona[0][i+1][(anoActual-j)]['diferenciaNeto']-infoProductoZona[0][i+1][(anoActual-j-1)]['diferenciaNeto'])/infoProductoZona[0][i+1][(anoActual-j-1)]['diferenciaNeto'])*100).toFixed(2);
												}

												a=porcentaje;
												a+="%";
											}
										}
										canal+="<td "+(porcentaje<0?"class='negativo'":"")+">"+a+"</td>";
									}else{
										canal+="<td>-</td>";
									}
								}
								canal+="</tr>";
								valoresCanal[(infoProductoZona[0][i+1].id_canal)-1]+=canal;
							}

							for(i=0;i<conteoCanales;i++){
								tabla+=valoresCanal[i];
							}

							tabla+="</tbody>";
							tabla+="</table>";

							$('#main').html(tabla);
			          	  }
			          	});	
//-------------------------------------el for por canales------------------------------------------------
			        	console.log("3. La variable conteoCanales numero de canales al dia "+conteoCanales);
			        	for(k=1;k<=conteoCanales;k++){
							var zonas;
				        	var datosCircular;
				        	var datosBarras;
				        	var arrayBarras;
				        	var nombreCanal;

			        		console.log("4. Hay más de 1 canal"+conteoCanales);
				        	$.ajax({
							  url: "http://localhost/proyecto/index.php/cnt_producto/getNombresZonasPorCanal/"+k,
							  async:false,
				          	  success: function( msg ) {
							    var arr=JSON.parse(msg);
							    zonas = $.map(arr, function(el) { return el });
							    console.log("5.Obtencion de las zonas del canal "+k+": ");
							    console.log(zonas);
							}});

							$.ajax({
							  url: "http://localhost/proyecto/index.php/cnt_producto/getNombreCanal/"+k,
							  async:false,
				          	  success: function( msg ) {
							    var arr=JSON.parse(msg);
							    nombreCanal = $.map(arr, function(el) { return el });
							    console.log("6. Nombre Canal"+nombreCanal);
							}});
				        	
			        	
				        	$.ajax({
							  url: "http://localhost/proyecto/index.php/cnt_producto/getNetoZonasPorAno",
							  method: "POST",
							  async:false,
							  beforeSend: function( xhr ) {
							    $("#main"+k).html("<div style='min-width: 310px; margin: 0 auto'><img src='http://localhost/proyecto/components/images/loading.gif'></div>");
							  },
							  data: { producto: producto, canal: k },
				          	  success: function(msg){
				          	  	//arrayBarras=msg;
				          	  	var arr=JSON.parse(msg);
							    
							    datosBarras = $.map(arr[0], function(el) { return el });
								datosCircular=$.map(arr[1], function(el) { return el });
							    //--console.log(t);
							    //--console.log(JSON.parse(t));
							    console.log("Datos Grafica de Barras: ");console.log(datosBarras);
							    console.log("Datos Grafica Circular: ");console.log(datosCircular);
								$("#main"+k).html("<div id='container"+k+"' style='min-width: 310px; margin: 0 auto'></div><BR><div id='containerC"+k+"' style='min-width: 310px; margin: 0 auto'></div>");

								    $('#container'+k).highcharts({
							        chart: {
							            type: 'column'
							        },
							        title: {
							            text: 'INFORMACIÓN POR ZONAS'
							        },
							        subtitle: {
							            text: nombreCanal
							        },
							        xAxis: {
							            categories: zonas,
							            crosshair: true
							        },
							        yAxis: {
							            min: 0,
							            title: {
							                text: 'Valor Neto ($)'
							            }
							        },
							        tooltip: {//--eso
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
							        series: datosBarras
							    });

								$('#containerC'+k).highcharts({
						            chart: {
						                plotBackgroundColor: null,
						                plotBorderWidth: null,
						                plotShadow: false,
						                type: 'pie'
						            },
						            title: {
						                text: 'CANAL '+nombreCanal
						            },
						            tooltip: {
						                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
						            },
						            plotOptions: {
						                pie: {
						                    allowPointSelect: true,
						                    cursor: 'pointer',
						                    dataLabels: {
						                        enabled: false
						                    },
						                    showInLegend: true
						                }
						            },
						            series: [{
						                name: 'Brands',
						                colorByPoint: true,
						                data: datosCircular
						            }]
						        });


				          	  }
				          	});
					}
			        
					$.ajax({
							  url: "http://localhost/proyecto/index.php/cnt_producto/getTopTenClientes/"+producto,
							  method: "POST",
							  async:false,
				          	  success: function(msg){
						    var arr=JSON.parse(msg);
						    console.log(arr);

						    if(""+msg=="null"){
						    	$("#mainTC").html("NO HAY DATOS PARA MOSTRAR");
						    }
						    else{
						    	datosTopTen = $.map(arr, function(el) { return el });
							    console.log("comoasi que como fue");
							    console.log(datosTopTen);
							    var fecha = new Date();
								var anoActual = fecha.getFullYear();
							    //--$("#main5").html("<div id='container5' style='min-width: 310px; margin: 0 auto'></div>");
							    var tabla="";
							    tabla="<table class='table table-condensed'><thead><tr>";
							    tabla+="<th colspan='10'>TOP 10 CLIENTES QUE MAS CONSUMEN</th></tr><tr>";
								tabla+="<th>NIT</th>";
								tabla+="<th>Nombre Cliente</th>";
								for(i=3;i>=0;i--){
									//console.log((anoActual-i));
									tabla+="<th>Cantidad (Año "+(anoActual-i)+")</th>";
									tabla+="<th>Valor Neto (Año "+(anoActual-i)+")</th>";
								}
								tabla+="</tr>";
								for(j=0;j<datosTopTen.length;j++){
									tabla+="<tr><td>"+datosTopTen[j]['info']["id_cliente"]+"</td>";
									tabla+="<td>"+datosTopTen[j]['info']["nombreCliente"]+"</td>";

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

								$('#mainTC').html(tabla);
						    }
						}
				        });

			        
//---------------------------------------------------------------------------------------------------------
			        }
				});//--fin onchange
	      });

	</script>

<div align="center">
<div class="row">
  <div class="col-md-1">&nbsp;</div>
  <div class="col-md-5">
    <div id="div_customer">
      <input type="hidden" id="sel_product" pattern=".+" required="" placeholder="Seleccione un producto"> </div>
  </div>
</div>

<div id="mainT" style="min-width: 310px; margin: 0 auto"></div>
<div id="mainTC" style="min-width: 310px; margin: 0 auto"></div>
<div id="main" style="min-width: 310px; margin: 0 auto"></div>
<div id="main1" style="min-width: 310px;  margin: 0 auto"></div>
<div id="main2" style="min-width: 310px;  margin: 0 auto"></div>
<div id="main3" style="min-width: 310px; margin: 0 auto"></div>
<div id="main4" style="min-width: 310px; margin: 0 auto"></div>

<br/>
</body>
</html>
