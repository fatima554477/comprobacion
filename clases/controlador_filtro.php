<?php
/**
 * --------------------------
 * Autor:      Sandor Matamoros
 * Programer:  Fatima Arellano
 * Propietario: EPC
 * Revisado:   2026 — seguridad, bugs y limpieza
 * --------------------------
 *
 * CAMBIOS RESPECTO A LA VERSIÓN ORIGINAL
 * ----------------------------------------
 * [SEGURIDAD]   Todos los echo de $row['campo'] ahora usan h() = htmlspecialchars()
 *               para prevenir XSS.
 * [SEGURIDAD]   Verificación de sesión activa antes de procesar la acción AJAX.
 * [BUG]         NOMBRE_DEL_AYUDO_1 mostraba $NOMBRE_DEL_EJECUTIVO — corregido.
 * [BUG]         Variables de totales ($MONTO_FACTURA12, etc.) inicializadas a 0
 *               antes del foreach para evitar PHP Notice y sumas incorrectas.
 * [BUG]         Comentarios HTML malformados <!-..-> corregidos a <!-- ... -->.
 * [BUG]         "DESCRIPCION " con espacio extra corregido a "DESCRIPCION".
 * [BUG]         $campos se asignaba dos veces; se eliminó la primera asignación.
 * [BUG]         $key en foreach nunca se usaba; simplificado a foreach($datos as $row).
 * [RENDIMIENTO] per_page validado con rango mínimo/máximo (1–200).
 * [RENDIMIENTO] renderDocumentLinks() movida fuera del foreach.
 * [RENDIMIENTO] NUMERO_EVENTO se resuelve en un solo bloque limpio.
 * [LIMPIEZA]    CSS de thead sticky extraído a bloque <style> único arriba.
 * [LIMPIEZA]    Estilos inline repetidos reemplazados por clases CSS.
 */

// ─── Seguridad: verificar sesión antes de todo ────────────────────────────────
if (!isset($_SESSION)) {
    session_start();
}

