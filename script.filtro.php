<script type="text/javascript">

	function pasarpagado2(pasarpagado_id){


	var checkBox = document.getElementById("pasarpagado1a"+pasarpagado_id);
	var pasarpagado_text = "";
	if (checkBox.checked == true){
	pasarpagado_text = "si";
	}else{
	pasarpagado_text = "no";
	}
	  $.ajax({
		url:'comprobaciones/controladorPP.php',
		method:'POST',
		data:{pasarpagado_id:pasarpagado_id,pasarpagado_text:pasarpagado_text},
		beforeSend:function(){
		$('#pasarpagado2').html('cargando');
	},
		success:function(data){
		var result = data.split('^');			
		$('#pasarpagado2').html("<span 'ACTUALIZADO'</span>").fadeIn().delay(500).fadeOut();
		load(1);
		
		if(pasarpagado_text=='si'){
		$('#color_pagado1a'+pasarpagado_id).css('background-color', '#ceffcc');
		}
		if(pasarpagado_text=='no'){
		$('#color_pagado1a'+pasarpagado_id).css('background-color', '#e9d8ee');
		}		
		
	}
	});
}


	function STATUS_RESPONSABLE_EVENTO(RESPONSABLE_EVENTO_id){


	var checkBox = document.getElementById("STATUS_RESPONSABLE_EVENTO"+RESPONSABLE_EVENTO_id);
	var RESPONSABLE_text = "";
	if (checkBox.checked == true){
	RESPONSABLE_text = "si";
	}else{
	RESPONSABLE_text = "no";
	}
	  $.ajax({
		url:'comprobaciones/controladorPP.php',
		method:'POST',
		data:{RESPONSABLE_EVENTO_id:RESPONSABLE_EVENTO_id,RESPONSABLE_text:RESPONSABLE_text},
		beforeSend:function(){
		$('#pasarpagado2').html('cargando');
	},
		success:function(data){
		var result = data.split('^');				
		$('#pasarpagado2').html("<span id='ACTUALIZADO' >"+result[0]+"</span>");
		
		if(result[1]=='si'){
		$('#color_RESPONSABLE_EVENTO'+RESPONSABLE_EVENTO_id).css('background-color', '#ceffcc');
		}
		if(result[1]=='no'){
		$('#color_RESPONSABLE_EVENTO'+RESPONSABLE_EVENTO_id).css('background-color', '#e9d8ee');
		}
		
	}
	});
}






	function STATUS_AUDITORIA1(AUDITORIA1_id){


	var checkBox = document.getElementById("STATUS_AUDITORIA1"+AUDITORIA1_id);
	var AUDITORIA1_text = "";
	if (checkBox.checked == true){
	AUDITORIA1_text = "si";
	}else{
	AUDITORIA1_text = "no";
	}

	  $.ajax({
		url:'comprobaciones/controladorPP.php',
		method:'POST',
		data:{AUDITORIA1_id:AUDITORIA1_id,AUDITORIA1_text:AUDITORIA1_text},
		beforeSend:function(){
		$('#STATUS_AUDITORIA1').html('cargando');
	},
		success:function(data){
		var result = data.split('^');				
		$('#STATUS_AUDITORIA1').html("ACTUALIZADO").fadeIn().delay(1000).fadeOut();
		 load(1);

		if(result[1]=='si'){
		$('#color_AUDITORIA1'+AUDITORIA1_id).css('background-color', '#ceffcc');
		}
		if(result[1]=='no'){
		$('#color_AUDITORIA1'+AUDITORIA1_id).css('background-color', '#e9d8ee');
		}
	   	
		
	}
	});
}


