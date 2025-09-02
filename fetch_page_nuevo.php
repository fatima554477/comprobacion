<div id="content">     
	<hr/>
	<strong>
		<p class="mb-0 text-uppercase">
			<img src="includes/contraer31.png" id="mostrar3" onclick="load(1);" style="cursor:pointer;"/>
			<img src="includes/contraer41.png" id="ocultar3" style="cursor:pointer;"/>&nbsp;&nbsp;&nbsp; FILTRO COMPROBACIONES DE GASTOS 
		</p>
	</strong>
</div>

<div id="mensajefiltro">
	<div class="progress" style="width: 25%;">
		<div class="progress-bar" role="progressbar" style="width: <?php echo $Aeventosporcentaje ; ?>%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"><?php echo $Aeventosporcentaje ; ?>%</div>
	</div>
</div>
							
<div id="target3" style="display:block;" class="content2">
	<div class="card">
		<div class="card-body">
			<!--aqui inicia filtro-->
			<div class="row text-center" id="loader" style="position: absolute;top: 140px;left: 50%"></div>
			<table width="100%" border="0">
				<tr>

<td width="30%" align="center"> 
    <span>MOSTRAR</span>
    <select class="form-select mb-3" id="per_page" onchange="load(1);">
  

        <option value="20" <?php if($_REQUEST['per_page']=='20') echo 'selected'; ?>>20</option>
        <option value="50" <?php if($_REQUEST['per_page']=='50') echo 'selected'; ?>>50</option>
        <option value="100"<?php if($_REQUEST['per_page']=='100')echo 'selected'; ?>>100</option>
	<option value="100000"  <?php if($_REQUEST['per_page']=='100000')  echo 'selected'; ?>>TODOS</option>
        
    </select>
</td>
					<td width="30%" align="center">					
						<button class="btn btn-sm btn-outline-success px-5" type="button" onclick="load(1);">BUSCAR</button>
					</td>
<td width="30%" align="center">
    <span>PLANTILLA</span>
    <?php
    $encabezado = '';
    $option = '';
    $queryper = $conexion->desplegablesfiltro('comprobaciones','');
    $encabezado = '<select class="form-select mb-3" id="DEPARTAMENTO2WE" required="" onchange="load(1);">
        <option value="">SELECCIONA UNA OPCIÓN</option>';
    /*linea para multiples colores*/
    $fondos = array("fff0df","f4ffdf","dfffed","dffeff","dfe8ff","efdfff","ffdffd","efdfff","ffdfe9");
    $num = 0;
    /*linea para multiples colores*/	
    while($row1 = mysqli_fetch_array($queryper)) {
        /*linea para multiples colores*/
        if($num==8) {
            $num=0;
        } else {
            $num++;
        }
        /*linea para multiples colores*/		
        $select = '';
        if($_SESSION['DEPARTAMENTO'] == $row1['nombreplantilla']) {
            $select = "selected";
        }

        // Cambio aplicado aquí: strtoupper() al texto mostrado
        $option .= '<option style="background: #'.$fondos[$num].'" '.$select.' value="'.$row1['nombreplantilla'].'">'.strtoupper($row1['nombreplantilla']).'</option>';
    }
    echo $encabezado.$option.'</select>';			
    ?>	
</td>
					<p>
						<strong style="background:#ffb6c1"> ROSA:</strong> 
						FORMAS DE PAGO DIFERENTES A (04 TARJETA)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<strong style="background:#fdfe87"> AMARILLO:</strong> 
						COMPROBACIÓN SIN XML
					</p>
				</tr>
			</table>
			<div class="datos_ajax"></div>
			<!--aqui termina filtro-->
		</div>
	</div>
</div>

<?php
if($_GET['num_evento']==true){
	$_SESSION['num_evento']=$_GET['num_evento'];
}else{
	$_SESSION['num_evento']='';
}
require "clases/script.filtro.php";
?>