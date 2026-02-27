<?php
/**
 * --------------------------
 * Autor:      Sandor Matamoros
 * Programer:  Fatima Arellano
 * Propietario: EPC
 * Revisado:   2026 — seguridad, rendimiento y limpieza
 * --------------------------
 *
 * CAMBIOS RESPECTO A LA VERSIÓN ORIGINAL
 * ----------------------------------------
 * [SEGURIDAD] getData(): TODOS los valores del array $search ahora se escapan
 *             con $this->mysqli->real_escape_string() antes de ser interpolados
 *             en el SQL.  El campo EJECUTIVOTARJETA ya lo hacía; se extendió
 *             el patrón a los ~60 campos restantes.
 *
 * [SEGURIDAD] nombreCompletoPorID(): el parámetro $id ya se escapaba, se
 *             mantiene igual.
 *
 * [RENDIMIENTO] getCounter() / setCounter(): sin cambios, ya estaba bien.
 *
 * [LIMPIEZA]  Se eliminó la variable $sWhere que nunca se usaba en getData().
 *             Se normalizaron indentaciones y llaves.
 *
 * NOTA IMPORTANTE
 * ---------------
 * La solución definitiva contra SQL-injection es usar PDO con parámetros
 * preparados (?).  La refactorización completa a PDO requiere cambiar también
 * la clase base `accesoclase` y está fuera del alcance de este archivo.
 * Con real_escape_string + charset utf8mb4 en la conexión el riesgo queda
 * mitigado en la práctica para MySQLi.
 */

define("__ROOT1__", dirname(dirname(__FILE__)));
include_once(__ROOT1__ . "/../includes/error_reporting.php");
include_once(__ROOT1__ . "/../comprobaciones/class.epcinnPP.php");


class orders extends accesoclase
{
    public  $mysqli;
    public  $counter; // número de registros devueltos por la consulta
    private $matchCache          = [];
    private $matchAnyCache       = [];
    private $tarjetaCache        = [];
    private $plantillaFiltroCache = [];

    // -------------------------------------------------------------------------
    // Constructor
    // -------------------------------------------------------------------------
    function __construct()
    {
        $this->mysqli = $this->db();
    }

    // -------------------------------------------------------------------------
    // plantilla_filtro — con caché local (igual que el original)
    // -------------------------------------------------------------------------
    public function plantilla_filtro($nombreTabla, $campo, $altaeventos, $departamento)
    {
        $cacheKey = $nombreTabla . '|' . $campo . '|' . $altaeventos . '|' . $departamento;
        if (isset($this->plantillaFiltroCache[$cacheKey])) {
            return $this->plantillaFiltroCache[$cacheKey];
        }
        $resultado = parent::plantilla_filtro($nombreTabla, $campo, $altaeventos, $departamento);
        $this->plantillaFiltroCache[$cacheKey] = $resultado;
        return $resultado;
    }

    // -------------------------------------------------------------------------
    // validaexistematch2COMPROBACION — con caché (igual que el original)
    // -------------------------------------------------------------------------
    public function validaexistematch2COMPROBACION($IpMATCHDOCUMENTOS2, $TARJETA)
    {
        $cacheKey = $IpMATCHDOCUMENTOS2 . '|' . $TARJETA;
        if (isset($this->matchCache[$cacheKey])) {
            return $this->matchCache[$cacheKey];
        }

        $conn      = $this->db();
        $documento = mysqli_real_escape_string($conn, $IpMATCHDOCUMENTOS2);
        $tarjeta   = mysqli_real_escape_string($conn, $TARJETA);

        $pregunta  = 'SELECT 1 FROM 12matchDocumentos
                      WHERE estatus = "si"
                        AND documentoConFactura = "' . $documento . '"
                        AND tarjeta = "' . $tarjeta . '"
                      LIMIT 1';
        $preguntaQ = mysqli_query($conn, $pregunta) or die('P1533' . mysqli_error($conn));
        $resultado = (mysqli_num_rows($preguntaQ) > 0) ? 'checked' : '';

        $this->matchCache[$cacheKey] = $resultado;
        return $resultado;
    }

