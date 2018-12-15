<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
class RelatoriosController extends Controller{

	private $rel=array();
	function home(){
		$this->isAdmin();
		$universidade = new UniversidadeDAO();
		$questionario = new QuestionariosDAO();
		$lista = new ListaConvidadosDAO();
		$questionarios = $questionario->getList();
		$universidades = $universidade->getList();
		$cursos = $universidade->getCursos();
		$listas = $lista->getListas();
		$this->f3->set('questionarios',$questionarios);
		$this->f3->set('universidades',$universidades);
		$this->f3->set('cursos',$cursos);
		$this->f3->set('turmas',$listas);
		$this->f3->set('content','admin/homeRelatorios.html');
		echo \Template::instance()->render('tela.htm');

	}

	function gerar(){

	}


	function relatorio(){
		
		$filtros=array();

		if ($this->f3->get('FILES')) {
			$this->web = \Web::instance();
			$this->f3->set('UPLOADS','lassi_files/'); // don't forget to set an Upload directory, and make it writable!

			$overwrite = true; // set to true, to overwrite an existing file; Default: false
			$slug = false; // rename file to filesystem-friendly version
			$files = $this->web->receive(function($file,$upload){
			        if($file['size'] > (2 * 1024 * 1024)) // if bigger than 2 MB
			            return false; // this file is not valid, return false will skip moving it
			        // everything went fine, hurray!
			        return true; // allows the file to be moved from php tmp dir to your defined upload dir
			    },
			    $overwrite,
			    function($fileBaseName,$upload){
			    	//this callback change de filename
			        $ext = explode('.', $fileBaseName)[1];
			        return 'novoNome.'.$ext;			    	
			    }
			);
		}

		$filtros['universidades_id'] 	 = $this->f3->get('POST.universidade');
		$filtros['questionarios_id'] = $this->f3->get('POST.questionario');
		$filtros['c.id'] 		 	 = $this->f3->get('POST.curso');
		$filtros['genero']		 	 = $this->f3->get('POST.genero');

		$relatorio = new RelatoriosDAO();
		$participantes = $relatorio->getParticipantes($filtros);
		
		$relobj=array();
		$relobj = $participantes;
		$i=0;
		foreach ($participantes as $participante) {
			$resultRespostas = $relatorio->getEstatisticas($filtros['questionarios_id'],$participante['participante']);
			$questao=1;

			foreach ($resultRespostas as $resposta) {

				if($filtros['questionarios_id'] == 11){
					$resposta["texto"] = str_replace("%", "", $resposta["texto"]);
					$outputResposta = $resposta["texto"];
				}else{
					$outputResposta = $resposta["alternativa"];
				}
				$relobj[$i]["q".$questao]=$outputResposta;
				$questao++;
			}
			$i++;
		}
		$this->rel = $relobj;

		if ($this->f3->get('POST.join')) {
			$this->unir();
		}else{
			$this->printArray($this->rel);
		}

		
		unset($relatorio);
	}

	function getQuestHeader($quest){
		$relatorio = new RelatoriosDAO();
		$result = $relatorio->getRelatorioHeader($quest);
		unset($relatorio);
		$string = "<tr><td colspan='".(count($result)+12)."'><strong>".$this->sanitizeWords($result[0]['titulo'],true)."</strong></td></tr>";
		$string.="<tr><td>Nome</td><td>Idade</td><td>E-mail</td><td>Gênero</td><td>Universidade</td><td>Curso</td><td>Semestre</td><td>Periodo</td>";
		$string.="<td>Cursou Ensino Médio</td><td>Etnia</td><td>Minhas Notas</td><td>Intenção Acadêmica</td>";
		foreach ($result as $lista) {
			$string.="<td align='center'>Q$lista[ordem]</td>";
		}
		$string.="</tr>";
		return $string;

	}

	function sanitizeWords($field,$capital=false){
	    $letters = [
	        0 => "a à á â ä æ ã å ā",
	        1 => "c ç ć č",
	        2 => "e é è ê ë ę ė ē",
	        3 => "i ī į í ì ï î",
	        4 => "l ł",
	        5 => "n ñ ń",
	        6 => "o ō ø œ õ ó ò ö ô",
	        7 => "s ß ś š",
	        8 => "u ū ú ù ü û",
	        9 => "w ŵ",
	        10 => "y ŷ ÿ",
	        11 => "z ź ž ż",
	    ];
	    foreach ($letters as &$values){
	        $newValue = substr($values, 0, 1);
	        $values = substr($values, 2, strlen($values));
	        $values = explode(" ", $values);
	        foreach ($values as &$oldValue){
	            while (strpos($field,$oldValue) !== false){
	                $field = preg_replace("/" . $oldValue . '/', $newValue, $field, 1);
	            }
	        }
	    }
	    return $field;
	}

	function relatorios(){
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue('A1', 'Hello World !');

		$writer = new Xlsx($spreadsheet);
		$writer->save('lassi_files/hello_world.xlsx');		
	}

	function unir(){
		// $handle = fopen("tmp/planilha_bruta.csv", "r");
		// $LassiArr = array();
		// $i=0;
		// $header = fgetcsv($handle);
		// foreach ($header as $key => $value) {
		// 	$header[$key] = strtolower($value);
		// }
		// while($linha=fgetcsv($handle)){
		// 	foreach ($linha as $key => $campo) {
		// 		$LassiArr[$i][$header[$key]] = $campo;
		// 	}
		// 	$i++;
		// }
		$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xls');
		$reader->setReadDataOnly(TRUE);
		$spreadsheet = $reader->load("lassi_files/planilha_lassi.xls");

		$aux = $spreadsheet->getActiveSheet()
						   ->toArray();
		$header = $aux[0];
		array_shift($aux);
		foreach ($header as $key => $value) {
			$header[$key] = strtolower($value);
		}
		foreach ($aux as $key => $linha) {
			foreach ($linha as $chave=>$campo) {
				$LassiArr[$key][$header[$chave]] = $campo;
			}
		}
		var_dump($LassiArr);die();

		$newArr = array();
		foreach ($this->rel as $Skey => $linhaSys) {
			foreach ($LassiArr as $Lkey=>$linhaLassi) {
				if($linhaSys["email"] == $linhaLassi["email"]){
					$newArr[] = array_merge($linhaSys, $linhaLassi);
				}
			}
		}

		$this->printArray($newArr);

		fclose($handle);
	}

	function printArray($array,$generateFile=false){
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="test.xlsx"');
		header('Cache-Control: max-age=0');		
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet()
					->fromArray(array_keys($array[0]));
		$sheet = $spreadsheet->getActiveSheet()
					->fromArray($array,NULL,'A2');
		$writer = new Xlsx($spreadsheet);
		$writer->save('lassi_files/hello_world.xlsx');
		$writer->save('php://output');
		
		$spreadsheet->disconnectWorksheets();
		unset($spreadsheet);
		$cabecalho = array_keys($array[0]);
		array_unshift($cabecalho, "ordem");
		// echo "<table border=1>";
		// echo "<tr>";
		// foreach ($cabecalho as $campo) {
		// 	echo "<td>$campo</td>";
		// }
		// echo "<tr>";
		// foreach ($array as $key => $linha) {
		// 	echo "<tr><td>$key</td>";
		// 	foreach ($linha as $campo) {
		// 		echo "<td>".$campo."</td>";
		// 	}
		// 	echo "</tr>";
		// }		
		// echo "</table>";		
	}
}