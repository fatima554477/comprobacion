<?php

    if(!isset($_SESSION)) 
    { 
        session_start(); 
    }  
//select.php  CONTRASENA_DE1
$identioficador = isset($_POST["personal_id"])?$_POST["personal_id"]:'';
if($identioficador != '')
{
 $output = '';
	require "controladorPP.php";

$pagoproveedores = NEW accesoclase();
$queryVISTAPREV = $pagoproveedores->Listado_pagoproveedor2($identioficador);

?>
<div id="actualizatabla">
<?php
   while($row = mysqli_fetch_array($queryVISTAPREV))
    {
$SOLICITADO = "";$APROBADO = "";$PAGADO = "";$PAGADO = "";
    if($row['STATUS_DE_PAGO']=="SOLICITADO"){$SOLICITADO = "selected";}
elseif($row['STATUS_DE_PAGO']=="APROBADO"){$APROBADO = "selected";}
elseif($row['STATUS_DE_PAGO']=="PAGADO"){$PAGADO = "selected";}
elseif($row['STATUS_DE_PAGO']=="RECHAZADO"){$RECHAZADO = "selected";}

$STATUS_DE_PAGO = '<select required="" name="STATUS_DE_PAGO"> 

<option style="background:#d9f9fa" value="SOLICITADO" '.$SOLICITADO.'>SOLICITADO</option>
<option style="background:#e1f5de" value="APROBADO" '.$APROBADO.'>APROBADO</option>
<option style="background:#f5deee" value="PAGADO" '.$PAGADO.'>PAGADO</option>
<option style="background:#f5f4de" value="RECHAZADO" '.$RECHAZADO.'>RECHAZADO</option>
</select>';
		

	$queryVISTAPREV = $pagoproveedores->Listado_subefacturaDOCTOS($row['id']);		
	while($rowDOCTOS = mysqli_fetch_array($queryVISTAPREV))
	{

		
		
	//}


        if($rowDOCTOS["ADJUNTAR_FACTURA_PDF"]!=""){$ADJUNTAR_FACTURA_PDF .=  "<a target='_blank' href='includes/archivos/".$rowDOCTOS["ADJUNTAR_FACTURA_PDF"]."'>Visualizar!</a>"." <span id='".$rowDOCTOS['id']."' class='view_dataSBborrar2' style='cursor:pointer;color:blue;'>Borrar!</span> <span > ".$rowDOCTOS['fechaingreso']."</span>".'<br/>'; 
		}	else{
			
			//$ADJUNTAR_FACTURA_PDF = "";
			
		}
        
        
        if($rowDOCTOS["ADJUNTAR_FACTURA_XML"]!=""){$ADJUNTAR_FACTURA_XML .=  "<a target='_blank' href='includes/archivos/".$rowDOCTOS["ADJUNTAR_FACTURA_XML"]."'>Visualizar!</a>"." <span id='".$rowDOCTOS['id']."' class='view_dataSBborrar2' style='cursor:pointer;color:blue;'>Borrar!</span> <span > ".$rowDOCTOS['fechaingreso']."</span>".'<br/>'; 
		}	else{
			
			//$ADJUNTAR_FACTURA_XML = "";
			
		}

         
        if($rowDOCTOS["ADJUNTAR_COTIZACION"]!=""){$ADJUNTAR_COTIZACION .=  "<a target='_blank' href='includes/archivos/".$rowDOCTOS["ADJUNTAR_COTIZACION"]."'>Visualizar!</a>"." <span id='".$rowDOCTOS['id']."' class='view_dataSBborrar2' style='cursor:pointer;color:blue;'>Borrar!</span> <span > ".$rowDOCTOS['fechaingreso']."</span>".'<br/>'; 
		}	else{
			
			//$ADJUNTAR_COTIZACION = "";
			
		}
        
        if($rowDOCTOS["CONPROBANTE_TRANSFERENCIA"]!=""){$CONPROBANTE_TRANSFERENCIA =  "<a target='_blank' href='includes/archivos/".$rowDOCTOS["CONPROBANTE_TRANSFERENCIA"]."'>Visualizar!</a>"." <span id='".$rowDOCTOS['id']."' class='view_dataSBborrar2' style='cursor:pointer;color:blue;'>Borrar!</span> <span > ".$rowDOCTOS['fechaingreso']."</span>".'<br/>'; 
		}	else{
			
			//$CONPROBANTE_TRANSFERENCIA = "";
			
		}
        
        if($rowDOCTOS["FOTO_ESTADO_PROVEE"]!=""){$FOTO_ESTADO_PROVEE .=  "<a target='_blank' href='includes/archivos/".$rowDOCTOS["FOTO_ESTADO_PROVEE"]."'>Visualizar!</a>"." <span id='".$rowDOCTOS['id']."' class='view_dataSBborrar2' style='cursor:pointer;color:blue;'>Borrar!</span> <span > ".$rowDOCTOS['fechaingreso']."</span>".'<br/>'; 
		}	else{
			
			//$FOTO_ESTADO_PROVEE = "";
			
		}
        
        if($rowDOCTOS["COMPLEMENTOS_PAGO_PDF"]!=""){$COMPLEMENTOS_PAGO_PDF .=  "<a target='_blank' href='includes/archivos/".$rowDOCTOS["COMPLEMENTOS_PAGO_PDF"]."'>Visualizar!</a>"." <span id='".$rowDOCTOS['id']."' class='view_dataSBborrar2' style='cursor:pointer;color:blue;'>Borrar!</span> <span > ".$rowDOCTOS['fechaingreso']."</span>".'<br/>'; 
		}	else{
			
			//$COMPLEMENTOS_PAGO_PDF = "";
			
		}
        

        if($rowDOCTOS["COMPLEMENTOS_PAGO_XML"]!=""){$COMPLEMENTOS_PAGO_XML .=  "<a target='_blank' href='includes/archivos/".$rowDOCTOS["COMPLEMENTOS_PAGO_XML"]."'>Visualizar!</a>"." <span id='".$rowDOCTOS['id']."' class='view_dataSBborrar2' style='cursor:pointer;color:blue;'>Borrar!</span> <span > ".$rowDOCTOS['fechaingreso']."</span>".'<br/>'; 
		}	else{
			
			//$COMPLEMENTOS_PAGO_XML = "";
			
		}

        if($rowDOCTOS["CANCELACIONES_PDF"]!=""){$CANCELACIONES_PDF .=  "<a target='_blank' href='includes/archivos/".$rowDOCTOS["CANCELACIONES_PDF"]."'>Visualizar!</a>"." <span id='".$rowDOCTOS['id']."' class='view_dataSBborrar2' style='cursor:pointer;color:blue;'>Borrar!</span> <span > ".$rowDOCTOS['fechaingreso']."</span>".'<br/>'; 
		}	else{
			
			//$CANCELACIONES_PDF = "";
			
		}

        if($rowDOCTOS["CANCELACIONES_XML"]!=""){$CANCELACIONES_XML .=  "<a target='_blank' href='includes/archivos/".$rowDOCTOS["CANCELACIONES_XML"]."'>Visualizar!</a>"." <span id='".$rowDOCTOS['id']."' class='view_dataSBborrar2' style='cursor:pointer;color:blue;'>Borrar!</span> <span > ".$rowDOCTOS['fechaingreso']."</span>".'<br/>'; 
		}	else{
			
			//$CANCELACIONES_XML = "";
			
		}

        
        if($rowDOCTOS["ADJUNTAR_FACTURA_DE_COMISION_PDF"]!=""){$ADJUNTAR_FACTURA_DE_COMISION_PDF .=  "<a target='_blank' href='includes/archivos/".$rowDOCTOS["ADJUNTAR_FACTURA_DE_COMISION_PDF"]."'>Visualizar!</a>"." <span id='".$rowDOCTOS['id']."' class='view_dataSBborrar2' style='cursor:pointer;color:blue;'>Borrar!</span> <span > ".$rowDOCTOS['fechaingreso']."</span>".'<br/>'; 
		}	else{
			
			//$ADJUNTAR_FACTURA_DE_COMISION_PDF = "";
			
		}

        if($rowDOCTOS["ADJUNTAR_ARCHIVO_1"]!=""){$ADJUNTAR_ARCHIVO_1 .=  "<a target='_blank' href='includes/archivos/".$rowDOCTOS["ADJUNTAR_ARCHIVO_1"]."'>Visualizar!</a>"." <span id='".$rowDOCTOS['id']."' class='view_dataSBborrar2' style='cursor:pointer;color:blue;'>Borrar!</span> <span > ".$rowDOCTOS['fechaingreso']."</span>".'<br/>'; 
		}	else{
			
			//$ADJUNTAR_ARCHIVO_1 = "";
			
		}
        
        if($rowDOCTOS["CALCULO_DE_COMISION"]!=""){$CALCULO_DE_COMISION .=  "<a target='_blank' href='includes/archivos/".$rowDOCTOS["CALCULO_DE_COMISION"]."'>Visualizar!</a>"." <span id='".$rowDOCTOS['id']."' class='view_dataSBborrar2' style='cursor:pointer;color:blue;'>Borrar!</span> <span > ".$rowDOCTOS['fechaingreso']."</span>".'<br/>'; 
		}	else{
			
			//$CALCULO_DE_COMISION = "";
			
		}

        if($rowDOCTOS["COMPROBANTE_DE_DEVOLUCION"]!=""){$COMPROBANTE_DE_DEVOLUCION .=  "<a target='_blank' href='includes/archivos/".$rowDOCTOS["COMPROBANTE_DE_DEVOLUCION"]."'>Visualizar!</a>"." <span id='".$rowDOCTOS['id']."' class='view_dataSBborrar2' style='cursor:pointer;color:blue;'>Borrar!</span> <span > ".$rowDOCTOS['fechaingreso']."</span>".'<br/>'; 
		}	else{
			
			//$COMPROBANTE_DE_DEVOLUCION = "";
			
		}

        if($rowDOCTOS["NOTA_DE_CREDITO_COMPRA"]!=""){$NOTA_DE_CREDITO_COMPRA .=  "<a target='_blank' href='includes/archivos/".$rowDOCTOS["NOTA_DE_CREDITO_COMPRA"]."'>Visualizar!</a>"." <span id='".$rowDOCTOS['id']."' class='view_dataSBborrar2' style='cursor:pointer;color:blue;'>Borrar!</span> <span > ".$rowDOCTOS['fechaingreso']."</span>".'<br/>'; 
		}	else{
			
			//$NOTA_DE_CREDITO_COMPRA = "";
			
		}

      
        if($rowDOCTOS["ADJUNTAR_FACTURA_DE_COMISION_XML"]!=""){$ADJUNTAR_FACTURA_DE_COMISION_XML .=  "<a target='_blank' href='includes/archivos/".$rowDOCTOS["ADJUNTAR_FACTURA_DE_COMISION_XML"]."'>Visualizar!</a>"." <span id='".$rowDOCTOS['id']."' class='view_dataSBborrar2' style='cursor:pointer;color:blue;'>Borrar!</span> <span > ".$rowDOCTOS['fechaingreso']."</span>".'<br/>'; 
		}	else{
			
			//$ADJUNTAR_FACTURA_DE_COMISION_XML = "";
			
		}

}


?>
</div>
<?php


 $output .= '<div id="respuestaser"></div><form  id="ListadoPAGOPROVEEform"> 
      <div class="table-responsive">  
           <table class="table table-bordered">';
 
     $output .= '





 <tr>
 
<td width="30%"><label>ADJUNTAR FACTURA (FORMATO PDF)</label></td>
<td width="70%">	<div id="drop_file_zone" ondrop="upload_file2(event,\'ADJUNTAR_FACTURA_PDF\')" ondragover="return false" style="width:300px;">
<p>Suelta aquí o busca tu archivo</p>
<p><input class="form-control form-control-sm" id="ADJUNTAR_FACTURA_PDF" type="text" onkeydown="return false" onclick="file_explorer2(\'ADJUNTAR_FACTURA_PDF\');" style="width:250px;" VALUE="'.$row["ADJUNTAR_FACTURA_PDF"] .' " required /></p>
<input type="file" name="ADJUNTAR_FACTURA_PDF" id="nono"/>
<div id="2ADJUNTAR_FACTURA_PDF">
'.$ADJUNTAR_FACTURA_PDF.'
</tr> 

<tr>

<td width="30%"><label>ADJUNTAR FACTURA FORMATO&nbsp;<a style="color:red;font:12px">(XML)</a></label></td>
<td width="70%">	<div id="drop_file_zone" ondrop="upload_file2(event,\'ADJUNTAR_FACTURA_XML\')" ondragover="return false" style="width:300px;">
<p>Suelta aquí o busca tu archivo</p>
<p><input class="form-control form-control-sm" id="ADJUNTAR_FACTURA_XML" type="text" onkeydown="return false" onclick="file_explorer2(\'ADJUNTAR_FACTURA_XML\');" style="width:250px;" VALUE="'.$row["ADJUNTAR_FACTURA_XML"] .' " required /></p>
<input type="file" name="ADJUNTAR_FACTURA_XML" id="nono"/>
<div id="2ADJUNTAR_FACTURA_XML">
'.$ADJUNTAR_FACTURA_XML.'
</tr> 
<tr>

<td width="30%"><label>NÚMERO CONSECUTIVO DE PAGO A PROVEEDORES</label></td>
<td width="70%"><input type="text" name="NUMERO_CONSECUTIVO_PROVEE" value="'.$row["NUMERO_CONSECUTIVO_PROVEE"].'"></td>
</tr> 

<tr>
<td width="30%"><label>RAZÓN SOCIAL</label></td>
<td width="70%"><input type="text" name="RAZON_SOCIAL" value="'.$row["RAZON_SOCIAL"].'"></td>
</tr> 


<tr>
<td width="30%"><label>RFC DEL PROVEEDOR</label></td>
<td width="70%"><input type="text" name="RFC_PROVEEDOR" value="'.$row["RFC_PROVEEDOR"].'"></td>
</tr> 


<tr>
<td width="30%"><label>No. DE EVENTO</label></td>
<td width="70%"><input type="text" name="NUMERO_EVENTO" value="'.$row["NUMERO_EVENTO"].'"></td>
</tr> 

<tr>
<td width="30%"><label>NOMBRE DEL EVENTO</label></td>
<td width="70%"><input type="text" name="NOMBRE_EVENTO" value="'.$row["NOMBRE_EVENTO"].'"></td>
</tr> 


<tr>
<td width="30%"><label>MOTIVO DEL GASTO</label></td>
<td width="70%"><input type="text" name="MOTIVO_GASTO" value="'.$row["MOTIVO_GASTO"].'"></td>
</tr> 

<tr>
<td width="30%"><label>CONCEPTO</label></td>
<td width="70%"><input type="text" name="CONCEPTO_PROVEE" value="'.$row["CONCEPTO_PROVEE"].'"></td>
</tr>

<tr>
<td width="30%"><label>MONTO TOTAL DE LA COTIZACIÓN O DEL ADEUDO</label></td>
<td width="70%"><input type="text" name="MONTO_TOTAL_COTIZACION_ADEUDO" value="'.$row["MONTO_TOTAL_COTIZACION_ADEUDO"].'"></td>
</tr> 

<tr style="background: #c3f5d9">
<td width="30%"><label>MONTO DE LA FACTURA</label></td>
<td width="70%"><input type="text" name="MONTO_FACTURA" value="'.$row["MONTO_FACTURA"].'"></td>
</tr>

<tr>
<td width="30%"><label>MONTO DE LA PROPINA O SERVICIO NO INCLUIDO EN LA FACTURA</label></td>
<td width="70%"><input type="text" name="MONTO_PROPINA" value="'.$row["MONTO_PROPINA"].'"></td>
</tr> 

<tr>
<td width="30%"><label>MONTO A COMPROBAR</label></td>
<td width="70%"><input type="text" name="MONTO_DEPOSITAR" value="'.$row["MONTO_DEPOSITAR"].'"></td>
</tr> 

<tr>
<td width="30%"><label>TIPO DE MONEDA O DIVISA</label></td>
<td width="70%"><input type="text" name="TIPO_DE_MONEDA" value="'.$row["TIPO_DE_MONEDA"].'"></td>
</tr>

<tr>
<td width="30%"><label>FECHA DE PAGO</label></td>
<td width="70%"><input type="text" name="FECHA_DE_PAGO" value="'.$row["FECHA_DE_PAGO"].'"></td>
</tr> 


<tr style="background: #c3f5d9">
<td width="30%"><label>STATUS DE PAGO</label></td>
<td width="70%">'.$STATUS_DE_PAGO .'</td>
</tr> 

<tr>
<td width="30%"><label>ADJUNTAR COTIZACIÓN O REPORTE: (CUAQUIER FORMATO)</label></td>
<td width="70%">	<div id="drop_file_zone" ondrop="upload_file2(event,\'ADJUNTAR_COTIZACION\')" ondragover="return false" style="width:300px;">
<p>Suelta aquí o busca tu archivo</p>
<p><input class="form-control form-control-sm" id="ADJUNTAR_COTIZACION" type="text" onkeydown="return false" onclick="file_explorer2(\'ADJUNTAR_COTIZACION\');" style="width:250px;" VALUE="'.$row["ADJUNTAR_COTIZACION"] .' " required /></p>
<input type="file" name="ADJUNTAR_COTIZACION" id="nono"/>
<div id="2ADJUNTAR_COTIZACION">
'.$ADJUNTAR_COTIZACION.'
</tr> 



<tr>
<td width="30%"><label>NÚMERO DE TARJETA</label></td>
<td width="70%"><input type="text" name="BANCO_ORIGEN" value="'.$row["BANCO_ORIGEN"].'"></td>
</tr>


<tr>
<td width="30%"><label>ACTIVO FIJO</label></td>
<td width="70%"><input type="text" name="ACTIVO_FIJO" value="'.$row["ACTIVO_FIJO"].'"></td>
</tr>

<tr>
<td width="30%"><label> GASTO FIJO</label></td>
<td width="70%"><input type="text" name="GASTO_FIJO" value="'.$row["GASTO_FIJO"].'"></td>
</tr>

<tr>
<td width="30%"><label>PAGAR CADA</label></td>
<td width="70%"><input type="text" name="PAGAR_CADA" value="'.$row["PAGAR_CADA"].'"></td>
</tr>

<tr>
<td width="30%"><label>FECHA DE PROGRAMACIÓN DE PAGO</label></td>
<td width="70%"><input type="text" name="FECHA_PPAGO" value="'.$row["FECHA_PPAGO"].'"></td>
</tr>

<tr>
<td width="30%"><label>FECHA DE TERMINACIÓN DE LA PROGRAMACIÓN</label></td>
<td width="70%"><input type="text" name="FECHA_TPROGRAPAGO" value="'.$row["FECHA_TPROGRAPAGO"].'"></td>
</tr>
<tr>
<td width="30%"><label>NÚMERO DE EVENTO (FIJO) PARA PROGRAMACIÓN</label></td>
<td width="70%"><input type="text" name="NUMERO_EVENTOFIJO" value="'.$row["NUMERO_EVENTOFIJO"].'"></td>
</tr>
<tr>
<td width="30%"><label>CLASIFICACIÓN GENERAL</label></td>
<td width="70%"><input type="text" name="CLASI_GENERAL" value="'.$row["CLASI_GENERAL"].'"></td>
</tr>
<tr>
<td width="30%"><label>SUB CLASIFICACIÓN GENERAL</label></td>
<td width="70%"><input type="text" name="SUB_GENERAL" value="'.$row["SUB_GENERAL"].'"></td>
</tr>
<tr>
<td width="30%" style="font-weight:bold;background:#A3ED8C" ><label>COMPLEMENTO DE PAGO &nbsp;<a style="color:red;font:12px">(FORMATO  XML)</a></label></td>
<td width="70%" style="font-weight:bold;background:#A3ED8C" >	<div id="drop_file_zone" ondrop="upload_file2(event,\'COMPLEMENTOS_PAGO_XML\')" ondragover="return false" style="width:300px;">
<p>Suelta aquí o busca tu archivo</p>
<p><input class="form-control form-control-sm" id="COMPLEMENTOS_PAGO_XML" type="text" onkeydown="return false" onclick="file_explorer2(\'COMPLEMENTOS_PAGO_XML\');" style="width:250px;" VALUE="'.$row["COMPLEMENTOS_PAGO_XML"] .' " required /></p>
<input type="file" name="COMPLEMENTOS_PAGO_XML" id="nono"/>
<div id="3COMPLEMENTOS_PAGO_XML">
'.$COMPLEMENTOS_PAGO_XML.'</td>
</tr> 
<tr>
<td width="30%" style="font-weight:bold;background:#A3ED8C"  ><label>COMPLEMENTO DE PAGO  (FORMATO PDF)</label></td>
<td width="70%"  style="font-weight:bold;background:#A3ED8C" >	<div id="drop_file_zone" ondrop="upload_file2(event,\'COMPLEMENTOS_PAGO_PDF\')" ondragover="return false" style="width:300px;">
<p>Suelta aquí o busca tu archivo</p>
<p><input class="form-control form-control-sm" id="COMPLEMENTOS_PAGO_PDF" type="text" onkeydown="return false" onclick="file_explorer2(\'COMPLEMENTOS_PAGO_PDF\');" style="width:250px;" VALUE="'.$row["COMPLEMENTOS_PAGO_PDF"] .' " required /></p>
<input type="file" name="COMPLEMENTOS_PAGO_PDF" id="nono"/>
<div id="3COMPLEMENTOS_PAGO_PDF">
'.$COMPLEMENTOS_PAGO_PDF.'</td>
</tr>

<tr>
<td width="30%"><label>ADJUNTAR CANCELACIONES (FORMATO PDF)</label></td>
<td width="70%">	<div id="drop_file_zone" ondrop="upload_file2(event,\'CANCELACIONES_PDF\')" ondragover="return false" style="width:300px;">
<p>Suelta aquí o busca tu archivo</p>
<p><input class="form-control form-control-sm" id="CANCELACIONES_PDF" type="text" onkeydown="return false" onclick="file_explorer2(\'CANCELACIONES_PDF\');" style="width:250px;" VALUE="'.$row["CANCELACIONES_PDF"] .' " required /></p>
<input type="file" name="CANCELACIONES_PDF" id="nono"/>
<div id="3CANCELACIONES_PDF">
'.$CANCELACIONES_PDF.'
</tr> 

<tr>
<td width="30%"><label>ADJUNTAR CANCELACIONES (FORMATO PDF)</label></td>
<td width="70%">	<div id="drop_file_zone" ondrop="upload_file2(event,\'CANCELACIONES_XML\')" ondragover="return false" style="width:300px;">
<p>Suelta aquí o busca tu archivo</p>
<p><input class="form-control form-control-sm" id="CANCELACIONES_XML" type="text" onkeydown="return false" onclick="file_explorer2(\'CANCELACIONES_XML\');" style="width:250px;" VALUE="'.$row["CANCELACIONES_XML"] .' " required /></p>
<input type="file" name="CANCELACIONES_XML" id="nono"/>
<div id="3CANCELACIONES_XML">
'.$CANCELACIONES_XML.'
</tr> 

<tr>
<td width="30%"><label>ADJUNTAR FACTURA DE COMISIÓN DESCONTADA:(FORMATO PDF)</label></td>
<td width="70%">	<div id="drop_file_zone" ondrop="upload_file2(event,\'ADJUNTAR_FACTURA_DE_COMISION_PDF\')" ondragover="return false" style="width:300px;">
<p>Suelta aquí o busca tu archivo</p>
<p><input class="form-control form-control-sm" id="ADJUNTAR_FACTURA_DE_COMISION_PDF" type="text" onkeydown="return false" onclick="file_explorer2(\'ADJUNTAR_FACTURA_DE_COMISION_PDF\');" style="width:250px;" VALUE="'.$row["ADJUNTAR_FACTURA_DE_COMISION_PDF"] .' " required /></p>
<input type="file" name="ADJUNTAR_FACTURA_DE_COMISION_PDF" id="nono"/>
<div id="3ADJUNTAR_FACTURA_DE_COMISION_PDF">
'.$ADJUNTAR_FACTURA_DE_COMISION_PDF.'
</tr>

<tr>
<td width="30%"><label>ADJUNTAR FACTURA DE COMISIÓN DESCONTADA:(FORMATO XML)</label></td>
<td width="70%">	<div id="drop_file_zone" ondrop="upload_file2(event,\'ADJUNTAR_FACTURA_DE_COMISION_XML\')" ondragover="return false" style="width:300px;">
<p>Suelta aquí o busca tu archivo</p>
<p><input class="form-control form-control-sm" id="ADJUNTAR_FACTURA_DE_COMISION_XML" type="text" onkeydown="return false" onclick="file_explorer2(\'ADJUNTAR_FACTURA_DE_COMISION_XML\');" style="width:250px;" VALUE="'.$row["ADJUNTAR_FACTURA_DE_COMISION_XML"] .' " required /></p>
<input type="file" name="ADJUNTAR_FACTURA_DE_COMISION_XML" id="nono"/>
<div id="3ADJUNTAR_FACTURA_DE_COMISION_XML">
'.$ADJUNTAR_FACTURA_DE_COMISION_XML.'
</tr>

<tr>
<td width="30%"><label> ADJUNTAR CALCULO DE COMISIÓN</label></td>
<td width="70%">	<div id="drop_file_zone" ondrop="upload_file2(event,\'CALCULO_DE_COMISION\')" ondragover="return false" style="width:300px;">
<p>Suelta aquí o busca tu archivo</p>
<p><input class="form-control form-control-sm" id="CALCULO_DE_COMISION" type="text" onkeydown="return false" onclick="file_explorer2(\'CALCULO_DE_COMISION\');" style="width:250px;" VALUE="'.$row["CALCULO_DE_COMISION"] .' " required /></p>
<input type="file" name="CALCULO_DE_COMISION" id="nono"/>
<div id="3CALCULO_DE_COMISION">
'.$CALCULO_DE_COMISION.'
</tr>

<tr>
<td width="30%"><label>MONTO DE COMISIÓN</label></td>
<td width="70%"><input type="text" name="MONTO_DE_COMISION" value="'.$row["MONTO_DE_COMISION"].'"></td>
</tr>

<tr>
<td width="30%"><label> ADJUNTAR COMPROBANTE DE DEVOLUCIÓN DE DINERO A EPC</label></td>
<td width="70%">	<div id="drop_file_zone" ondrop="upload_file2(event,\'COMPROBANTE_DE_DEVOLUCION\')" ondragover="return false" style="width:300px;">
<p>Suelta aquí o busca tu archivo</p>
<p><input class="form-control form-control-sm" id="COMPROBANTE_DE_DEVOLUCION" type="text" onkeydown="return false" onclick="file_explorer2(\'COMPROBANTE_DE_DEVOLUCION\');" style="width:250px;" VALUE="'.$row["COMPROBANTE_DE_DEVOLUCION"] .' " required /></p>
<input type="file" name="COMPROBANTE_DE_DEVOLUCION" id="nono"/>
<div id="3COMPROBANTE_DE_DEVOLUCION">
'.$COMPROBANTE_DE_DEVOLUCION.'
</tr>

<tr>
<td width="30%"><label> ADJUNTAR NOTA DE CREDITO DE COMPRA</label></td>
<td width="70%">	<div id="drop_file_zone" ondrop="upload_file2(event,\'NOTA_DE_CREDITO_COMPRA\')" ondragover="return false" style="width:300px;">
<p>Suelta aquí o busca tu archivo</p>
<p><input class="form-control form-control-sm" id="NOTA_DE_CREDITO_COMPRA" type="text" onkeydown="return false" onclick="file_explorer2(\'NOTA_DE_CREDITO_COMPRA\');" style="width:250px;" VALUE="'.$row["NOTA_DE_CREDITO_COMPRA"] .' " required /></p>
<input type="file" name="NOTA_DE_CREDITO_COMPRA" id="nono"/>
<div id="3NOTA_DE_CREDITO_COMPRA">
'.$NOTA_DE_CREDITO_COMPRA.'
</tr>

<tr>
<td width="30%"><label>PÓLIZA NÚMERO</label></td>
<td width="70%"><input type="text" name="POLIZA_NUMERO" value="'.$row["POLIZA_NUMERO"].'"></td>
</tr>

<tr>
<td width="30%"><label>NOMBRE DEL EJECUTIVO QUE REALIZÓ LA COMPRA</label></td>
<td width="70%"><input type="text" name="NOMBRE_DEL_EJECUTIVO" value="'.$row["NOMBRE_DEL_EJECUTIVO"].'"></td>
</tr>

<tr>
<td width="30%"><label>OBSERVACIONES</label></td>
<td width="70%"><input type="text" name="OBSERVACIONES_1" value="'.$row["OBSERVACIONES_1"].'"></td>
</tr>


<tr>
<td width="30%"><label>ADJUNTAR ARCHIVO RELACIONADO A ESTE GASTO</label></td>
<td width="70%">	<div id="drop_file_zone" ondrop="upload_file2(event,\'ADJUNTAR_ARCHIVO_1\')" ondragover="return false" style="width:300px;">
<p>Suelta aquí o busca tu archivo</p>
<p><input class="form-control form-control-sm" id="ADJUNTAR_ARCHIVO_1" type="text" onkeydown="return false" onclick="file_explorer2(\'ADJUNTAR_ARCHIVO_1\');" style="width:250px;" VALUE="'.$row["ADJUNTAR_ARCHIVO_1"] .' " required /></p>
<input type="file" name="ADJUNTAR_ARCHIVO_1" id="nono"/>
<div id="3ADJUNTAR_ARCHIVO_1">
'.$ADJUNTAR_ARCHIVO_1.'
</tr>


<tr>
<td width="30%"><label>FECHA DE ÚLTIMA CARGA:</label></td>
<td width="70%"><input  type=»text» readonly=»readonly» name="FECHA_DE_LLENADO" value="'.$row["FECHA_DE_LLENADO"].'"></td>
</tr>
</table>


	        <tr>
            <td width="30%"><label></label></td>  
            <td width="70%"><button class="btn btn-sm btn-outline-success px-5"  type="button" id="clickPAGOP">GUARDAR</button>
			
			<input type="hidden" value="ENVIARPAGOprovee"  name="ENVIARPAGOprovee"/>
			<input type="hidden" value="'.$row["id"].'"  name="IPpagoprovee" id="IPpagoprovee"/>
			</td>  
        </tr>

     ';
    }
    $output .= '</table></div>

	</form>';
    echo $output;
}

