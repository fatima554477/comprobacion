<?php
    if(!isset($_SESSION)) 
    { 
        session_start(); 
    }  
//select.php  CONTRASENA_DE1
echo "<STRONG>";
echo 'REGISTRO ACTUAL </STRONG>'.$identioficador = isset($_POST["personal_id"])?$_POST["personal_id"]:'';

if($identioficador != '')
{
	
	$output = '';
	require "../reportes/controladorMH.php";
	$queryVISTAPREV = $match->Listado_BBVA($identioficador);
	$conn = $match->db();
	$idempermiso = $_SESSION["idempermiso"];
	echo ', <STRONG>ROL DE TRABAJO: </STRONG>'.$permiso = $conexion2->idempermiso($idempermiso,$conn);
	$var_respuesta = $conexion->variablespermisos('','BBVA_VERIFICAR','ver');
	echo ', <STRONG>TARJETA: </STRONG> BBVA. ';
?>
<div id="actualizatabla">




<?php
 $output .= '
<div id="mensajeMATCHAdocumentos"></div> 
 <form  id="Listado_MATCH_documentos_form"> 
      <div class="table-responsive">  
           <table class="table table-bordered">
		   <table class="table table-bordered">
		   <tr>
		   <td width="5.5%"><STRONG>id</STRONG></td>		   
		   <td width="12.5%"><STRONG>COLABORADOR</STRONG></td>
		   <td width="12.5%"><STRONG>INTITUCIÓN BANCARIA</STRONG></td>
		   <td width="12.5%"><STRONG>NOMBRE COMERCIAL</STRONG></td>
		   <td width="12.5%"><STRONG>FECHA DEL CARGO</STRONG></td>
		   <td width="12.5%"><STRONG>MONTO</STRONG></td>
		   <td width="12.5%"><STRONG>OBSERVACIONES</STRONG></td>
		   <td width="12.5%"><STRONG>FECHA</STRONG></td>
		   <td width="12.5%"><STRONG>MATCH</STRONG></td>
		   </tr>
		   ';


while($row=mysqli_fetch_array($queryVISTAPREV)){
$output .= '<tr>
<td width="5.5%">'.$row['id'].'</td>
<td width="12.5%">'.$row['COLABORADOR_BBVA'].'</td>
<td width="12.5%">'.$row['TARJETA_BBVA'].'</td>
<td width="12.5%">'.$row['ESTABLECIMIENTO_BBVA'].'</td>
<td width="12.5%">'.$row['FECHADD_BBVA'].'</td>
<td width="12.5%">'. number_format( $row['MONTO_BBVA'],2,'.',',').'</td>
<td width="12.5%">'.$row['OBSERVACIONES_BBVA'].'</td>
<td width="12.5%">'.$row['FECHA_BBVA'].'</td>
<td width="12.5%">

<input type="checkbox" style="width:30px; color:red;" name="documentos'.$row["id"].'"  value="'.$row["id"].'" class="form-check-input" 
			id="documentos'.$row['id'].'"  
			onclick="pasarmatchdocumento('.$identioficador.','.$row["id"].',\'TARJETABBVA\')" 
			'./*regresa checked*/$match->validaexistematchCOMPROBACION($identioficador,$row["id"],'TARJETABBVA').' 
			'./*regresa disabled*/$match->validaexistematch3resCOMPROBACION($row["id"],$identioficador,$permiso,'TARJETABBVA',$var_respuesta).'>
&nbsp;&nbsp;&nbsp;&nbsp;'.$match->validaexistematch4COMPROBACION($row["id"],'TARJETABBVA').'
</td>
</tr>
';
}
echo $output;
?>

	        <tr>
            <td colspan="7" ><label>GUARDAR</label></td>  
            <td><button class="btn btn-sm btn-outline-success px-5"  type="button" id="clickPAGOP">GUARDAR</button>
			
			<input type="hidden" value="ENVIARPAGOprovee"  name="ENVIARPAGOprovee"/>
			<input type="hidden" value="<?php echo $row["id"];?>"  name="IPpagoprovee" id="IPpagoprovee"/>
			</td>  
        </tr>
    
 </table>
 </form>
 </div>
<?php
 }
?> 


<script>

function pasarmatchdocumento(pasardocumentomatch_id,IpMATCHDOCUMENTOS2,tarjeta){
	//$('#personal_detalles4').html();

	var checkBox = document.getElementById("documentos"+IpMATCHDOCUMENTOS2);
	//var checkBox = document.getElementById("documentos"+pasardocumentomatch_id);
	var pasardocumentomatch_text = "";
	if (checkBox.checked == true){
	pasardocumentomatch_text = "si";
	}else{
	pasardocumentomatch_text = "no";
	}
	  $.ajax({
		//url:'comprobacionesVYO/controladorPP.php',
		url:'reportes/controladorMH.php',
		method:'POST',
		data:{pasardocumentomatch_id:pasardocumentomatch_id,pasardocumentomatch_text:pasardocumentomatch_text,IpMATCHDOCUMENTOS2:IpMATCHDOCUMENTOS2,tarjeta:tarjeta},
		beforeSend:function(){
		$('#mensajeMATCHAdocumentos').html('cargando');
	},
		success:function(data){
    $('#mensajeMATCHAdocumentos').html("<span id='ACTUALIZADO' >"+data+"</span>");
			$.getScript(load(1));
	//$("#reset_BBVA").load(location.href + " #reset_BBVA");		
	/*$("#actualizatotalpagadoingreso").load(location.href + " #actualizatotalpagadoingreso");
		$("#reset_totales_egresos").load(location.href + " #reset_totales_egresos");
		$('#mensapagosingresos').html("<span id='ACTUALIZADO' >"+data+"</span>");*/
	}
	});

}

    $(document).ready(function(){
$("#clickPAGOP").click(function(){
	
   $.ajax({  
    url:"comprobacionesVYO/controladorPP.php",
    method:"POST",  
    data:$('#ListadoPAGOPROVEEform').serialize(),

    beforeSend:function(){  
    $('#mensajepagoproveedores').html('cargando'); 
    }, 	
	
    success:function(data){
		$('#dataModal14').modal('hide');
		if($.trim(data)=='Ingresado' || $.trim(data)=='Actualizado'){

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