function STATUS_CHECKBOX(CHECKBOX_id, permisoModificar) {
    var checkBox = document.getElementById("STATUS_CHECKBOX" + CHECKBOX_id);
    var CHECKBOX_text = checkBox.checked ? "si" : "no";

    // Cambiar color visual inmediato (optimista)
    var newColor = checkBox.checked ? '#ceffcc' : '#e9d8ee';
    $('#color_CHECKBOX' + CHECKBOX_id).css('background-color', newColor);

    let monto = $('#montoOriginal_' + CHECKBOX_id).text().replace(/,/g, '');
    
    // Bloqueo inmediato si se activa sin permiso
    if (checkBox.checked && !permisoModificar) {
        setTimeout(() => {
            checkBox.disabled = true;
        }, 100);
    }

    // Actualizar el valor calculado en la interfaz inmediatamente
    if (checkBox.checked) {
        $('#valorCalculado_' + CHECKBOX_id).text('');
    } else {
        if (!isNaN(monto)) {
            let resultado = monto * 1.46;
            let resultadoFormateado = resultado.toLocaleString('es-MX', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            $('#valorCalculado_' + CHECKBOX_id).text('$' + resultadoFormateado);
        } else {
            $('#valorCalculado_' + CHECKBOX_id).text('NaN');
        }
    }

    // Enviar actualización al servidor
    $.ajax({
        url: 'comprobaciones/controladorPP.php',
        method: 'POST',
        data: { 
            CHECKBOX_id: CHECKBOX_id,
            CHECKBOX_text: CHECKBOX_text 
        },
        beforeSend: function() {
            $('#ajax-notification')
                .html('<div class="loader"></div> ⏳ ACTUALIZANDO...')
                .fadeIn();
        },
        success: function(data) {
            var result = data.split('^'); // ejemplo de retorno: "ok^si" o "ok^no"

            // Mostrar notificación de éxito
            $('#ajax-notification')
                .html("✅ ACTUALIZADO")
                .delay(1000)
                .fadeOut();

            // Validar respuesta del servidor
            if (result[1] === 'si') {
                $('#color_CHECKBOX' + CHECKBOX_id).css('background-color', '#ceffcc');
                $('#valorCalculado_' + CHECKBOX_id).text('');
                
                // Bloquear después de confirmación si no hay permiso
                if (!permisoModificar) {
                    checkBox.disabled = true;
                }
            } else if (result[1] === 'no') {
                $('#color_CHECKBOX' + CHECKBOX_id).css('background-color', '#e9d8ee');
                
                if (!isNaN(monto)) {
                    let resultado = monto * 1.46;
                    let resultadoFormateado = resultado.toLocaleString('es-MX', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                    $('#valorCalculado_' + CHECKBOX_id).text('$' + resultadoFormateado);
                } else {
                    $('#valorCalculado_' + CHECKBOX_id).text('NaN');
                }
                
                // Re-habilitar si falló el guardado
                checkBox.disabled = false;
            }
        },
        error: function() {
            // Revertir el cambio si ocurre un error
            checkBox.checked = !checkBox.checked;
            let originalColor = checkBox.checked ? '#ceffcc' : '#e9d8ee';
            $('#color_CHECKBOX' + CHECKBOX_id).css('background-color', originalColor);
            
            // Re-habilitar en caso de error
            checkBox.disabled = false;

            $('#ajax-notification')
                .html("❌ Error al actualizar")
                .delay(2000)
                .fadeOut();
        }
    });
    recalcularTotal();
}


function recalcularTotal() {
    let total = 0;

    $('[id^=valorCalculado_]').each(function() {
        let texto = $(this).text().replace(/[$,]/g, ''); // quitar $ y ,
        let valor = parseFloat(texto);
        if (!isNaN(valor)) {
            total += valor;
        }
    });

    let totalFormateado = total.toLocaleString('es-MX', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    $('#totalCalculado').text('$' + totalFormateado);
}











	function STATUS_AUDITORIA2(AUDITORIA2_id){
	

	var checkBox = document.getElementById("STATUS_AUDITORIA2"+AUDITORIA2_id);
	var AUDITORIA2_text = "";
	if (checkBox.checked == true){
	AUDITORIA2_text = "si";
	}else{
	AUDITORIA2_text = "no";
	}
	  $.ajax({
		url:'comprobaciones/controladorPP.php',
		method:'POST',
		data:{AUDITORIA2_id:AUDITORIA2_id,AUDITORIA2_text:AUDITORIA2_text},
		beforeSend:function(){
		$('#pasarpagado2').html('cargando');
	},
		success:function(data){
		var result = data.split('^');				
		$('#pasarpagado2').html("Cargando...").fadeIn().delay(500).fadeOut();

		if(result[1]=='si'){
		$('#color_AUDITORIA2'+AUDITORIA2_id).css('background-color', '#ceffcc');
		}
		if(result[1]=='no'){
		$('#color_AUDITORIA2'+AUDITORIA2_id).css('background-color', '#e9d8ee');
		}		
		
	}
	});
}



	function STATUS_FINANZAS(FINANZAS_id){


	var checkBox = document.getElementById("STATUS_FINANZAS"+FINANZAS_id);
	var FINANZAS_text = "";
	if (checkBox.checked == true){
	FINANZAS_text = "si";
	}else{
	FINANZAS_text = "no";
	}
	  $.ajax({
		url:'comprobaciones/controladorPP.php',
		method:'POST',
		data:{FINANZAS_id:FINANZAS_id,FINANZAS_text:FINANZAS_text},
		beforeSend:function(){
		$('#pasarpagado2').html('cargando');
	},
		success:function(data){
		var result = data.split('^');				
		$('#pasarpagado2').html("Cargando...").fadeIn().delay(500).fadeOut();
		
		if(result[1]=='si'){
		$('#color_FINANZAS'+FINANZAS_id).css('background-color', '#ceffcc');
		}
		if(result[1]=='no'){
		$('#color_FINANZAS'+FINANZAS_id).css('background-color', '#e9d8ee');
		}		
		
	}
	});
}

	function STATUS_VENTAS(VENTAS_id){
	

	var checkBox = document.getElementById("STATUS_VENTAS"+VENTAS_id);
	var VENTAS_text = "";
	if (checkBox.checked == true){
	VENTAS_text = "si";
	}else{
	VENTAS_text = "no";
	}
	  $.ajax({
		url:'comprobaciones/controladorPP.php',
		method:'POST',
		data:{VENTAS_id:VENTAS_id,VENTAS_text:VENTAS_text},
		beforeSend:function(){
		$('#pasarpagado2').html('cargando');
	},
		success:function(data){
		var result = data.split('^');				
		$('#pasarpagado2').html("Cargando...").fadeIn().delay(500).fadeOut();
		
		if(result[1]=='si'){
		$('#color_VENTAS'+VENTAS_id).css('background-color', '#ceffcc');
		}
		if(result[1]=='no'){
		$('#color_VENTAS'+VENTAS_id).css('background-color', '#e9d8ee');
		}		
		
	}
	});
}

	$(function() {
		
		load(1);
	});

	function load(page){
		var getVal = id => $("#" + id).val();
		var query = $("#NOMBRE_EVENTO").val();
		var DEPARTAMENTO2 = getVal("DEPARTAMENTO2WE");
		var NUMERO_CONSECUTIVO_PROVEE = getVal("NUMERO_CONSECUTIVO_PROVEE_1");
		var RAZON_SOCIAL = getVal("RAZON_SOCIAL_1");
		var RFC_PROVEEDOR = getVal("RFC_PROVEEDOR_1");
		var NUMERO_EVENTO = getVal("NUMERO_EVENTO_1");
		var NOMBRE_EVENTO = getVal("NOMBRE_EVENTO_1");
		var MOTIVO_GASTO = getVal("MOTIVO_GASTO_1");
		var CONCEPTO_PROVEE = getVal("CONCEPTO_PROVEE_1");
		var MONTO_TOTAL_COTIZACION_ADEUDO = getVal("MONTO_TOTAL_COTIZACION_ADEUDO_1");
		var MONTO_FACTURA = getVal("MONTO_FACTURA_1");
		var MONTO_PROPINA = getVal("MONTO_PROPINA_1");
		var MONTO_DEPOSITAR = getVal("MONTO_DEPOSITAR_1");
		var TIPO_DE_MONEDA = getVal("TIPO_DE_MONEDA_1");
		var PFORMADE_PAGO = getVal("PFORMADE_PAGO_1");
		var FECHA_A_DEPOSITAR = getVal("FECHA_A_DEPOSITAR_1");
		var STATUS_DE_PAGO = getVal("STATUS_DE_PAGO_1");
		var BANCO_ORIGEN = getVal("BANCO_ORIGEN1AA");
		var ACTIVO_FIJO = getVal("ACTIVO_FIJO_1");
		var GASTO_FIJO = getVal("GASTO_FIJO_1");
		var PAGAR_CADA = getVal("PAGAR_CADA_1");
		var FECHA_PPAGO = getVal("FECHA_PPAGO_1");
		var FECHA_TPROGRAPAGO = getVal("FECHA_TPROGRAPAGO_1");
		var NUMERO_EVENTOFIJO = getVal("NUMERO_EVENTOFIJO_1");
		var CLASI_GENERAL = getVal("CLASI_GENERAL_1");
		var SUB_GENERAL = getVal("SUB_GENERAL_1");
		var MONTO_DE_COMISION = getVal("MONTO_DE_COMISION_1");
		var POLIZA_NUMERO = getVal("POLIZA_NUMERO_1");
		var NOMBRE_DEL_EJECUTIVO = getVal("NOMBRE_DEL_EJECUTIVO_1");
		var NOMBRE_DEL_AYUDO = getVal("NOMBRE_DEL_AYUDO_1");
		var OBSERVACIONES_1 = getVal("OBSERVACIONES_1_1_1");
		var FECHA_DE_LLENADO = getVal("FECHA_DE_LLENADO_1");
		var ADJUNTAR_COTIZACION_1_1 = getVal("ADJUNTAR_COTIZACION_1_1");
		var TIPO_CAMBIOP = getVal("TIPO_CAMBIOP");
		var TOTAL_ENPESOS = getVal("TOTAL_ENPESOS");
		var IMPUESTO_HOSPEDAJE = getVal("IMPUESTO_HOSPEDAJE");
		var NOMBRE_COMERCIAL = getVal("NOMBRE_COMERCIAL_1");
		var IVA = getVal("IVA");
		var TImpuestosRetenidosIVA = getVal("TImpuestosRetenidosIVA_5");
		var TImpuestosRetenidosISR = getVal("TImpuestosRetenidosISR_5");
		var descuentos = getVal("descuentos_5");
		var UUID = getVal("UUID");
		var metodoDePago = getVal("metodoDePago");
		var total = getVal("total");
		var serie = getVal("serie");
		var folio = getVal("folio");
		var regimenE = getVal("regimenE");
		var UsoCFDI = getVal("UsoCFDI");
		var TImpuestosTrasladados = getVal("TImpuestosTrasladados");
		var TImpuestosRetenidos = getVal("TImpuestosRetenidos");
		var Version = getVal("Version");
		var tipoDeComprobante = getVal("tipoDeComprobante");
		var condicionesDePago = getVal("condicionesDePago");
		var fechaTimbrado = getVal("fechaTimbrado");
		var nombreR = getVal("nombreR");
		var rfcR = getVal("rfcR");
		var Moneda = getVal("Moneda");
		var TipoCambio = getVal("TipoCambio");
		var ValorUnitarioConcepto = getVal("ValorUnitarioConcepto");
		var DescripcionConcepto = getVal("DescripcionConcepto");
		var ClaveUnidad = getVal("ClaveUnidad");
		var ClaveProdServ = getVal("ClaveProdServ");
		var Cantidad = getVal("Cantidad");
		var ImporteConcepto = getVal("ImporteConcepto");
		var UnidadConcepto = getVal("UnidadConcepto");
		var TUA = getVal("TUA");
		var TuaTotalCargos = getVal("TuaTotalCargos");
		var Descuento = getVal("Descuento");
		var propina = getVal("propina");
		var per_page = getVal("per_page");
		var parametros = {
			"action": "ajax",
			"page": page,
			'query': query,
			'per_page': per_page,
			
			
			
			'NUMERO_CONSECUTIVO_PROVEE': NUMERO_CONSECUTIVO_PROVEE,
			'RAZON_SOCIAL': RAZON_SOCIAL,
			'RFC_PROVEEDOR': RFC_PROVEEDOR,
			'NUMERO_EVENTO': NUMERO_EVENTO,
			'NOMBRE_EVENTO': NOMBRE_EVENTO,
			'MOTIVO_GASTO': MOTIVO_GASTO,
			'CONCEPTO_PROVEE': CONCEPTO_PROVEE,
			'MONTO_TOTAL_COTIZACION_ADEUDO': MONTO_TOTAL_COTIZACION_ADEUDO,
			'MONTO_FACTURA': MONTO_FACTURA,
			'MONTO_PROPINA': MONTO_PROPINA,
			'MONTO_DEPOSITAR': MONTO_DEPOSITAR,
			'TIPO_DE_MONEDA': TIPO_DE_MONEDA,
			'PFORMADE_PAGO': PFORMADE_PAGO,
			'FECHA_A_DEPOSITAR': FECHA_A_DEPOSITAR,
			'STATUS_DE_PAGO': STATUS_DE_PAGO,
			'BANCO_ORIGEN': BANCO_ORIGEN,
			'ACTIVO_FIJO': ACTIVO_FIJO,
			'GASTO_FIJO': GASTO_FIJO,
			'PAGAR_CADA': PAGAR_CADA,
			'FECHA_PPAGO': FECHA_PPAGO,
			'FECHA_TPROGRAPAGO': FECHA_TPROGRAPAGO,
			'NUMERO_EVENTOFIJO': NUMERO_EVENTOFIJO,
			'CLASI_GENERAL': CLASI_GENERAL,
			'SUB_GENERAL': SUB_GENERAL,
			'MONTO_DE_COMISION': MONTO_DE_COMISION,
			'POLIZA_NUMERO': POLIZA_NUMERO,
			'NOMBRE_DEL_EJECUTIVO': NOMBRE_DEL_EJECUTIVO,
			'NOMBRE_DEL_AYUDO': NOMBRE_DEL_AYUDO,
			'OBSERVACIONES_1': OBSERVACIONES_1,
			'FECHA_DE_LLENADO': FECHA_DE_LLENADO,
			'ADJUNTAR_COTIZACION_1_1': ADJUNTAR_COTIZACION_1_1,
			'TIPO_CAMBIOP': TIPO_CAMBIOP,
			'TOTAL_ENPESOS': TOTAL_ENPESOS,
			'IMPUESTO_HOSPEDAJE': IMPUESTO_HOSPEDAJE,
			'TImpuestosRetenidosIVA_5': TImpuestosRetenidosIVA,
			'TImpuestosRetenidosISR_5': TImpuestosRetenidosISR,
			'descuentos_5': descuentos,
			'NOMBRE_COMERCIAL': NOMBRE_COMERCIAL,
			'UUID': UUID,
			'metodoDePago': metodoDePago,
			'total': total,
			'serie': serie,
			'folio': folio,
			'regimenE': regimenE,
			'UsoCFDI': UsoCFDI,
			'TImpuestosTrasladados': TImpuestosTrasladados,
			'TImpuestosRetenidos': TImpuestosRetenidos,
			'Version': Version,
			'tipoDeComprobante': tipoDeComprobante,
			'condicionesDePago': condicionesDePago,
			'fechaTimbrado': fechaTimbrado,
			'nombreR': nombreR,
			'rfcR': rfcR,
			'Moneda': Moneda,
			'TipoCambio': TipoCambio,
			'ValorUnitarioConcepto': ValorUnitarioConcepto,
			'DescripcionConcepto': DescripcionConcepto,
			'ClaveUnidad': ClaveUnidad,
			'ClaveProdServ': ClaveProdServ,
			'Cantidad': Cantidad,
			'ImporteConcepto': ImporteConcepto,
			'UnidadConcepto': UnidadConcepto,
			'TUA': TUA,
			'TuaTotalCargos': TuaTotalCargos,
			'Descuento': Descuento,
			'propina': propina,
			
			
			'DEPARTAMENTO2':DEPARTAMENTO2
			};
			$("#loader").fadeIn('slow');
    $.ajax({
        url: 'comprobaciones/clases/controlador_filtro.php', 
        type: 'POST',
        data: parametros,
						 beforeSend: function(objeto){
				$("#loader").html("Cargando...").fadeIn().delay(500).fadeOut();
			  },
        success: function (data) {
            $(".datos_ajax").html(data).fadeIn('slow');
			$('.checkbox').each(function() {
    const id = $(this).data('id');
    if (localStorage.getItem('checkbox_' + id) === 'checked') {
        this.checked = true;
        this.closest('tr').style.filter = 'brightness(65%) sepia(100%) saturate(200%) hue-rotate(0deg)';
    }
});
          

            // Scroll al checkbox editado
if (lastCheckboxID !== null) {
    setTimeout(function () {
        let el = document.getElementById("STATUS_CHECKBOX" + lastCheckboxID);
        if (el) {
            el.scrollIntoView({ behavior: "smooth", block: "center" });
            lastCheckboxID = null;
        }
    }, 500);
}
        }
    });
}
/* terminaB1*/		
		
	</script>
			
			
			
			
			
			
			