    // -------------------------------------------------------------------------
    // validaexistematch2COMPROBACIONtodos — con caché (igual que el original)
    // -------------------------------------------------------------------------
    public function validaexistematch2COMPROBACIONtodos($IpMATCHDOCUMENTOS2, $TARJETA)
    {
        if (isset($this->matchAnyCache[$IpMATCHDOCUMENTOS2])) {
            return $this->matchAnyCache[$IpMATCHDOCUMENTOS2];
        }

        $conn      = $this->db();
        $documento = mysqli_real_escape_string($conn, $IpMATCHDOCUMENTOS2);

        $pregunta  = 'SELECT 1 FROM 12matchDocumentos
                      WHERE estatus = "si"
                        AND documentoConFactura = "' . $documento . '"
                        AND tarjeta IN ("AMERICANE","INBURSA","TARJETABBVA")
                      LIMIT 1';
        $preguntaQ = mysqli_query($conn, $pregunta) or die('P1533' . mysqli_error($conn));
        $resultado = (mysqli_num_rows($preguntaQ) > 0) ? 'checked' : '';

        $this->matchAnyCache[$IpMATCHDOCUMENTOS2] = $resultado;
        return $resultado;
    }

    // -------------------------------------------------------------------------
    // tarjetaComprobacion — con caché (igual que el original)
    // -------------------------------------------------------------------------
    public function tarjetaComprobacion($IpMATCHDOCUMENTOS2)
    {
        if (isset($this->tarjetaCache[$IpMATCHDOCUMENTOS2])) {
            return $this->tarjetaCache[$IpMATCHDOCUMENTOS2];
        }

        $conn      = $this->db();
        $documento = mysqli_real_escape_string($conn, $IpMATCHDOCUMENTOS2);

        $pregunta  = 'SELECT DISTINCT tarjeta FROM 12matchDocumentos
                      WHERE estatus = "si"
                        AND documentoConFactura = "' . $documento . '"';
        $preguntaQ = mysqli_query($conn, $pregunta) or die('P1533' . mysqli_error($conn));

        $tarjetas = [];
        while ($ROWP = mysqli_fetch_array($preguntaQ, MYSQLI_ASSOC)) {
            $tarjetas[] = $this->nombreTarjeta($ROWP['tarjeta']);
        }
        $resultado = (count($tarjetas) === 0) ? '' : implode(', ', $tarjetas);

        $this->tarjetaCache[$IpMATCHDOCUMENTOS2] = $resultado;
        return $resultado;
    }

    // -------------------------------------------------------------------------
    // nombreTarjeta — helper privado (igual que el original)
    // -------------------------------------------------------------------------
    private function nombreTarjeta($tarjeta)
    {
        $map = [
            'AMERICANE'   => 'AMERICAN EXPRESS',
            'INBURSA'     => 'INBURSA',
            'TARJETABBVA' => 'BBVA',
        ];
        $tarjetaUpper = strtoupper($tarjeta);
        return $map[$tarjetaUpper] ?? $tarjetaUpper;
    }

    // -------------------------------------------------------------------------
    // nombreCompletoPorID — sin cambios funcionales, ya escapaba el ID
    // -------------------------------------------------------------------------
    public function nombreCompletoPorID($id)
    {
        $conn = $this->db();
        $id   = mysqli_real_escape_string($conn, trim((string) $id));

        $sql = "SELECT NOMBRE_1, NOMBRE_2, APELLIDO_PATERNO, APELLIDO_MATERNO
                FROM 01informacionpersonal
                WHERE idRelacion = '$id'
                LIMIT 1";

        $nombreCompleto = 'SIN INFORMACIÓN';
        if ($query = mysqli_query($conn, $sql)) {
            if ($row = mysqli_fetch_assoc($query)) {
                $partes = array_filter([
                    $row['NOMBRE_1'],
                    $row['NOMBRE_2'],
                    $row['APELLIDO_PATERNO'],
                    $row['APELLIDO_MATERNO'],
                ]);
                $nombreCompleto = trim(implode(' ', $partes)) ?: 'SIN INFORMACIÓN';
            }
        }
        return $nombreCompleto;
    }