?>

<script>
	var fileobj;
	function upload_file2(e,name) {
	    e.preventDefault();
	    fileobj = e.dataTransfer.files[0];
	    ajax_file_upload2(fileobj,name);
	}
	 
	function file_explorer2(name) {
	    document.getElementsByName(name)[0].click();
	    document.getElementsByName(name)[0].onchange = function() {
	        fileobj = document.getElementsByName(name)[0].files[0];
	        ajax_file_upload2(fileobj,name);
	    };
	}

function ajax_file_upload2(file_obj, nombre) {
    if (!file_obj) return;

    var form_data = new FormData();
    form_data.append(nombre, file_obj);
    form_data.append("IPpagoprovee", $("#IPpagoprovee").val());

    $.ajax({
        type: 'POST',
        url: 'comprobaciones/controladorPP.php',
        dataType: 'html',
        contentType: false,
        processData: false,
        data: form_data,
        beforeSend: function() {
            $('#3' + nombre).html('<p style="color:green;"><span class="spinner-border spinner-border-sm"></span>&nbsp;Cargando archivo...</p>');
            $('#respuestaser').html('<p style="color:green;">Actualizando...</p>');
        },
        success: function(response) {
            var resp = $.trim(response);

            // ── Archivo vacío (0 bytes) ─────────────────────────────────
            if (resp.indexOf('VACIO^^') === 0) {
                $('#3' + nombre).html(
                    '<p style="color:red;font-weight:600;">⚠️ EL ARCHIVO ESTÁ VACÍO (0 KB). ' +
                    'Verifica que el archivo tenga contenido antes de subirlo.</p>'
                );
                $('#' + nombre).val('');

            // ── Sin extensión ───────────────────────────────────────────
            } else if (resp.indexOf('SIN_EXTENSION^^') === 0) {
                $('#3' + nombre).html(
                    '<p style="color:red;font-weight:600;">⚠️ EL ARCHIVO NO TIENE EXTENSIÓN RECONOCIDA. ' +
                    'Asegúrate de que el nombre termine en .xml, .pdf, .jpg, etc.</p>'
                );
                $('#' + nombre).val('');

            // ── Error de subida al servidor ─────────────────────────────
            } else if (resp.indexOf('ERROR_SUBIDA^^') === 0) {
                $('#3' + nombre).html(
                    '<p style="color:red;font-weight:600;">⚠️ ERROR AL RECIBIR EL ARCHIVO EN EL SERVIDOR. ' +
                    'Puede que sea demasiado grande o que la conexión se interrumpió. Intenta de nuevo.</p>'
                );
                $('#' + nombre).val('');

            // ── Error al guardar en disco ───────────────────────────────
            } else if (resp === '1') {
                $('#3' + nombre).html(
                    '<p style="color:red;font-weight:600;">⚠️ ERROR AL GUARDAR EL ARCHIVO EN EL SERVIDOR. ' +
                    'Intenta de nuevo o contacta a soporte técnico.</p>'
                );
                $('#' + nombre).val('');

            // ── Formato no permitido ────────────────────────────────────
            } else if (resp === '2') {
                var exts = (nombre === 'ADJUNTAR_FACTURA_XML') ? 'XML' :
                           (nombre === 'ADJUNTAR_FACTURA_PDF') ? 'PDF' :
                           'PDF, JPG, PNG, DOCX, XML u otro formato de documento';
                $('#3' + nombre).html(
                    '<p style="color:red;">⚠️ FORMATO DE ARCHIVO NO PERMITIDO. ' +
                    'Este campo acepta únicamente: <strong>' + exts + '</strong>.</p>'
                );
                $('#' + nombre).val('');

            // ── UUID duplicado en 07XML o 02XML ─────────────────────────
            } else if (resp === '3' || resp.indexOf('3|') === 0) {
                var partesDuplicado = resp.split('|');
                var idDuplicado = partesDuplicado.length > 1 ? partesDuplicado[1] : '';
                var numeroEventoDuplicado = partesDuplicado.length > 2 ? partesDuplicado[2] : '';

                var esPagoProveedores = idDuplicado.indexOf('2^^') === 0;
                if (esPagoProveedores) {
                    idDuplicado = idDuplicado.replace('2^^', '');
                    var mensajePago = '⚠️ UUID YA REGISTRADO EN PAGO A PROVEEDORES';
                    if (idDuplicado !== '') { mensajePago += ' — Solicitud: <strong>' + idDuplicado + '</strong>'; }
                    if (numeroEventoDuplicado !== '') { mensajePago += ', Evento: <strong>' + numeroEventoDuplicado + '</strong>'; }
                    $('#3' + nombre).html('<p style="color:#9C2007;font-weight:600;">' + mensajePago + '</p>');
                } else {
                    var mensajeDup = '⚠️ UUID PREVIAMENTE CARGADO';
                    if (idDuplicado !== '') { mensajeDup += ' CON EL ID: ' + idDuplicado + '.'; }
                    if (numeroEventoDuplicado !== '') { mensajeDup += ' EN EL NÚMERO DE EVENTO: ' + numeroEventoDuplicado + '.'; }
                    $('#3' + nombre).html('<p style="color:red;font-weight:600;">' + mensajeDup + '</p>');
                }
                $('#' + nombre).val('');

            // ── Ya existe un adjunto ────────────────────────────────────
            } else if (resp === '4') {
                $('#3' + nombre).html(
                    '<p style="color:red;">⚠️ Ya existe un archivo adjunto. Primero bórralo para subir uno nuevo.</p>'
                );
                $('#' + nombre).val('');

            // ── Formato XML requerido ───────────────────────────────────
            } else if (resp === 'El archivo debe estar en formato XML.') {
                $('#3' + nombre).html('<p style="color:red;font-weight:600;">⚠️ ' + resp + '</p>');
                $('#' + nombre).val('');

            // ── Receptor no válido ──────────────────────────────────────
            } else if (resp.indexOf('6^^') === 0) {
                var partesReceptor = resp.split('^^');
                var receptorOriginal = partesReceptor.length > 1 ? partesReceptor[1] : '';
                var receptorNorm = (receptorOriginal || '').toString().trim().toUpperCase().replace(/\s+/g, ' ');
                $('#3' + nombre).html(
                    '<p style="color:red;font-weight:600;">⚠️ EL RECEPTOR DE LA FACTURA NO ES: EPC, INN, EVE520. ' +
                    'RECEPTOR DETECTADO: <strong>' + receptorNorm + '</strong></p>'
                );
                $('#' + nombre).val('');

            // ── Éxito ───────────────────────────────────────────────────
            } else {
                $('#' + nombre).val(response);
                $('#3' + nombre).html('<p style="color:green;">✅ ¡Archivo cargado con éxito!</p>');
                $('#respuestaser').html('<p style="color:green;">✅ ¡Actualizado!</p>');
                $('#reseteaxml').remove();
            }
        }
    });
}
	
	
    $(document).ready(function(){


$("#clickPAGOP").click(function(){
	
   $.ajax({  
    url:"comprobaciones/controladorPP.php",
    method:"POST",  
    data:$('#ListadoPAGOPROVEEform').serialize(),

    beforeSend:function(){  
    $('#mensajepagoproveedores').html('cargando'); 
    }, 	
	
    success:function(data){
	
		if($.trim(data)=='Ingresado' || $.trim(data)=='Actualizado'){
				
			$('#dataModal').modal('hide');
			//$("#results").load("pagoproveedores/fetch_pagesPP.php");
			$.getScript(load(1));
			$("#mensajepagoproveedores").html("<span id='ACTUALIZADO' >"+data+"</span>");

			}else{
				
			$("#mensajepagoproveedores").html(data);
			
		}
    }  
   });
   
});

		});
		
	</script>