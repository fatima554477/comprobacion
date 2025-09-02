<?php
    if(!isset($_SESSION)) 
    { 
        session_start(); 
    }  
//select.php  CONTRASENA_DE1
echo $identioficador = isset($_POST["personal_id"])?$_POST["personal_id"]:'';
if($identioficador != '')
{
 $output = '';
	require "controladorPP.php";

//$conexion = NEW pagoproveedores();
$queryVISTAPREV = $pagoproveedores->Listado_match($identioficador);
//print_r($queryVISTAPREV);
//exit;
?>
<div id="actualizatabla">



<table>
<?php
while($row=mysqli_fetch_array($queryVISTAPREV)){
?>

<tr>

<td>
<?php
echo $row['id'];

?> 
</td>

<td>
<?php
echo $row['COLABORADOR_BBVA'];
?> 
</td>
</tr>
<?php
}
?>
	        <tr>
            <td width="30%"><label>GUARDAR</label></td>  
            <td width="70%"><button class="btn btn-sm btn-outline-success px-5"  type="button" id="clickPAGOP">GUARDAR</button>
			
			<input type="hidden" value="ENVIARPAGOprovee"  name="ENVIARPAGOprovee"/>
			<input type="hidden" value="<?php echo $row["id"];?>"  name="IPpagoprovee" id="IPpagoprovee"/>
			</td>  
        </tr>
    
 </table>
 
 </div>
<?php
 }
?> 


<script>

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