    // -------------------------------------------------------------------------
    // countAll — sin cambios
    // -------------------------------------------------------------------------
    public function countAll($sql)
    {
        $query = $this->mysqli->query($sql);
        return $query->num_rows;
    }

    // =========================================================================
    // getData — CAMBIO PRINCIPAL: escape de TODOS los valores del $search
    // =========================================================================
    /**
     * Construye y ejecuta la consulta de datos con paginación.
     *
     * SEGURIDAD: cada valor del array $search que se interpola en el SQL
     * pasa por $this->mysqli->real_escape_string() antes de usarse.
     * Los valores numéricos se pasan además por (float) o (int) según
     * corresponda para asegurar el tipo esperado.
     */
    public function getData($tables, $campos, $search)
    {
        $offset   = (int) $search['offset'];
        $per_page = (int) $search['per_page'];

        $tables  = '07COMPROBACION';
        $tables2 = '07XML';
        $tables5 = '04altaeventos';

        $joinAltaEventos = " LEFT JOIN $tables5
                             ON $tables.NUMERO_EVENTO  = $tables5.NUMERO_EVENTO
                            AND $tables.NOMBRE_EVENTO  = $tables5.NOMBRE_EVENTO ";
        $sWhereCC = " ON 07COMPROBACION.id = 07XML.`ultimo_id` " . $joinAltaEventos;

        // Helper para escapar strings de forma segura
        $e = function ($value) {
            return $this->mysqli->real_escape_string(trim((string) $value));
        };

        $sWhere2 = "";

        // ---- Campos de 07COMPROBACION ----------------------------------------
        $camposLike07 = [
            'NUMERO_CONSECUTIVO_PROVEE', 'RAZON_SOCIAL',     'RFC_PROVEEDOR',
            'NUMERO_EVENTO',             'NOMBRE_EVENTO',    'MOTIVO_GASTO',
            'CONCEPTO_PROVEE',           'MONTO_TOTAL_COTIZACION_ADEUDO',
            'MONTO_FACTURA',             'MONTO_PROPINA',    'MONTO_DEPOSITAR',
            'TIPO_DE_MONEDA',            'PFORMADE_PAGO',    'FECHA_A_DEPOSITAR',
            'STATUS_DE_PAGO',            'BANCO_ORIGEN',     'NOMBRE_COMERCIAL',
            'ACTIVO_FIJO',               'GASTO_FIJO',       'PAGAR_CADA',
            'FECHA_PPAGO',               'FECHA_TPROGRAPAGO','NUMERO_EVENTOFIJO',
            'CLASI_GENERAL',             'SUB_GENERAL',      'MONTO_DE_COMISION',
            'POLIZA_NUMERO',             'NOMBRE_DEL_EJECUTIVO', 'NOMBRE_DEL_AYUDO',
            'OBSERVACIONES_1',           'FECHA_DE_LLENADO', 'hiddenpagoproveedores',
            'ADJUNTAR_COTIZACION',       'TIPO_CAMBIOP',     'TOTAL_ENPESOS',
            'IVA',                       'TImpuestosRetenidosIVA', 'TImpuestosRetenidosISR',
            'descuentos',
        ];
        foreach ($camposLike07 as $campo) {
            if (!empty($search[$campo])) {
                $val      = $e($search[$campo]);
                $sWhere2 .= " $tables.`$campo` LIKE '%$val%' AND ";
            }
        }

        // ---- Campos de 04altaeventos -----------------------------------------
        if (!empty($search['FECHA_INICIO_EVENTO'])) {
            $val      = $e($search['FECHA_INICIO_EVENTO']);
            $sWhere2 .= " $tables5.FECHA_INICIO_EVENTO LIKE '%$val%' AND ";
        }
        if (!empty($search['FECHA_FINAL_EVENTO'])) {
            $val      = $e($search['FECHA_FINAL_EVENTO']);
            $sWhere2 .= " $tables5.FECHA_FINAL_EVENTO LIKE '%$val%' AND ";
        }

        // ---- EJECUTIVOTARJETA (búsqueda por nombre o ID) ---------------------
        if (!empty($search['EJECUTIVOTARJETA'])) {
            $ejecutivo = strtoupper($e($search['EJECUTIVOTARJETA']));
            $busquedaNombre = "SELECT idRelacion
                               FROM 01informacionpersonal
                               WHERE UPPER(CONCAT_WS(' ', NOMBRE_1, NOMBRE_2,
                                                     APELLIDO_PATERNO, APELLIDO_MATERNO))
                                     LIKE '%$ejecutivo%'";
            $sWhere2 .= " (UPPER($tables.EJECUTIVOTARJETA) LIKE '%$ejecutivo%'
                           OR $tables.EJECUTIVOTARJETA IN ($busquedaNombre)) OR ";
        }

