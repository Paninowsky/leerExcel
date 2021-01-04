<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Import_excel extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->helper(array('url'));
		$this->load->library(['excel']);
		$this->load->database();

	}
	public function index()
	{		
		$this->load->view('import_excel',array('error'=>'','success' =>''));
	}

	public function import(){
		/*--- Configuraciones de la subida --- */
		
		$config['upload_path'] = "./uploads/";
		$config['allowed_types'] = 'xls|xlsx|x-dbf';
		$config['max_size'] = '200000'; //Preguntar el tam maximo del archivo
		$this->load->library('upload', $config);
		if(!$this->upload->do_upload()){
			$messages = array('error' => $this->upload->display_errors(),'success'=>'');
			$this->load->view('import_excel', $messages);
		}else{
			/*--- El archivo se subio correctamente --- */
			$file_data = $this->upload->data();
			$path = './uploads/'.$file_data['file_name'];	
			/*----------Me fijo si es DBF o Excel----------*/	
			$resultado = exec("file -i ".$path);	
			if (strpos($resultado, 'x-dbf') !== false) {
				$this->importDBF($path);
			}else{
				$this->importExcel($path);
			}
			unlink($path);
		}
	}



	function importDBF($path) {
		$database = dbase_open($path,0);
		//var_dump(dbase_get_record($database ,6512));
		$rowNumber;
		$datosCorrectos = true;
		$this->db->trans_begin(); /*--- Comienzo la transaccion --- */
		for ($i = 1; $i <= dbase_numrecords ($database); $i++) {
			$registro = dbase_get_record($database ,$i);
			try {
				$recibo = $registro[0];;
				$legajo = $registro[1]; 
				$revista = utf8_encode($registro[4]);
				$apellido = utf8_encode($legajo.$revista);
				$categoria =utf8_encode($registro[9]);
				$ingreso = $registro[5];
				$pernom = utf8_encode(substr($categoria,0,10).$ingreso);    // Concatena la categoria con el numero de ingreso
				$ingreso = PHPExcel_Shared_Date::ExcelToPHPObject($ingreso)->format('d/m/y');
				$banco = utf8_encode($registro[6]);
				$mes = $registro[7];
				if(!empty($_POST['checkSac'])){
					if($mes == 6){
						$mes = 13;
					}
					if($mes == 12){
						$mes = 14;
					}
				}
				$anio = $registro[8];
				$basico = $registro[10];
				$cod = $registro[11];
				$concepto = utf8_encode($registro[12]);
				$unidades =$registro[13];
				$aportes = $registro[14];
				$exentas = $registro[15];
				$descuento = $registro[16];
				$cuil = $registro[17];
				
				$data = array(
					'RECIBO_NRO' => $recibo,
					'LEGAJO' =>$legajo,
					'APELLIDO' => $apellido,
					'PERNOM' => $pernom,
					'REVISTA' => $revista,
					'INGRESO' => $ingreso,
					'BANCO' => $banco,
					'MES' => $mes,
					'ANIO' => $anio,
					'CATEGORIA' => $categoria,
					'BASICO' => $basico,
					'COD' => $cod,
					'CONCEPTO' => $concepto,
					'UNIDADES' => $unidades,
					'APORTES' => $aportes,
					'EXENTAS' => $exentas,
					'DESCUENTO' => $descuento,
					'CUIL' => $cuil
				);
				$this->db->insert('sueldos_recibos',$data);	
			}catch (Exception $e) {	
				$datosCorrectos = false;
				$rowNumber = $i;
				var_dump("catch: ".$e);
			}
			if($datosCorrectos == false)break;
		}
		if ($this->db->trans_status() === FALSE or $datosCorrectos==false){
			$this->db->trans_rollback();
			$messages = array('error' => 'Error en la fila '.($rowNumber).' del archivo','success'=>'');
			$this->load->view('import_excel',$messages);
		}else{
			$this->db->trans_commit();	
			$messages = array('error' => '','success'=>'Archivo subido con exito.');
			$this->load->view('import_excel',$messages);
		}					
	} 

	function importExcel($path){
		$object = PHPExcel_IOFactory::load($path);
		$rowNumber = 2; /*--- Primera fila son las cabeceras --- */
		$worksheet = $object->setActiveSheetIndex(0);
		$this->db->trans_begin(); /*--- Comienzo la transaccion --- */
		$datosCorrectos = true; 

		while($worksheet->getCellByColumnAndRow(0,$rowNumber)->getValue() != "" and $datosCorrectos == true){
			/*--- Obtengo los valores del excel y formateo --- */					
			try {
				$recibo = (int)$worksheet->getCellByColumnAndRow(0, $rowNumber)->getValue();
				$legajo = (int)$worksheet->getCellByColumnAndRow(1, $rowNumber)->getValue();
				$apellido =(string) $worksheet->getCellByColumnAndRow(2, $rowNumber)->getCalculatedValue();	
				$revista = (string)$worksheet->getCellByColumnAndRow(4, $rowNumber)->getValue();
				$categoria =(string)$worksheet->getCellByColumnAndRow(9, $rowNumber)->getValue();
				$ingreso = $worksheet->getCellByColumnAndRow(5, $rowNumber)->getValue();
				$pernom = substr($categoria,0,10).$ingreso;    // Concatena la categoria con el numero de ingreso
				$ingreso = PHPExcel_Shared_Date::ExcelToPHPObject($ingreso)->format('d/m/y');
				$banco = (string)$worksheet->getCellByColumnAndRow(6, $rowNumber)->getValue();
				$mes = $worksheet->getCellByColumnAndRow(7, $rowNumber)->getValue();
				if(!empty($_POST['checkSac'])){
					if($mes == 6){
						$mes = 13;
					}
					if($mes == 12){
						$mes = 14;
					}
				}
				$anio = (int)$worksheet->getCellByColumnAndRow(8, $rowNumber)->getValue();
				$basico = (float)$worksheet->getCellByColumnAndRow(10, $rowNumber)->getValue();
				$cod = (int)$worksheet->getCellByColumnAndRow(11, $rowNumber)->getValue();
				$concepto = (string)$worksheet->getCellByColumnAndRow(12, $rowNumber)->getValue();
				$unidades = (int)$worksheet->getCellByColumnAndRow(13, $rowNumber)->getValue();
				$aportes = (float)$worksheet->getCellByColumnAndRow(14, $rowNumber)->getValue();
				$exentas = (float)$worksheet->getCellByColumnAndRow(15, $rowNumber)->getValue();
				$descuento = (float)$worksheet->getCellByColumnAndRow(16, $rowNumber)->getValue();
				$cuil = (string)$worksheet->getCellByColumnAndRow(17, $rowNumber)->getValue();
				
				$data = array(
					'RECIBO_NRO' => $recibo,
					'LEGAJO' =>$legajo,
					'APELLIDO' => $apellido,
					'PERNOM' => $pernom,
					'REVISTA' => $revista,
					'INGRESO' => $ingreso,
					'BANCO' => $banco,
					'MES' => $mes,
					'ANIO' => $anio,
					'CATEGORIA' => $categoria,
					'BASICO' => $basico,
					'COD' => $cod,
					'CONCEPTO' => $concepto,
					'UNIDADES' => $unidades,
					'APORTES' => $aportes,
					'EXENTAS' => $exentas,
					'DESCUENTO' => $descuento,
					'CUIL' => $cuil
				);
				$this->db->insert('sueldos_recibos',$data);	
			}catch (Exception $e) {
				
				$datosCorrectos = false;
			}
			$rowNumber++;
		}
		if ($this->db->trans_status() === FALSE)
		{
				$this->db->trans_rollback();
				$messages = array('error' => 'Error en la fila '.($rowNumber-1).' del archivo','success'=>'');
				$this->load->view('import_excel',$messages);
		}
		else
		{
				$this->db->trans_commit();	
				$messages = array('error' => '','success'=>'Archivo subido con exito.');
				$this->load->view('import_excel',$messages);
		}
	}
}