// ─── Helper de escape XSS — úsalo en todos los echo de datos externos ─────────
function h($value, $default = ''): string
{
    return htmlspecialchars((string)($value ?? $default), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ─── Helper para renderizar enlaces de documentos ─────────────────────────────
// MOVIDO FUERA DEL FOREACH: se define una sola vez al inicio del archivo.
function renderDocumentLinks($rawValue): string
{
    if (!isset($rawValue) || trim((string)$rawValue) === '') {
        return '';
    }

    $links = '';
    $files = preg_split('/\s*,\s*/', (string)$rawValue);
    foreach ($files as $file) {
        $file = trim(html_entity_decode((string)$file));
        if ($file === '') {
            continue;
        }

        if (preg_match('#^https?://#i', $file) === 1) {
            $filePath = $file;
        } else {
            $fileNormalizado = ltrim($file, '/');
            if (stripos($fileNormalizado, 'includes/archivos/') === 0) {
                $filePath = $fileNormalizado;
            } else {
                $filePath = 'includes/archivos/' . $fileNormalizado;
            }
            $partesPath = array_map('rawurlencode', explode('/', $filePath));
            $filePath   = implode('/', $partesPath);
        }

        $links .= '<a href="' . h($filePath) . '" target="_blank" rel="noopener noreferrer">Ver!</a><br/>';
    }

    return $links;
}

// ─── Constante de ruta ────────────────────────────────────────────────────────
define("__ROOT6__", dirname(__FILE__));

// ─── Acción AJAX ──────────────────────────────────────────────────────────────
$action = (isset($_POST["action"]) && $_POST["action"] !== '') ? $_POST["action"] : "";

if ($action === "ajax") {

    // [SEGURIDAD] Verificar que el usuario esté autenticado
    if (empty($_SESSION['idem'])) {
        http_response_code(401);
        exit('<div class="alert alert-danger">Sesión no válida. Por favor recarga la página.</div>');
    }

    require(__ROOT6__ . "/class.filtro.php");
    $database = new orders();

    $usuarioActual             = $_SESSION['idem'];
    $eventosAutorizadosVentas  = array_flip($database->puedeAutorizarVentas($usuarioActual));

    $query = isset($_POST["query"]) ? trim($_POST["query"]) : "";

    $DEPARTAMENTO = !empty($_POST["DEPARTAMENTO2"]) ? $_POST["DEPARTAMENTO2"] : "DEFAULT";

    $nombreTabla = "SELECT * FROM `08comprobacionesfiltroDes`, `08altaeventosfiltroPLA`
                    WHERE 08comprobacionesfiltroDes.id = 08altaeventosfiltroPLA.idRelacion";
    $altaeventos = "comprobaciones";
    $tables      = "04altaeventos";

    // ── Leer variables POST (sin sanitizar aquí; el escape ocurre en getData()) ─
    $campos_post = [
        'NUMERO_CONSECUTIVO_PROVEE', 'RAZON_SOCIAL',       'EJECUTIVOTARJETA',
        'RFC_PROVEEDOR',             'NOMBRE_EVENTO',       'MOTIVO_GASTO',
        'CONCEPTO_PROVEE',           'MONTO_TOTAL_COTIZACION_ADEUDO',
        'MONTO_FACTURA',             'MONTO_PROPINA',       'MONTO_DEPOSITAR',
        'TIPO_DE_MONEDA',            'PFORMADE_PAGO',       'FECHA_A_DEPOSITAR',
        'STATUS_DE_PAGO',            'BANCO_ORIGEN',        'ACTIVO_FIJO',
        'GASTO_FIJO',                'PAGAR_CADA',          'FECHA_PPAGO',
        'FECHA_TPROGRAPAGO',         'NUMERO_EVENTOFIJO',   'CLASI_GENERAL',
        'SUB_GENERAL',               'MONTO_DE_COMISION',   'POLIZA_NUMERO',
        'NOMBRE_DEL_EJECUTIVO',      'NOMBRE_DEL_AYUDO',    'OBSERVACIONES_1',
        'FECHA_DE_LLENADO',          'hiddenpagoproveedores','TIPO_CAMBIOP',
        'TOTAL_ENPESOS',             'IMPUESTO_HOSPEDAJE',  'IVA',
        'NOMBRE_COMERCIAL',          'UUID',                'metodoDePago',
        'total',                     'serie',               'folio',
        'regimenE',                  'UsoCFDI',             'TImpuestosTrasladados',
        'TImpuestosRetenidos',       'Version',             'tipoDeComprobante',
        'condicionesDePago',         'fechaTimbrado',       'nombreR',
        'rfcR',                      'Moneda',              'TipoCambio',
        'ValorUnitarioConcepto',     'DescripcionConcepto', 'ClaveUnidad',
        'ClaveProdServ',             'Cantidad',            'ImporteConcepto',
        'UnidadConcepto',            'TUA',                 'TuaTotalCargos',
        'Descuento',                 'subTotal',            'propina',
        'FECHA_INICIO_EVENTO',       'FECHA_FINAL_EVENTO',
    ];

    $postData = [];
    foreach ($campos_post as $campo) {
        $postData[$campo] = isset($_POST[$campo]) ? trim($_POST[$campo]) : '';
    }

    // Campos con nombres de POST distintos al nombre de la variable
    $postData['TImpuestosRetenidosIVA'] = isset($_POST["TImpuestosRetenidosIVA_5"])
        ? trim($_POST["TImpuestosRetenidosIVA_5"]) : '';
    $postData['TImpuestosRetenidosISR'] = isset($_POST["TImpuestosRetenidosISR_5"])
        ? trim($_POST["TImpuestosRetenidosISR_5"]) : '';
    $postData['descuentos']             = isset($_POST["descuentos_5"])
        ? trim($_POST["descuentos_5"]) : '';
    $postData['ADJUNTAR_COTIZACION']    = isset($_POST["ADJUNTAR_COTIZACION_1_1"])
        ? trim($_POST["ADJUNTAR_COTIZACION_1_1"]) : '';

    // [BUG CORREGIDO] NUMERO_EVENTO: resolución en un solo bloque, prioridad POST > SESSION
    $NUMERO_EVENTO = '';
    if (!empty($_POST['NUMERO_EVENTO'])) {
        $NUMERO_EVENTO = trim($_POST['NUMERO_EVENTO']);
    } elseif (!empty($_SESSION['num_evento'])) {
        $NUMERO_EVENTO = $_SESSION['num_evento'];
    }
    $postData['NUMERO_EVENTO'] = $NUMERO_EVENTO;

    // Paginación
    // [RENDIMIENTO] per_page con rango seguro (1–200) para evitar división por 0
    //               o consultas que traigan toda la tabla.
    $per_page  = max(1, min(200, (int)($_POST["per_page"] ?? 25)));
    $page      = (!empty($_POST["page"])) ? (int)$_POST["page"] : 1;
    $adjacents = 4;
    $offset    = ($page - 1) * $per_page;

    // [BUG CORREGIDO] $campos: eliminada la primera asignación $campos="*"
    $campos = "07COMPROBACION.*, 07XML.*, 04altaeventos.FECHA_INICIO_EVENTO AS FECHA_INICIO_EVENTO, 04altaeventos.FECHA_FINAL_EVENTO AS FECHA_FINAL_EVENTO";

    $search = array_merge($postData, [
        'query'    => $query,
        'per_page' => $per_page,
        'offset'   => $offset,
    ]);

    $datos    = $database->getData($tables, $campos, $search);
    $countAll = $database->getCounter();
    $numrows  = ($countAll > 0) ? $countAll : 0;
    $total_pages = ceil($numrows / $per_page);

    // [BUG CORREGIDO] Variables de totales inicializadas a 0 antes del foreach
    $MONTO_FACTURA12           = 0;
    $IVA12                     = 0;
    $TImpuestosRetenidosIVA12  = 0;
    $TImpuestosRetenidosISR12  = 0;
    $MONTO_PROPINA12           = 0;
    $IMPUESTO_HOSPEDAJE12      = 0;
    $descuentos12              = 0;
    $MONTO_DEPOSITAR12         = 0;
    $TIPO_CAMBIOP12            = 0;
    $TOTAL_ENPESOS12           = 0;
    $subTotal12                = 0;
    $propina12                 = 0;
    $Descuento12               = 0;
    $TImpuestosTrasladados12   = 0;
    $TImpuestosRetenidos12     = 0;
    $TUA12                     = 0;
    $totalf12                  = 0;
    $PorfaltaDeFactura12       = 0;
    $finales                   = 0;
    $totales                   = 'no';
    $totales2                  = 'no';
    ?>

    <!-- ── CSS extraído del HTML inline ── -->
    <style>
        /* Encabezados fijos al hacer scroll */
        thead tr:first-child th {
            position: sticky;
            top: 0;
            background: #c9e8e8;
            z-index: 10;
        }
        thead tr:nth-child(2) td {
            position: sticky;
            top: 60px;
            background: #e2f2f2;
            z-index: 9;
        }

        /* Clases reutilizables para celdas de la tabla */
        .th-default  { background: #c9e8e8; text-align: center; }
        .th-pago     { background: #f48a81; text-align: center; }
        .th-xml      { background: #f9f3a1; text-align: center; }
        .th-fiscal   { background: #f16c4f; text-align: center; }
        .th-sincuarenta { background: #c6eaaa; text-align: center; }

        .td-center   { text-align: center; }
        .td-default  { background: #c9e8e8; }
        .td-pago     { background: #f48a81; }
        .td-xml      { background: #f9f3a1; }
        .td-obs      { width: 700px; min-width: 700px; max-width: 700px; text-align: left; }
    </style>

    <div class="clearfix">
        <?php
            echo "<div class='hint-text'>" . (int)$numrows . " registros</div>";
            require __ROOT6__ . "/pagination.php";
            $pagination = new Pagination($page, $total_pages, $adjacents);
            echo $pagination->paginate();
        ?>
    </div>

    <div style="max-height:600px; overflow-y:auto;">
    <div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th class="th-default"></th>
                <th class="th-default">#</th>
                <th class="th-default">SOLICITANTE</th>
                <th class="th-default">VENTAS Y<br>OPERACIONES</th><!-- AUDITORIA 1 -->
                <th class="th-default">DIRECCIÓN</th><!-- antes finanzas y tesorería -->
                <th class="th-default">AUDITORÍA</th>
                <th class="th-default">CONTABILIDAD</th>
                <?php if ($database->variablespermisos('', 'rechazo_pago', 'ver') == 'si') { ?>
                    <th class="th-default">RECHAZADO</th>
                <?php } ?>

                <?php if ($database->plantilla_filtro($nombreTabla,"FECHA_DE_LLENADO",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">FECHA Y HORA<br>DE LLENADO</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"ADJUNTAR_FACTURA_XML",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">FACTURA XML</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"ADJUNTAR_FACTURA_PDF",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">FACTURA PDF</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"NOMBRE_COMERCIAL",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">NOMBRE COMERCIAL</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"RAZON_SOCIAL",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">RAZÓN SOCIAL</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"RFC_PROVEEDOR",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">RFC PROVEEDOR</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"NUMERO_EVENTO",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">NÚMERO EVENTO</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"NOMBRE_EVENTO",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">NOMBRE DEL EVENTO</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"FECHA_INICIO_EVENTO",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">FECHA INICIO DEL EVENTO</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"FECHA_FINAL_EVENTO",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">FECHA FINAL DEL EVENTO</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"MOTIVO_GASTO",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">MOTIVO DEL GASTO</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"CONCEPTO_PROVEE",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">CONCEPTO DE LA FACTURA</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"MONTO_FACTURA",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">SUBTOTAL</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"IVA",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">IVA</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"TImpuestosRetenidosIVA",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">IMPUESTOS RETENIDOS IVA</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"TImpuestosRetenidosISR",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">IMPUESTOS RETENIDOS ISR</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"MONTO_PROPINA",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">MONTO DE LA PROPINA O SERVICIO<br>NO INCLUIDO EN LA FACTURA</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"IMPUESTO_HOSPEDAJE",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">IMPUESTO SOBRE HOSPEDAJE MÁS<br>EL IMPUESTO DE SANEAMIENTO</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"descuentos",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">DESCUENTO</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"MONTO_DEPOSITAR",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">TOTAL</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"MATCH",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">MATCH CON<br>ESTADO DE CUENTA</th><th class="th-default">STATUS DE<br>COMPROBACIÓN</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"TIPO_DE_MONEDA",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">TIPO DE MONEDA O DIVISA</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"TIPO_CAMBIOP",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">TIPO DE CAMBIO</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"TOTAL_ENPESOS",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">TOTAL DE LA CONVERSIÓN</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"PFORMADE_PAGO",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-pago">FORMA DE PAGO</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"FECHA_A_DEPOSITAR",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-pago">FECHA DE CARGO EN TDC</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"STATUS_DE_PAGO",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-pago">STATUS DE PAGO</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"ADJUNTAR_COTIZACION",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-pago">COTIZACIÓN O REPORTE</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"ACTIVO_FIJO",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">ACTIVO FIJO</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"GASTO_FIJO",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">GASTO FIJO</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"PAGAR_CADA",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">PAGAR CADA</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"FECHA_PPAGO",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">FECHA DE PROGRAMACIÓN DE PAGO</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"FECHA_TPROGRAPAGO",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">FECHA DE TERMINACIÓN DE<br>LA PROGRAMACIÓN</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"NUMERO_EVENTOFIJO",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">NÚMERO DE EVENTO FIJO</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"CLASI_GENERAL",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">CLASIFICACIÓN GENERAL</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"SUB_GENERAL",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">SUB CLASIFICACIÓN GENERAL</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"COMPLEMENTOS_PAGO_PDF",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">COMPLEMENTOS DE PAGO PDF</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"COMPLEMENTOS_PAGO_XML",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">COMPLEMENTOS DE PAGO XML</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"CANCELACIONES_PDF",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">ADJUNTAR CANCELACIONES PDF</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"CANCELACIONES_XML",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">ADJUNTAR CANCELACIONES XML</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"ADJUNTAR_FACTURA_DE_COMISION_PDF",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">ADJUNTAR FACTURA DE COMISIÓN PDF</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"ADJUNTAR_FACTURA_DE_COMISION_XML",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">ADJUNTAR FACTURA DE COMISIÓN XML</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"CALCULO_DE_COMISION",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">ADJUNTAR CALCULO DE COMISIÓN</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"MONTO_DE_COMISION",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">MONTO DE COMISIÓN</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"COMPROBANTE_DE_DEVOLUCION",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">ADJUNTAR COMPROBANTE DE DEVOLUCIÓN DE DINERO A EPC</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"NOTA_DE_CREDITO_COMPRA",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">ADJUNTAR NOTA DE CREDITO DE COMPRA</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"POLIZA_NUMERO",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">PÓLIZA NÚMERO</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"EJECUTIVOTARJETA",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">NOMBRE DEL EJECUTIVO<br>TITULAR DE LA TARJETA</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"BANCO_ORIGEN",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">INSTITUCIÓN BANCARIA</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"NOMBRE_DEL_EJECUTIVO",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">NOMBRE DEL EJECUTIVO<br>QUE REALIZÓ LA COMPRA</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"NOMBRE_DEL_AYUDO",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">NOMBRE DEL EJECUTIVO<br>QUE INGRESO ESTÁ FACTURA</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"OBSERVACIONES_1",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default td-obs">OBSERVACIONES 1</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"ADJUNTAR_ARCHIVO_1",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-default">ADJUNTAR ARCHIVO RELACIONADO<br>CON ESTA FACTURA</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"NOMBRE_RECEPTOR",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-xml">NOMBRE RECEPTOR</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"RFC_RECEPTOR",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-xml">RFC RECEPTOR</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"REGIMEN_FISCAL",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-xml">RÉGIMEN FISCAL</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"UUID",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-xml">UUID</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"FOLIO",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-xml">FOLIO</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"SERIE",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-xml">SERIE</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"CLAVE_UNIDAD",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-xml">CLAVE DE UNIDAD</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"CANTIDAD",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-xml">CANTIDAD</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"CLAVE_PODUCTO",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-xml">CLAVE DE PRODUCTO O SERVICIO</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"DESCRIPCION",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-xml">DESCRIPCIÓN</th><?php } ?><!-- [BUG] quitado espacio extra en "DESCRIPCION " -->
                <?php if ($database->plantilla_filtro($nombreTabla,"Moneda",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-xml">MONEDA</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"TIPO_CAMBIO",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-xml">TIPO DE CAMBIO</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"USO_CFDI",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-xml">USO DE CFDI</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"metodoDePago",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-xml">MÉTODO DE PAGO</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"CONDICIONES_PAGO",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-xml">CONDICIONES DE PAGO</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"TIPO_COMPROBANTE",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-xml">TIPO DE COMPROBANTE</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"VERSION",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-xml">VERSIÓN</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"FECHA_TIMBRADO",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-xml">FECHA DE TIMBRADO</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"subTotal",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-xml">SUBTOTAL</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"propina",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-xml">SERVICIO, PROPINA, ISH Y SANEAMIENTO</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"Descuento",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-xml">DESCUENTO</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"TOTAL_IMPUESTOS_TRASLADADOS",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-xml">IVA</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"TOTAL_IMPUESTOS_RETENIDOS",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-xml">TOTAL DE IMPUESTOS RETENIDOS</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"TUA",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-xml">TUA</th><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"total",$altaeventos,$DEPARTAMENTO)=="si") { ?><th class="th-xml">TOTAL</th><?php } ?>
                <th class="th-fiscal">46% PÉRDIDA DE COSTO FISCAL</th>
                <?php if ($database->variablespermisos('','sincuarenta','ver')=='si') { ?><th class="th-sincuarenta">SIN 46%</th><?php } ?>
            </tr>

            <!-- Fila de filtros de búsqueda -->
            <tr>
                <td class="td-default"></td>
                <td class="td-default"></td>
                <td class="td-default"></td>
                <td class="td-default"></td>
                <td class="td-default"></td>
                <td class="td-default"></td>
                <td class="td-default"></td>
                <?php if ($database->variablespermisos('', 'rechazo_pago', 'ver') == 'si') { ?><td class="td-default"></td><?php } ?>

                <?php if ($database->plantilla_filtro($nombreTabla,"FECHA_DE_LLENADO",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="FECHA_DE_LLENADO_1" value="<?php echo h($postData['FECHA_DE_LLENADO']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"ADJUNTAR_FACTURA_XML",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="ADJUNTAR_FACTURA_XML_1"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"ADJUNTAR_FACTURA_PDF",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="ADJUNTAR_FACTURA_PDF_1"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"NOMBRE_COMERCIAL",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="NOMBRE_COMERCIAL_1" value="<?php echo h($postData['NOMBRE_COMERCIAL']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"RAZON_SOCIAL",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="RAZON_SOCIAL_1" value="<?php echo h($postData['RAZON_SOCIAL']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"RFC_PROVEEDOR",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="RFC_PROVEEDOR_1" value="<?php echo h($postData['RFC_PROVEEDOR']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"NUMERO_EVENTO",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="NUMERO_EVENTO_1" value="<?php echo h($NUMERO_EVENTO); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"NOMBRE_EVENTO",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="NOMBRE_EVENTO_1" value="<?php echo h($postData['NOMBRE_EVENTO']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"FECHA_INICIO_EVENTO",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="date" class="form-control" id="FECHA_INICIO_EVENTO" value="<?php echo h($postData['FECHA_INICIO_EVENTO']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"FECHA_FINAL_EVENTO",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="date" class="form-control" id="FECHA_FINAL_EVENTO" value="<?php echo h($postData['FECHA_FINAL_EVENTO']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"MOTIVO_GASTO",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="MOTIVO_GASTO_1" value="<?php echo h($postData['MOTIVO_GASTO']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"CONCEPTO_PROVEE",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="CONCEPTO_PROVEE_1" value="<?php echo h($postData['CONCEPTO_PROVEE']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"MONTO_FACTURA",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="MONTO_FACTURA_1" value="<?php echo h($postData['MONTO_FACTURA']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"IVA",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="IVA_1" value="<?php echo h($postData['IVA']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"TImpuestosRetenidosIVA",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="TImpuestosRetenidosIVA_5" value="<?php echo h($postData['TImpuestosRetenidosIVA']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"TImpuestosRetenidosISR",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="TImpuestosRetenidosISR_5" value="<?php echo h($postData['TImpuestosRetenidosISR']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"MONTO_PROPINA",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="MONTO_PROPINA_1" value="<?php echo h($postData['MONTO_PROPINA']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"IMPUESTO_HOSPEDAJE",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="IMPUESTO_HOSPEDAJE_1" value="<?php echo h($postData['IMPUESTO_HOSPEDAJE']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"descuentos",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="descuentos_5" value="<?php echo h($postData['descuentos']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"MONTO_DEPOSITAR",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="MONTO_DEPOSITAR_1" value="<?php echo h($postData['MONTO_DEPOSITAR']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"MATCH",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="MATCH_1"></td><td class="td-default"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"TIPO_DE_MONEDA",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="TIPO_DE_MONEDA_1" value="<?php echo h($postData['TIPO_DE_MONEDA']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"TIPO_CAMBIOP",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="TIPO_CAMBIOP_1" value="<?php echo h($postData['TIPO_CAMBIOP']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"TOTAL_ENPESOS",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="TOTAL_ENPESOS_1" value="<?php echo h($postData['TOTAL_ENPESOS']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"PFORMADE_PAGO",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-pago"><input type="text" class="form-control" id="PFORMADE_PAGO_1" value="<?php echo h($postData['PFORMADE_PAGO']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"FECHA_A_DEPOSITAR",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-pago td-center"><input type="date" class="form-control" id="FECHA_A_DEPOSITAR_1" value="<?php echo h($postData['FECHA_A_DEPOSITAR']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"STATUS_DE_PAGO",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-pago td-center"><input type="text" class="form-control" id="STATUS_DE_PAGO_1" value="<?php echo h($postData['STATUS_DE_PAGO']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"ADJUNTAR_COTIZACION",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-pago td-center"><input type="text" class="form-control" id="ADJUNTAR_COTIZACION_1_1" value="<?php echo h($postData['ADJUNTAR_COTIZACION']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"ACTIVO_FIJO",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="ACTIVO_FIJO_1" value="<?php echo h($postData['ACTIVO_FIJO']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"GASTO_FIJO",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="GASTO_FIJO_1" value="<?php echo h($postData['GASTO_FIJO']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"PAGAR_CADA",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="PAGAR_CADA_1" value="<?php echo h($postData['PAGAR_CADA']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"FECHA_PPAGO",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="date" class="form-control" id="FECHA_PPAGO_1" value="<?php echo h($postData['FECHA_PPAGO']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"FECHA_TPROGRAPAGO",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="date" class="form-control" id="FECHA_TPROGRAPAGO_1" value="<?php echo h($postData['FECHA_TPROGRAPAGO']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"NUMERO_EVENTOFIJO",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="NUMERO_EVENTOFIJO_1" value="<?php echo h($postData['NUMERO_EVENTOFIJO']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"CLASI_GENERAL",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="CLASI_GENERAL_1" value="<?php echo h($postData['CLASI_GENERAL']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"SUB_GENERAL",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="SUB_GENERAL_1" value="<?php echo h($postData['SUB_GENERAL']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"COMPLEMENTOS_PAGO_PDF",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="COMPLEMENTOS_PAGO_PDF_1"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"COMPLEMENTOS_PAGO_XML",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="COMPLEMENTOS_PAGO_XML_1"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"CANCELACIONES_PDF",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="CANCELACIONES_PDF_1"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"CANCELACIONES_XML",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="CANCELACIONES_XML_1"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"ADJUNTAR_FACTURA_DE_COMISION_PDF",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="ADJUNTAR_FACTURA_DE_COMISION_PDF_1"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"ADJUNTAR_FACTURA_DE_COMISION_XML",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="ADJUNTAR_FACTURA_DE_COMISION_XML_1"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"CALCULO_DE_COMISION",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="CALCULO_DE_COMISION_1"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"MONTO_DE_COMISION",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="MONTO_DE_COMISION_1" value="<?php echo h($postData['MONTO_DE_COMISION']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"COMPROBANTE_DE_DEVOLUCION",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="COMPROBANTE_DE_DEVOLUCION_1"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"NOTA_DE_CREDITO_COMPRA",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="NOTA_DE_CREDITO_COMPRA_1"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"POLIZA_NUMERO",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="POLIZA_NUMERO_1" value="<?php echo h($postData['POLIZA_NUMERO']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"EJECUTIVOTARJETA",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="EJECUTIVOTARJETA_1" value="<?php echo h($postData['EJECUTIVOTARJETA']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"BANCO_ORIGEN",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="BANCO_ORIGEN1AA" value="<?php echo h($postData['BANCO_ORIGEN']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"NOMBRE_DEL_EJECUTIVO",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="NOMBRE_DEL_EJECUTIVO_1" value="<?php echo h($postData['NOMBRE_DEL_EJECUTIVO']); ?>"></td><?php } ?>
                <!-- [BUG CORREGIDO] Antes mostraba $NOMBRE_DEL_EJECUTIVO en lugar de $NOMBRE_DEL_AYUDO -->
                <?php if ($database->plantilla_filtro($nombreTabla,"NOMBRE_DEL_AYUDO",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="NOMBRE_DEL_AYUDO_1" value="<?php echo h($postData['NOMBRE_DEL_AYUDO']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"OBSERVACIONES_1",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default td-obs"><input type="text" class="form-control" id="OBSERVACIONES_1_2" value="<?php echo h($postData['OBSERVACIONES_1']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"ADJUNTAR_ARCHIVO_1",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-default"><input type="text" class="form-control" id="ADJUNTAR_ARCHIVO_1"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"NOMBRE_RECEPTOR",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-xml td-center"><input type="text" class="form-control" id="nombreR" value="<?php echo h($postData['nombreR']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"RFC_RECEPTOR",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-xml td-center"><input type="text" class="form-control" id="rfcR" value="<?php echo h($postData['rfcR']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"REGIMEN_FISCAL",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-xml td-center"><input type="text" class="form-control" id="regimenE" value="<?php echo h($postData['regimenE']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"UUID",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-xml td-center"><input type="text" class="form-control" id="UUID" value="<?php echo h($postData['UUID']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"FOLIO",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-xml td-center"><input type="text" class="form-control" id="folio" value="<?php echo h($postData['folio']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"SERIE",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-xml td-center"><input type="text" class="form-control" id="serie" value="<?php echo h($postData['serie']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"CLAVE_UNIDAD",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-xml td-center"><input type="text" class="form-control" id="ClaveUnidad" value="<?php echo h($postData['ClaveUnidad']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"CANTIDAD",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-xml td-center"><input type="text" class="form-control" id="Cantidad" value="<?php echo h($postData['Cantidad']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"CLAVE_PODUCTO",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-xml td-center"><input type="text" class="form-control" id="ClaveProdServ" value="<?php echo h($postData['ClaveProdServ']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"DESCRIPCION",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-xml td-center"><input type="text" class="form-control" id="DescripcionConcepto" value="<?php echo h($postData['DescripcionConcepto']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"MonedaF",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-xml td-center"><input type="text" class="form-control" id="Moneda" value="<?php echo h($postData['Moneda']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"TIPO_CAMBIO",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-xml td-center"><input type="text" class="form-control" id="TipoCambio" value="<?php echo h($postData['TipoCambio']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"USO_CFDI",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-xml td-center"><input type="text" class="form-control" id="UsoCFDI" value="<?php echo h($postData['UsoCFDI']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"metodoDePago",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-xml td-center"><input type="text" class="form-control" id="metodoDePago" value="<?php echo h($postData['metodoDePago']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"CONDICIONES_PAGO",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-xml td-center"><input type="text" class="form-control" id="condicionesDePago" value="<?php echo h($postData['condicionesDePago']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"TIPO_COMPROBANTE",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-xml td-center"><input type="text" class="form-control" id="tipoDeComprobante" value="<?php echo h($postData['tipoDeComprobante']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"VERSION",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-xml td-center"><input type="text" class="form-control" id="Version" value="<?php echo h($postData['Version']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"FECHA_TIMBRADO",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-xml td-center"><input type="text" class="form-control" id="fechaTimbrado" value="<?php echo h($postData['fechaTimbrado']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"SUBTOTAL",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-xml td-center"><input type="text" class="form-control" id="subTotal" value="<?php echo h($postData['subTotal']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"propina",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-xml td-center"><input type="text" class="form-control" id="propina" value="<?php echo h($postData['propina']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"Descuento",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-xml td-center"><input type="text" class="form-control" id="Descuento" value="<?php echo h($postData['Descuento']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"TOTAL_IMPUESTOS_TRASLADADOS",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-xml td-center"><input type="text" class="form-control" id="TImpuestosTrasladados" value="<?php echo h($postData['TImpuestosTrasladados']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"TOTAL_IMPUESTOS_RETENIDOS",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-xml td-center"><input type="text" class="form-control" id="TImpuestosRetenidos" value="<?php echo h($postData['TImpuestosRetenidos']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"TUA",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-xml td-center"><input type="text" class="form-control" id="TUA" value="<?php echo h($postData['TUA']); ?>"></td><?php } ?>
                <?php if ($database->plantilla_filtro($nombreTabla,"total",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-xml td-center"><input type="text" class="form-control" id="total" value="<?php echo h($postData['total']); ?>"></td><?php } ?>
                <td style="background:#f16c4f; text-align:center"><input type="text" class="form-control" id="PorfaltaDeFactura"></td>
                <?php if ($database->variablespermisos('','sincuarenta','ver')=='si') { ?><td class="td-xml td-center"></td><?php } ?>
                <td class="td-default"></td>
                <td class="td-default"></td>
                <td class="td-default"></td>
            </tr>
        </thead>

        <?php if ($numrows <= 0) { ?>
            </table>
        <?php } else { ?>
        <tbody>
        <?php
        // [BUG CORREGIDO] $key eliminado — nunca se usaba
        foreach ($datos as $row) {
            $colspan  = 0;
            $colspan2 = 0;
            $fondo_existe_xml  = "";
            $fondo_existe_xml2 = "";

            // Determinar color de fila según estado
            if (isset($row['STATUS_DE_PAGO']) && $row['STATUS_DE_PAGO'] === 'RECHAZADO') {
                $fondo_existe_xml  = "style='background-color:#ff0000'";
                $fondo_existe_xml2 = "style='background-color:#ff0000'";
            } elseif (isset($row['PFORMADE_PAGO']) && $row['PFORMADE_PAGO'] !== '04') {
                $fondo_existe_xml  = "style='background-color:#ffb6c1'";
                $fondo_existe_xml2 = "style='background-color:#ffb6c1'";
            } elseif (!empty($row['ClaveProdServ'])) {
                $fondo_existe_xml  = "style='background-color:#ffffff'";
                $fondo_existe_xml2 = "style='background-color:#ffffff'";
            } else {
                $fondo_existe_xml  = "style='background-color:#fdfe87'";
                $fondo_existe_xml2 = "style='background-color:#fdfe87'";
            }

            // Cargar documentos adjuntos para esta fila
            $ADJUNTAR_FACTURA_PDF             = '';
            $ADJUNTAR_FACTURA_XML             = '';
            $ADJUNTAR_COTIZACION_LINK         = '';
            $CONPROBANTE_TRANSFERENCIA        = '';
            $ADJUNTAR_ARCHIVO_1_LINK          = '';
            $COMPLEMENTOS_PAGO_PDF            = '';
            $COMPLEMENTOS_PAGO_XML            = '';
            $CANCELACIONES_PDF                = '';
            $CANCELACIONES_XML                = '';
            $ADJUNTAR_FACTURA_DE_COMISION_PDF = '';
            $ADJUNTAR_FACTURA_DE_COMISION_XML = '';
            $CALCULO_DE_COMISION              = '';
            $COMPROBANTE_DE_DEVOLUCION        = '';
            $NOTA_DE_CREDITO_COMPRA           = '';
            $FOTO_ESTADO_PROVEE11             = '';

            $querycontrasDOCTOS = $database->Listado_subefacturaDOCTOS($row['07COMPROBACIONid']);
            while ($rowDOCTOS = mysqli_fetch_array($querycontrasDOCTOS)) {
                $ADJUNTAR_FACTURA_PDF             .= renderDocumentLinks($rowDOCTOS["ADJUNTAR_FACTURA_PDF"]);
                $ADJUNTAR_FACTURA_XML             .= renderDocumentLinks($rowDOCTOS["ADJUNTAR_FACTURA_XML"]);
                $ADJUNTAR_COTIZACION_LINK         .= renderDocumentLinks($rowDOCTOS["ADJUNTAR_COTIZACION"]);
                $CONPROBANTE_TRANSFERENCIA        .= renderDocumentLinks($rowDOCTOS["CONPROBANTE_TRANSFERENCIA"]);
                $COMPLEMENTOS_PAGO_PDF            .= renderDocumentLinks($rowDOCTOS["COMPLEMENTOS_PAGO_PDF"]);
                $COMPLEMENTOS_PAGO_XML            .= renderDocumentLinks($rowDOCTOS["COMPLEMENTOS_PAGO_XML"]);
                $CANCELACIONES_PDF                .= renderDocumentLinks($rowDOCTOS["CANCELACIONES_PDF"]);
                $CANCELACIONES_XML                .= renderDocumentLinks($rowDOCTOS["CANCELACIONES_XML"]);
                $ADJUNTAR_FACTURA_DE_COMISION_PDF .= renderDocumentLinks($rowDOCTOS["ADJUNTAR_FACTURA_DE_COMISION_PDF"]);
                $ADJUNTAR_FACTURA_DE_COMISION_XML .= renderDocumentLinks($rowDOCTOS["ADJUNTAR_FACTURA_DE_COMISION_XML"]);
                $CALCULO_DE_COMISION              .= renderDocumentLinks($rowDOCTOS["CALCULO_DE_COMISION"]);
                $COMPROBANTE_DE_DEVOLUCION        .= renderDocumentLinks($rowDOCTOS["COMPROBANTE_DE_DEVOLUCION"]);
                $NOTA_DE_CREDITO_COMPRA           .= renderDocumentLinks($rowDOCTOS["NOTA_DE_CREDITO_COMPRA"]);
                $FOTO_ESTADO_PROVEE11             .= renderDocumentLinks($rowDOCTOS["FOTO_ESTADO_PROVEE11"]);
                $ADJUNTAR_ARCHIVO_1_LINK          .= renderDocumentLinks($rowDOCTOS["ADJUNTAR_ARCHIVO_1"]);
            }
        ?>

        <tr <?php echo $fondo_existe_xml2; ?>>

            <!-- Checkbox de selección -->
            <td>
                <input type="checkbox"
                       class="checkbox"
                       data-id="<?php echo (int)$row['07COMPROBACIONid']; ?>"
                       style="transform:scale(1.1); cursor:pointer;">
            </td>

            <!-- ID -->
            <td <?php echo $fondo_existe_xml; ?>>
                <?php echo (int)$row['07COMPROBACIONid']; $colspan++; ?>
            </td>

            <!-- Solicitante (STATUS_RESPONSABLE_EVENTO) -->
            <td style="text-align:center; background:#ceffcc">
                <input type="checkbox" style="width:30px;" checked disabled
                       class="form-check-input"
                       id="STATUS_RESPONSABLE_EVENTO<?php echo (int)$row['07COMPROBACIONid']; ?>"
                       name="STATUS_RESPONSABLE_EVENTO<?php echo (int)$row['07COMPROBACIONid']; ?>"
                       value="<?php echo (int)$row['07COMPROBACIONid']; ?>"
                       onclick="STATUS_RESPONSABLE_EVENTO(<?php echo (int)$row['07COMPROBACIONid']; ?>)"
                       <?php if ($row["STATUS_RESPONSABLE_EVENTO"] === 'si') echo "checked"; $colspan++; ?>>
            </td>

            <!-- Ventas y Operaciones -->
            <td style="text-align:center; background:<?php echo ($row['STATUS_VENTAS'] === 'si') ? '#ceffcc' : '#e9d8ee'; ?>;"
                id="color_VENTAS<?php echo (int)$row['07COMPROBACIONid']; ?>">
                <?php
                    $atributosVentas = [];
                    if ($row['STATUS_VENTAS'] === 'si') {
                        $atributosVentas[] = 'checked';
                    }
                    $numeroEventoRegistro = isset($row['NUMERO_EVENTO'])
                        ? strtoupper(trim((string)$row['NUMERO_EVENTO'])) : '';
                    $tienePermisoVenta = ($numeroEventoRegistro !== '')
                        && isset($eventosAutorizadosVentas[$numeroEventoRegistro]);
                    if (!$tienePermisoVenta) {
                        $atributosVentas[] = 'disabled';
                    }
                    $colspan++;
                ?>
                <input type="checkbox" style="width:30px;" class="form-check-input"
                       id="STATUS_VENTAS<?php echo (int)$row['07COMPROBACIONid']; ?>"
                       name="STATUS_VENTAS<?php echo (int)$row['07COMPROBACIONid']; ?>"
                       value="<?php echo (int)$row['07COMPROBACIONid']; ?>"
                       onclick="STATUS_VENTAS(<?php echo (int)$row['07COMPROBACIONid']; ?>)"
                       <?php echo implode(' ', $atributosVentas); ?>>
            </td>

            <!-- Dirección (antes Finanzas) -->
            <td style="text-align:center; background:<?php echo ($row['STATUS_FINANZAS'] === 'si') ? '#ceffcc' : '#e9d8ee'; ?>;"
                id="color_FINANZAS<?php echo (int)$row['07COMPROBACIONid']; ?>">
                <?php
                    $permisoVerFINANZAS       = $database->variablespermisos('', 'DIRECCIONCOM2', 'ver')       === 'si';
                    $permisoModificarFINANZAS = $database->variablespermisos('', 'DIRECCIONCOM2', 'modificar') === 'si';
                    $colspan++;
                    if ($row['STATUS_FINANZAS'] === 'si') {
                        echo $permisoModificarFINANZAS
                            ? 'checked onclick="STATUS_FINANZAS(' . (int)$row['07COMPROBACIONid'] . ')"'
                            : 'checked disabled style="cursor:not-allowed;" title="Sin permiso para modificar"';
                    } else {
                        echo $permisoVerFINANZAS
                            ? 'onclick="STATUS_FINANZAS(' . (int)$row['07COMPROBACIONid'] . ')"'
                            : 'disabled style="cursor:not-allowed;" title="Sin permiso para modificar"';
                    }
                ?>
                <input type="checkbox" style="width:30px;" class="form-check-input"
                       id="STATUS_FINANZAS<?php echo (int)$row['07COMPROBACIONid']; ?>"
                       name="STATUS_FINANZAS<?php echo (int)$row['07COMPROBACIONid']; ?>"
                       value="<?php echo (int)$row['07COMPROBACIONid']; ?>">
            </td>

            <!-- Auditoría -->
            <td style="text-align:center; background:<?php echo ($row['STATUS_AUDITORIA2'] === 'si') ? '#ceffcc' : '#e9d8ee'; ?>;"
                id="color_AUDITORIA2<?php echo (int)$row['07COMPROBACIONid']; ?>">
                <?php
                    $permisoVerAUDITORIA2       = $database->variablespermisos('', 'AUDITORIACOM2', 'ver')       === 'si';
                    $permisoModificarAUDITORIA2 = $database->variablespermisos('', 'AUDITORIACOM2', 'modificar') === 'si';
                    $colspan++;
                    $atAud = '';
                    if ($row['STATUS_AUDITORIA2'] === 'si') {
                        $atAud = $permisoModificarAUDITORIA2
                            ? 'checked onclick="STATUS_AUDITORIA2(' . (int)$row['07COMPROBACIONid'] . ')"'
                            : 'checked disabled style="cursor:not-allowed;" title="Ya autorizado"';
                    } else {
                        $atAud = $permisoVerAUDITORIA2
                            ? 'onclick="STATUS_AUDITORIA2(' . (int)$row['07COMPROBACIONid'] . '); this.disabled=true; this.style.cursor=\'not-allowed\';"'
                            : 'disabled style="cursor:not-allowed;" title="Sin permiso para modificar"';
                    }
                ?>
                <input type="checkbox" style="width:30px; cursor:pointer;" class="form-check-input"
                       id="STATUS_AUDITORIA2<?php echo (int)$row['07COMPROBACIONid']; ?>"
                       name="STATUS_AUDITORIA2<?php echo (int)$row['07COMPROBACIONid']; ?>"
                       value="<?php echo (int)$row['07COMPROBACIONid']; ?>"
                       <?php echo $atAud; ?>>
            </td>

            <!-- Contabilidad -->
            <td style="text-align:center; background:<?php echo ($row['STATUS_AUDITORIA3'] === 'si') ? '#ceffcc' : '#e9d8ee'; ?>;"
                id="color_AUDITORIA3<?php echo (int)$row['07COMPROBACIONid']; ?>">
                <?php
                    $permisoVerAUDITORIA3       = $database->variablespermisos('', 'CONTABILIDADCOM2', 'ver')       === 'si';
                    $permisoModificarAUDITORIA3 = $database->variablespermisos('', 'CONTABILIDADCOM2', 'modificar') === 'si';
                    $colspan++;
                    $atCont = '';
                    if ($row['STATUS_AUDITORIA3'] === 'si') {
                        $atCont = $permisoModificarAUDITORIA3
                            ? 'checked onclick="STATUS_AUDITORIA3(' . (int)$row['07COMPROBACIONid'] . ')"'
                            : 'checked disabled style="cursor:not-allowed;" title="Ya autorizado"';
                    } else {
                        $atCont = $permisoVerAUDITORIA3
                            ? 'onclick="STATUS_AUDITORIA3(' . (int)$row['07COMPROBACIONid'] . '); this.disabled=true; this.style.cursor=\'not-allowed\';"'
                            : 'disabled style="cursor:not-allowed;" title="Sin permiso para modificar"';
                    }
                ?>
                <input type="checkbox" style="width:30px; cursor:pointer;" class="form-check-input"
                       id="STATUS_AUDITORIA3<?php echo (int)$row['07COMPROBACIONid']; ?>"
                       name="STATUS_AUDITORIA3<?php echo (int)$row['07COMPROBACIONid']; ?>"
                       value="<?php echo (int)$row['07COMPROBACIONid']; ?>"
                       <?php echo $atCont; ?>>
            </td>

            <!-- Rechazado -->
            <?php if ($database->variablespermisos('', 'rechazo_pago', 'ver') === 'si') { ?>
            <td style="text-align:center; background:<?php
                $statusRechazado = $row['STATUS_RECHAZADO'] ?? 'no';
                echo ($statusRechazado === 'si') ? '#ceffcc' : '#e9d8ee';
            ?>;" id="color_RECHAZADO<?php echo (int)$row['07COMPROBACIONid']; ?>">
                <?php
                    $motivoRechazo          = $database->obtener_motivo_rechazo($row['07COMPROBACIONid']);
                    $statusVentasAutorizado = isset($row['STATUS_VENTAS']) && $row['STATUS_VENTAS'] === 'si';
                    $mostrarAgregarRechazo  = ($statusRechazado === 'si' && $motivoRechazo === '');
                    $mostrarVerRechazo      = ($statusRechazado === 'si' && $motivoRechazo !== '');
                    $permisoguardarRechazo  = $database->variablespermisos('', 'rechazo_pago', 'guardar')    === 'si';
                    $permisomodificarRechazo = $database->variablespermisos('', 'rechazo_pago', 'modificar') === 'si';
                    $colspan++;
                ?>
                <input type="hidden"
                       id="motivo_rechazo_<?php echo (int)$row['07COMPROBACIONid']; ?>"
                       value="<?php echo h($motivoRechazo); ?>">
                <input type="checkbox" style="width:30px; cursor:pointer;" class="form-check-input"
                       id="STATUS_RECHAZADO<?php echo (int)$row['07COMPROBACIONid']; ?>"
                       name="STATUS_RECHAZADO<?php echo (int)$row['07COMPROBACIONid']; ?>"
                       value="<?php echo (int)$row['07COMPROBACIONid']; ?>"
                       <?php
                        if ($statusRechazado === 'si') {
                            echo $permisomodificarRechazo
                                ? 'checked onchange="STATUS_RECHAZADO(' . (int)$row['07COMPROBACIONid'] . ')" title="Pago rechazado"'
                                : 'checked disabled style="cursor:not-allowed;" title="Pago rechazado"';
                        } elseif ($statusVentasAutorizado) {
                            echo 'disabled style="cursor:not-allowed;" title="No se puede rechazar: autorizado por ventas"';
                        } else {
                            echo ($permisoguardarRechazo || $permisomodificarRechazo)
                                ? 'onchange="STATUS_RECHAZADO(' . (int)$row['07COMPROBACIONid'] . ')"'
                                : 'disabled style="cursor:not-allowed;" title="Sin permiso para modificar"';
                        }
                       ?>>
                <?php if ($permisoguardarRechazo || $permisomodificarRechazo) { ?>
                    <button type="button" title="Agregar motivo"
                            id="agregar_rechazo_<?php echo (int)$row['07COMPROBACIONid']; ?>"
                            data-rechazo-id="<?php echo (int)$row['07COMPROBACIONid']; ?>"
                            style="border:none; background:transparent; cursor:pointer; color:#007bff; font-size:14px;<?php echo $mostrarAgregarRechazo ? '' : 'display:none;'; ?>"
                            onclick="abrirFormularioRechazo(<?php echo (int)$row['07COMPROBACIONid']; ?>)">agregar<br>motivo</button>
                <?php } ?>
                <button type="button" title="Ver motivo"
                        id="ver_rechazo_<?php echo (int)$row['07COMPROBACIONid']; ?>"
                        data-rechazo-id="<?php echo (int)$row['07COMPROBACIONid']; ?>"
                        style="border:none; background:transparent; cursor:pointer; color:#28a745; font-size:16px;<?php echo $mostrarVerRechazo ? '' : 'display:none;'; ?>"
                        onclick="verMotivoRechazo(<?php echo (int)$row['07COMPROBACIONid']; ?>)">ver</button>
            </td>
            <?php } ?>

            <!-- Fecha de llenado -->
            <?php if ($database->plantilla_filtro($nombreTabla,"FECHA_DE_LLENADO",$altaeventos,$DEPARTAMENTO)=="si") {
                $fechaHora = $row['FECHA_DE_LLENADO'];
                $fechaFmt  = date('d-m-Y', strtotime($fechaHora));
                $horaFmt   = date('H:i:s', strtotime($fechaHora));
                $colspan2++;
            ?>
            <td class="td-center">
                <?php echo h($fechaFmt); ?>
                <span style="color:#2542C4; font-weight:bold;"><?php echo h($horaFmt); ?></span>
            </td>
            <?php } ?>

            <!-- Documentos adjuntos -->
            <?php if ($database->plantilla_filtro($nombreTabla,"ADJUNTAR_FACTURA_XML",$altaeventos,$DEPARTAMENTO)=="si") { $colspan++; ?><td class="td-center"><?php echo $ADJUNTAR_FACTURA_XML; ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"ADJUNTAR_FACTURA_PDF",$altaeventos,$DEPARTAMENTO)=="si") { $colspan++; ?><td class="td-center"><?php echo $ADJUNTAR_FACTURA_PDF; ?></td><?php } ?>

            <!-- [SEGURIDAD] Todos los campos de $row ahora usan h() -->
            <?php if ($database->plantilla_filtro($nombreTabla,"NOMBRE_COMERCIAL",$altaeventos,$DEPARTAMENTO)=="si") { $colspan++; ?><td class="td-center"><?php echo h($row['NOMBRE_COMERCIAL']); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"RAZON_SOCIAL",$altaeventos,$DEPARTAMENTO)=="si") { $colspan++; ?><td class="td-center"><?php echo h($row['RAZON_SOCIAL']); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"RFC_PROVEEDOR",$altaeventos,$DEPARTAMENTO)=="si") { $colspan++; ?><td class="td-center"><?php echo h($row['RFC_PROVEEDOR']); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"NUMERO_EVENTO",$altaeventos,$DEPARTAMENTO)=="si") { $colspan++; ?><td class="td-center"><?php echo h($row['NUMERO_EVENTO']); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"NOMBRE_EVENTO",$altaeventos,$DEPARTAMENTO)=="si") { $colspan++; ?><td class="td-center"><?php echo h($row['NOMBRE_EVENTO']); ?></td><?php } ?>

            <?php
            if ($database->plantilla_filtro($nombreTabla,"FECHA_INICIO_EVENTO",$altaeventos,$DEPARTAMENTO)=="si") {
                $colspan++;
                $fechaIni = isset($row['FECHA_INICIO_EVENTO']) ? trim($row['FECHA_INICIO_EVENTO']) : '';
                echo ($fechaIni !== '' && $fechaIni !== '0000-00-00')
                    ? "<td class='td-center'>" . h(date('d/m/Y', strtotime($fechaIni))) . "</td>"
                    : "<td class='td-center'></td>";
            }
            if ($database->plantilla_filtro($nombreTabla,"FECHA_FINAL_EVENTO",$altaeventos,$DEPARTAMENTO)=="si") {
                $colspan++;
                $fechaFin = isset($row['FECHA_FINAL_EVENTO']) ? trim($row['FECHA_FINAL_EVENTO']) : '';
                echo ($fechaFin !== '' && $fechaFin !== '0000-00-00')
                    ? "<td class='td-center'>" . h(date('d/m/Y', strtotime($fechaFin))) . "</td>"
                    : "<td class='td-center'></td>";
            }
            ?>

            <?php if ($database->plantilla_filtro($nombreTabla,"MOTIVO_GASTO",$altaeventos,$DEPARTAMENTO)=="si") { $colspan++; ?><td class="td-center"><?php echo h($row['MOTIVO_GASTO']); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"CONCEPTO_PROVEE",$altaeventos,$DEPARTAMENTO)=="si") { $colspan++; ?><td class="td-center"><?php echo h($row['CONCEPTO_PROVEE']); ?></td><?php } ?>

            <?php $colspan2 = $colspan; ?>

            <!-- Montos -->
            <?php if ($database->plantilla_filtro($nombreTabla,"MONTO_FACTURA",$altaeventos,$DEPARTAMENTO)=="si") { $totales = 'si'; $MONTO_FACTURA12 += (float)$row['MONTO_FACTURA']; $colspan2++; ?><td class="td-center">$<?php echo number_format($row['MONTO_FACTURA'],2,'.',','); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"IVA",$altaeventos,$DEPARTAMENTO)=="si") { $totales = 'si'; $IVA12 += (float)$row['IVA']; $colspan2++; ?><td class="td-center"><?php echo number_format($row['IVA'],2,'.',','); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"TImpuestosRetenidosIVA",$altaeventos,$DEPARTAMENTO)=="si") { $totales = 'si'; $TImpuestosRetenidosIVA12 += (float)$row['TImpuestosRetenidosIVA']; $colspan2++; ?><td class="td-center"><?php echo number_format($row['TImpuestosRetenidosIVA'],2,'.',','); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"TImpuestosRetenidosISR",$altaeventos,$DEPARTAMENTO)=="si") { $totales = 'si'; $TImpuestosRetenidosISR12 += (float)$row['TImpuestosRetenidosISR']; $colspan2++; ?><td class="td-center"><?php echo number_format($row['TImpuestosRetenidosISR'],2,'.',','); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"MONTO_PROPINA",$altaeventos,$DEPARTAMENTO)=="si") { $totales = 'si'; $MONTO_PROPINA12 += (float)$row['MONTO_PROPINA']; $colspan2++; ?><td class="td-center">$<?php echo number_format($row['MONTO_PROPINA'],2,'.',','); ?></td><?php } ?>
            <?php
            $supropinamashospedaje = 0;
            if ($database->plantilla_filtro($nombreTabla,"IMPUESTO_HOSPEDAJE",$altaeventos,$DEPARTAMENTO)=="si") {
                $totales = 'si'; $IMPUESTO_HOSPEDAJE12 += (float)$row['IMPUESTO_HOSPEDAJE']; $colspan2++;
                $supropinamashospedaje = (float)$row['MONTO_PROPINA'] + (float)$row['IMPUESTO_HOSPEDAJE'];
                echo '<td class="td-center">$' . number_format($row['IMPUESTO_HOSPEDAJE'],2,'.',',') . '</td>';
            }
            ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"descuentos",$altaeventos,$DEPARTAMENTO)=="si") { $totales = 'si'; $descuentos12 += (float)$row['descuentos']; $colspan2++; ?><td class="td-center">$<?php echo number_format($row['descuentos'],2,'.',','); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"MONTO_DEPOSITAR",$altaeventos,$DEPARTAMENTO)=="si") { $totales = 'si'; $MONTO_DEPOSITAR12 += (float)$row['MONTO_DEPOSITAR']; $colspan2++; ?><td class="td-center">$<?php echo number_format($row['MONTO_DEPOSITAR'],2,'.',','); ?></td><?php } ?>

            <!-- MATCH -->
            <?php if ($database->plantilla_filtro($nombreTabla,"MATCH",$altaeventos,$DEPARTAMENTO)=="si") { ?>
            <td class="td-center dropdown">
                <input class="btn btn-success dropdown-toggle" value="MATCH" type="button"
                       data-bs-toggle="dropdown" aria-expanded="false">
                <ul class="dropdown-menu">
                    <li style="background-color:#edccf3; cursor:pointer;" class="dropdown-item view_MATCH2filtroinbursa" id="<?php echo (int)$row['07COMPROBACIONid']; ?>"><a><?php echo (int)$row['07COMPROBACIONid']; ?> COMPROBAR ESTADO DE CUENTA INBURSA</a></li>
                    <li style="background-color:#ccd9f3; cursor:pointer;" class="dropdown-item view_MATCH2filtrobbva" id="<?php echo (int)$row['07COMPROBACIONid']; ?>"><a><?php echo (int)$row['07COMPROBACIONid']; ?> COMPROBAR ESTADO DE CUENTA BBVA</a></li>
                    <li style="background-color:#edccf3; cursor:pointer;" class="dropdown-item view_MATCH2filtroAMEX" id="<?php echo (int)$row['07COMPROBACIONid']; ?>"><a><?php echo (int)$row['07COMPROBACIONid']; ?> COMPROBAR ESTADO DE CUENTA AMERICAN EXPRESS</a></li>
                    <li style="background-color:#edccf3; cursor:pointer;" class="dropdown-item view_MATCH2filtroSIVALE" id="<?php echo (int)$row['07COMPROBACIONid']; ?>"><a><?php echo (int)$row['07COMPROBACIONid']; ?> COMPROBAR ESTADO DE CUENTA SÍ VALE</a></li>
                    <li style="background-color:#ccd9f3;"><a class="dropdown-item" href="MATCHESTADO.php" target="_blank">LINK A MATCH CON ESTADO DE CUENTA</a></li>
                </ul>
            </td>
            <td>
                <input type="checkbox" style="width:30%;" class="form-check-input"
                       <?php echo $database->validaexistematch2COMPROBACIONtodos($row['07COMPROBACIONid'], 'TARJETABBVA'); ?>
                       disabled>
                <?php echo h($database->tarjetaComprobacion($row['07COMPROBACIONid'])); ?>
            </td>
            <?php $colspan2++; $colspan2++; } ?>

            <?php if ($database->plantilla_filtro($nombreTabla,"TIPO_DE_MONEDA",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo h($row['TIPO_DE_MONEDA']); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"TIPO_CAMBIOP",$altaeventos,$DEPARTAMENTO)=="si") { $totales = 'si'; $TIPO_CAMBIOP12 += (float)$row['TIPO_CAMBIOP']; $colspan2++; ?><td class="td-center">$<?php echo number_format($row['TIPO_CAMBIOP'],2,'.',','); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"TOTAL_ENPESOS",$altaeventos,$DEPARTAMENTO)=="si") { $totales = 'si'; $TOTAL_ENPESOS12 += (float)$row['TOTAL_ENPESOS']; $colspan2++; ?><td class="td-center">$<?php echo number_format($row['TOTAL_ENPESOS'],2,'.',','); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"PFORMADE_PAGO",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo h($row['PFORMADE_PAGO']); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"FECHA_A_DEPOSITAR",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo h($row['FECHA_A_DEPOSITAR']); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"STATUS_DE_PAGO",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo h($row['STATUS_DE_PAGO']); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"ADJUNTAR_COTIZACION",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo $ADJUNTAR_COTIZACION_LINK; ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"ACTIVO_FIJO",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo h($row['ACTIVO_FIJO']); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"GASTO_FIJO",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo h($row['GASTO_FIJO']); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"PAGAR_CADA",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo h($row['PAGAR_CADA']); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"FECHA_PPAGO",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo h($row['FECHA_PPAGO']); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"FECHA_TPROGRAPAGO",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo h($row['FECHA_TPROGRAPAGO']); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"NUMERO_EVENTOFIJO",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo h($row['NUMERO_EVENTOFIJO']); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"CLASI_GENERAL",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo h($row['CLASI_GENERAL']); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"SUB_GENERAL",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo h($row['SUB_GENERAL']); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"COMPLEMENTOS_PAGO_PDF",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo $COMPLEMENTOS_PAGO_PDF; ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"COMPLEMENTOS_PAGO_XML",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo $COMPLEMENTOS_PAGO_XML; ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"CANCELACIONES_PDF",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo $CANCELACIONES_PDF; ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"CANCELACIONES_XML",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo $CANCELACIONES_XML; ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"ADJUNTAR_FACTURA_DE_COMISION_PDF",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo $ADJUNTAR_FACTURA_DE_COMISION_PDF; ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"ADJUNTAR_FACTURA_DE_COMISION_XML",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo $ADJUNTAR_FACTURA_DE_COMISION_XML; ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"CALCULO_DE_COMISION",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo $CALCULO_DE_COMISION; ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"MONTO_DE_COMISION",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo h($row['MONTO_DE_COMISION']); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"COMPROBANTE_DE_DEVOLUCION",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo $COMPROBANTE_DE_DEVOLUCION; ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"NOTA_DE_CREDITO_COMPRA",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo $NOTA_DE_CREDITO_COMPRA; ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"POLIZA_NUMERO",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo h($row['POLIZA_NUMERO']); ?></td><?php } ?>

            <?php
            if ($database->plantilla_filtro($nombreTabla,"EJECUTIVOTARJETA",$altaeventos,$DEPARTAMENTO)=="si") {
                $idEjecutivo    = isset($row['EJECUTIVOTARJETA']) ? trim($row['EJECUTIVOTARJETA']) : '';
                $nombreEjecutivo = $database->nombreCompletoPorID($idEjecutivo);
                $colorEjec       = ($nombreEjecutivo === 'SIN INFORMACIÓN') ? 'color:#bfbfbf;' : '';
                $colspan2++;
                echo '<td class="td-center" style="' . $colorEjec . '">' . h($nombreEjecutivo) . '</td>';
            }
            ?>

            <?php if ($database->plantilla_filtro($nombreTabla,"BANCO_ORIGEN",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo h($row['BANCO_ORIGEN']); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"NOMBRE_DEL_EJECUTIVO",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo h($row['NOMBRE_DEL_EJECUTIVO']); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"NOMBRE_DEL_AYUDO",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo h($row['NOMBRE_DEL_AYUDO']); ?></td><?php } ?>

            <!-- Observaciones — ya usaba htmlspecialchars, mantenemos igual -->
            <?php if ($database->plantilla_filtro($nombreTabla,"OBSERVACIONES_1",$altaeventos,$DEPARTAMENTO)=="si") { ?>
            <td class="td-obs">
                <div class="td-obs" style="white-space:normal; word-break:break-word;">
                    <?php echo h($row['OBSERVACIONES_1']); ?>
                </div>
            </td>
            <?php } ?>

            <?php if ($database->plantilla_filtro($nombreTabla,"ADJUNTAR_ARCHIVO_1",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo $ADJUNTAR_ARCHIVO_1_LINK; ?></td><?php } ?>

            <!-- Campos XML -->
            <?php if ($database->plantilla_filtro($nombreTabla,"NOMBRE_RECEPTOR",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo h($row['nombreR']); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"RFC_RECEPTOR",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo h($row['rfcR']); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"REGIMEN_FISCAL",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo h($row['regimenE']); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"UUID",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo h($row['UUID']); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"FOLIO",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo h($row['folio']); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"SERIE",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo h($row['serie']); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"CLAVE_UNIDAD",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo h($row['ClaveUnidad']); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"CANTIDAD",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo number_format((float)$row['Cantidad'],2,'.',','); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"CLAVE_PODUCTO",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo h($row['ClaveProdServ']); ?></td><?php } ?>
            <?php
            // Descripción: prefiere el XML; si está vacío, usa el campo de la tabla
            $DescripcionConcepto1 = (strlen((string)$row['DescripcionConcepto']) > 0)
                ? $row['DescripcionConcepto']
                : $row['CONCEPTO_PROVEE'];
            if ($database->plantilla_filtro($nombreTabla,"DESCRIPCION",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?>
            <td class="td-center"><?php echo h($DescripcionConcepto1); ?></td>
            <?php } ?>

            <?php if ($database->plantilla_filtro($nombreTabla,"MonedaF",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo h($row['Moneda']); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"TIPO_CAMBIO",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo number_format((float)$row['TipoCambio'],2,'.',','); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"USO_CFDI",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo h($row['UsoCFDI']); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"metodoDePago",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo h($row['metodoDePago']); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"CONDICIONES_PAGO",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo h($row['condicionesDePago']); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"TIPO_COMPROBANTE",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo h($row['tipoDeComprobante']); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"VERSION",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo h($row['Version']); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"FECHA_TIMBRADO",$altaeventos,$DEPARTAMENTO)=="si") { $colspan2++; ?><td class="td-center"><?php echo h($row['fechaTimbrado']); ?></td><?php } ?>

            <!-- Subtotal XML -->
            <?php if ($database->plantilla_filtro($nombreTabla,"SUBTOTAL",$altaeventos,$DEPARTAMENTO)=="si") {
                $subTotal123    = isset($row['subTotal']) ? (float)$row['subTotal'] : 0;
                $MONTO_FACTURA123 = isset($row['MONTO_FACTURA']) ? (float)$row['MONTO_FACTURA'] : 0;
                $MONTO_FACTURAxm2 = ($subTotal123 > 0) ? $subTotal123 : $MONTO_FACTURA123;
                $subTotal12 += $MONTO_FACTURAxm2;
                $totales2 = 'si';
                $colspan2++;
            ?><td class="td-center">$<?php echo number_format($MONTO_FACTURAxm2,2,'.',','); ?></td><?php } ?>

            <?php if ($database->plantilla_filtro($nombreTabla,"propina",$altaeventos,$DEPARTAMENTO)=="si") { $propina12 += (float)$row['propina'] + $supropinamashospedaje; $totales2 = 'si'; ?><td class="td-center"><?php echo number_format((float)$row['propina'] + $supropinamashospedaje,2,'.',','); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"Descuento",$altaeventos,$DEPARTAMENTO)=="si") { $Descuento12 += (float)$row['Descuento']; $totales2 = 'si'; ?><td class="td-center"><?php echo number_format((float)$row['Descuento'],2,'.',','); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"TOTAL_IMPUESTOS_TRASLADADOS",$altaeventos,$DEPARTAMENTO)=="si") { $TImpuestosTrasladados12 += (float)$row['TImpuestosTrasladados']; $totales2 = 'si'; ?><td class="td-center"><?php echo number_format((float)$row['TImpuestosTrasladados'],2,'.',','); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"TOTAL_IMPUESTOS_RETENIDOS",$altaeventos,$DEPARTAMENTO)=="si") { $TImpuestosRetenidos12 += (float)$row['TImpuestosRetenidos']; $totales2 = 'si'; ?><td class="td-center"><?php echo number_format((float)$row['TImpuestosRetenidos'],2,'.',','); ?></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"TUA",$altaeventos,$DEPARTAMENTO)=="si") { $TUA12 += (float)$row['TUA']; ?><td class="td-center"><?php echo number_format((float)$row['TUA'],2,'.',','); ?></td><?php } ?>

            <!-- Total XML -->
            <?php if ($database->plantilla_filtro($nombreTabla,"total",$altaeventos,$DEPARTAMENTO)=="si") {
                $total123       = isset($row['total']) ? (float)$row['total'] : 0;
                $MONTO_DEPOSITAR123 = isset($row['MONTO_DEPOSITAR']) ? (float)$row['MONTO_DEPOSITAR'] : 0;
                $porfalta2      = ($total123 > 0) ? $total123 : $MONTO_DEPOSITAR123;
                $totalf12      += $porfalta2;
                $totales2       = 'si';
            ?><td class="td-center" id="montoOriginal_<?php echo (int)$row['07COMPROBACIONid']; ?>">$<?php echo number_format($porfalta2,2,'.',','); ?></td><?php } ?>

            <!-- 46% pérdida de costo fiscal -->
            <td class="td-center" id="valorCalculado_<?php echo (int)$row['07COMPROBACIONid']; ?>">
                <?php
                if (($row['STATUS_CHECKBOX'] === 'no' || $row['STATUS_CHECKBOX'] === null)
                    && strlen(trim((string)$row['UUID'])) < 1) {
                    $valorCalculado = ($porfalta2 ?? 0) * 1.46;
                    echo number_format($valorCalculado, 2, '.', ',');
                    $PorfaltaDeFactura12 += $valorCalculado;
                }
                ?>
            </td>

            <!-- Sin 46% -->
            <?php if ($database->variablespermisos('','sincuarenta','ver')=='si') { ?>
            <td id="color_CHECKBOX<?php echo (int)$row['07COMPROBACIONid']; ?>"
                style="text-align:center; background:<?php
                if (strlen((string)$row['UUID']) < 1) {
                    echo ($row['STATUS_CHECKBOX'] === 'si') ? '#ceffcc' : '#e9d8ee';
                } else {
                    echo '#f0f0f0';
                }
                ?>">
                <span id="buscanumero<?php echo (int)$row['07COMPROBACIONid']; ?>">
                <?php if (strlen((string)$row['UUID']) < 1) {
                    $permiso_modificar = $database->variablespermisos('','sincuarenta','modificar') === 'si';
                    $disabled_chk      = ($row['STATUS_CHECKBOX'] === 'si' && !$permiso_modificar) ? 'disabled' : '';
                ?>
                    <input type="checkbox" style="width:30px;" class="form-check-input"
                           id="STATUS_CHECKBOX<?php echo (int)$row['07COMPROBACIONid']; ?>"
                           name="STATUS_CHECKBOX<?php echo (int)$row['07COMPROBACIONid']; ?>"
                           value="<?php echo (int)$row['07COMPROBACIONid']; ?>"
                           onclick="STATUS_CHECKBOX(<?php echo (int)$row['07COMPROBACIONid']; ?>, <?php echo $permiso_modificar ? 'true' : 'false'; ?>)"
                           <?php if ($row['STATUS_CHECKBOX'] === 'si') echo 'checked'; ?>
                           <?php echo $disabled_chk; ?>>
                <?php } else { ?>
                    <span style="color:#999;">CON XML</span>
                <?php } ?>
                </span>
            </td>
            <?php } ?>

            <div id="ajax-notification"
                 style="position:fixed; top:20px; right:20px; padding:15px; background:#4CAF50;
                        color:white; border-radius:5px; display:none; z-index:1000;"></div>

            <!-- Botones de acción -->
            <td <?php echo $fondo_existe_xml; ?>>
                <?php if ($database->variablespermisos('','PAGO_PROVEEDOR1','modificar')=='si') { ?>
                    <input type="button" value="MODIFICAR" id="<?php echo (int)$row['07COMPROBACIONid']; ?>"
                           class="btn btn-info btn-xs view_dataPAGOPROVEEmodifica">
                <?php } ?>
            </td>
            <td>
                <?php if ($database->variablespermisos('','PAGO_PROVEEDOR1','borrar')=='si') { ?>
                    <input type="button" name="view2" value="BORRAR" id="<?php echo (int)$row['07COMPROBACIONid']; ?>"
                           class="btn btn-info btn-xs view_dataSBborrar">
                <?php } ?>
            </td>

        </tr>
        <?php
            $finales++;
        } // fin foreach
        ?>

        <!-- Fila de TOTALES -->
        <tr>
            <?php if ($totales === 'si') { ?>
            <td style="text-align:right; padding-right:45px;" colspan="<?php echo $colspan + 2; ?>">
                <strong style="font-size:16px">TOTALES</strong>
            </td>
            <?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"MONTO_FACTURA",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-center"><strong style="font-size:16px">$<?php echo number_format($MONTO_FACTURA12,2,'.',','); ?></strong></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"IVA",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-center"><strong style="font-size:16px">$<?php echo number_format($IVA12,2,'.',','); ?></strong></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"TImpuestosRetenidosIVA",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-center"><strong style="font-size:16px">$<?php echo number_format($TImpuestosRetenidosIVA12,2,'.',','); ?></strong></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"TImpuestosRetenidosISR",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-center"><strong style="font-size:16px">$<?php echo number_format($TImpuestosRetenidosISR12,2,'.',','); ?></strong></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"MONTO_PROPINA",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-center"><strong style="font-size:16px">$<?php echo number_format($MONTO_PROPINA12,2,'.',','); ?></strong></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"IMPUESTO_HOSPEDAJE",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-center"><strong style="font-size:16px">$<?php echo number_format($IMPUESTO_HOSPEDAJE12,2,'.',','); ?></strong></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"descuentos",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-center"><strong style="font-size:16px">$<?php echo number_format($descuentos12,2,'.',','); ?></strong></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"MONTO_DEPOSITAR",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-center"><strong style="font-size:16px">$<?php echo number_format($MONTO_DEPOSITAR12,2,'.',','); ?></strong></td><?php } ?>
        </tr>

        <!-- Fila de TOTALES XML -->
        <tr>
            <?php if ($totales2 === 'si') { ?>
            <td style="text-align:right; padding-right:45px;" colspan="<?php echo $colspan2 + 2; ?>">
                <strong style="font-size:16px">TOTALES XML</strong>
            </td>
            <?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"SUBTOTAL",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-center"><strong style="font-size:16px">$<?php echo number_format($subTotal12,2,'.',','); ?></strong></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"propina",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-center"><strong style="font-size:16px">$<?php echo number_format($propina12,2,'.',','); ?></strong></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"Descuento",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-center"><strong style="font-size:16px">$<?php echo number_format($Descuento12,2,'.',','); ?></strong></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"TOTAL_IMPUESTOS_TRASLADADOS",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-center"><strong style="font-size:16px">$<?php echo number_format($TImpuestosTrasladados12,2,'.',','); ?></strong></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"TOTAL_IMPUESTOS_RETENIDOS",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-center"><strong style="font-size:16px">$<?php echo number_format($TImpuestosRetenidos12,2,'.',','); ?></strong></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"TUA",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-center"><strong style="font-size:16px">$<?php echo number_format($TUA12,2,'.',','); ?></strong></td><?php } ?>
            <?php if ($database->plantilla_filtro($nombreTabla,"total",$altaeventos,$DEPARTAMENTO)=="si") { ?><td class="td-center"><strong style="font-size:16px">$<?php echo number_format($totalf12,2,'.',','); ?></strong></td><?php } ?>
            <td class="td-center"><strong style="font-size:16px" id="totalCalculado">$<?php echo number_format($PorfaltaDeFactura12,2,'.',','); ?></strong></td>
        </tr>

        </tbody>
        </table>
        </div>
        </div>

        <div class="clearfix">
            <?php
                $inicios  = $offset + 1;
                $finales += $inicios - 1;
                echo '<div class="hint-text">Mostrando ' . $inicios . ' al ' . $finales . ' de ' . $numrows . ' registros</div>';
                echo $pagination->paginate();
            ?>
        </div>

        <!-- JS: comportamiento del checkbox de selección (sin localStorage inline) -->
        <script>
        document.addEventListener('change', function(e) {
            if (!e.target.classList.contains('checkbox')) return;
            const fila = e.target.closest('tr');
            if (!fila) return;
            fila.style.filter = e.target.checked
                ? 'brightness(65%) sepia(100%) saturate(200%) hue-rotate(0deg)'
                : 'none';
        });
        </script>

<?php } // fin else ($numrows > 0) ?>