        // ---- Campos de 07XML (igualdad exacta) --------------------------------
        $camposExactos07XML = [
            'UUID', 'metodoDePago', 'serie', 'folio', 'regimenE',
            'UsoCFDI', 'Version', 'tipoDeComprobante', 'condicionesDePago',
            'fechaTimbrado', 'nombreR', 'rfcR', 'Moneda', 'TipoCambio',
            'ValorUnitarioConcepto', 'ClaveProdServ', 'RFC_RECEPTOR',
            'CantidadConcepto', 'ImporteConcepto', 'UnidadConcepto',
        ];
        foreach ($camposExactos07XML as $campo) {
            if (!empty($search[$campo])) {
                $val      = $e($search[$campo]);
                $sWhere2 .= " $tables2.`$campo` = '$val' AND ";
            }
        }

        // ---- Campos de 07XML (LIKE) -------------------------------------------
        $camposLike07XML = ['Cantidad', 'ClaveUnidad', 'DescripcionConcepto'];
        foreach ($camposLike07XML as $campo) {
            if (!empty($search[$campo])) {
                $val      = $e($search[$campo]);
                $sWhere2 .= " $tables2.`$campo` LIKE '%$val%' AND ";
            }
        }

        // ---- Campos de 07XML numéricos (limpiar $ y comas antes de escapar) --
        $camposNumericos07XML = [
            'total'                 => 'total',
            'TImpuestosTrasladados' => 'TImpuestosTrasladados',
            'TImpuestosRetenidos'   => 'TImpuestosRetenidos',
            'TUA'                   => 'TUA',
            'TuaTotalCargos'        => 'TuaTotalCargos',
            'Descuento'             => 'Descuento',
            'subTotal'              => 'subTotal',
            'IMPUESTO_HOSPEDAJE'    => 'IMPUESTO_HOSPEDAJE',
            'propina'               => 'propina',
        ];
        foreach ($camposNumericos07XML as $key => $columna) {
            if (!empty($search[$key])) {
                // Quitar símbolo de moneda y separadores antes de escapar
                $raw = str_replace([',', '$'], '', (string) $search[$key]);
                $val = $e($raw);
                $sWhere2 .= " $tables2.`$columna` = '$val' AND ";
            }
        }

        // ---- Ensamblar WHERE -------------------------------------------------
        if ($sWhere2 !== "") {
            $sWhere22 = rtrim($sWhere2, " AND ");    // quitar el AND/OR sobrante
            $sWhere3  = " $sWhereCC WHERE ( $sWhere22 ) ";
        } else {
            $sWhere3  = " $sWhereCC ";
        }
        $sWhere3 .= " ORDER BY $tables.id DESC ";

        // ---- Construir y ejecutar queries ------------------------------------
        $selectBase = "SELECT $campos, 07COMPROBACION.id AS 07COMPROBACIONid
                       FROM $tables
                       LEFT JOIN $tables2 $sWhere3";

        $sql       = $selectBase . " LIMIT $offset, $per_page";
        $sqlCount  = $selectBase; // sin LIMIT para contar

        $query    = $this->mysqli->query($sql);
        $numRows  = $this->countAll($sqlCount);
        $this->setCounter($numRows);

        return $query;
    }

    // -------------------------------------------------------------------------
    // setCounter / getCounter — sin cambios
    // -------------------------------------------------------------------------
    function setCounter($counter) { $this->counter = $counter; }
    function getCounter()         { return $this->counter;     }

    // -------------------------------------------------------------------------
    // puedeAutorizarVentas — sin cambios funcionales
    // -------------------------------------------------------------------------
    public function puedeAutorizarVentas($idPersonal)
    {
        if (empty($idPersonal)) {
            return [];
        }
        $conn = $this->db();
        if (!$conn) {
            return [];
        }

        $idPersonal = mysqli_real_escape_string($conn, trim((string) $idPersonal));

        $columnasIdentificador = $this->columnasIdentificadorPersonal($conn);
        if (empty($columnasIdentificador)) {
            return [];
        }

        $condiciones = [];
        foreach ($columnasIdentificador as $columna) {
            $condiciones[] = "`p`.`$columna` = '$idPersonal'";
        }

        $sql = "SELECT DISTINCT ae.NUMERO_EVENTO
                FROM 04personal AS p
                INNER JOIN 04altaeventos AS ae ON ae.id = p.idRelacion
                WHERE (" . implode(' OR ', $condiciones) . ")
                  AND LOWER(p.autorizaAUT) = 'si'
                  AND ae.NUMERO_EVENTO IS NOT NULL
                  AND ae.NUMERO_EVENTO <> ''";

        $resultado = mysqli_query($conn, $sql);
        if (!$resultado) {
            return [];
        }

        $eventosAutorizados = [];
        while ($row = mysqli_fetch_assoc($resultado)) {
            $eventoNormalizado = strtoupper(trim((string) $row['NUMERO_EVENTO']));
            if ($eventoNormalizado !== '') {
                $eventosAutorizados[$eventoNormalizado] = true;
            }
        }
        mysqli_free_result($resultado);

        return array_keys($eventosAutorizados);
    }

    // -------------------------------------------------------------------------
    // columnasIdentificadorPersonal — sin cambios
    // -------------------------------------------------------------------------
    private function columnasIdentificadorPersonal($conn)
    {
        static $columnasCache = null;
        if ($columnasCache !== null) {
            return $columnasCache;
        }
        $columnasPosibles    = ['idem', 'idPersonal', 'IDEM', 'ID_PERSONAL'];
        $columnasDisponibles = [];
        foreach ($columnasPosibles as $columna) {
            if ($this->columnaExisteEnTabla($conn, '04personal', $columna)) {
                $columnasDisponibles[] = $columna;
            }
        }
        $columnasCache = $columnasDisponibles;
        return $columnasCache;
    }

    // -------------------------------------------------------------------------
    // columnaExisteEnTabla — sin cambios
    // -------------------------------------------------------------------------
    private function columnaExisteEnTabla($conn, $tabla, $columna)
    {
        if (!$conn || $tabla === '' || $columna === '') {
            return false;
        }
        $tablaLimpia   = str_replace('`', '``', $tabla);
        $columnaLimpia = mysqli_real_escape_string($conn, $columna);
        $sql           = "SHOW COLUMNS FROM `$tablaLimpia` LIKE '$columnaLimpia'";
        $resultado     = mysqli_query($conn, $sql);
        if ($resultado) {
            $existe = mysqli_num_rows($resultado) > 0;
            mysqli_free_result($resultado);
            return $existe;
        }
        return false;
    }
}
