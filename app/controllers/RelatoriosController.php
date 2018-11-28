<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
class RelatoriosController extends Controller{
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

		$filtros['universidades_id'] 	 = $this->f3->get('POST.universidade');
		$filtros['questionarios_id'] = $this->f3->get('POST.questionario');
		$filtros['c.id'] 		 	 = $this->f3->get('POST.curso');
		$filtros['genero']		 	 = $this->f3->get('POST.genero');

		$relatorio = new RelatoriosDAO();
		$participantes = $relatorio->getParticipantes($filtros);
		
		//$queryRespostas = "SELECT q.ordem,a.ordem alternativa FROM respostas r JOIN alternativas a ON r.alternativa_id = a.id JOIN 
  //   questoes q on r.questao_id = q.id JOIN questionarios qr on q.questionarios_id = qr.id 
  //   WHERE qr.id=? and r.participante=?";

		
		// header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		// header("Cache-Control: private",false);
		// header('Content-Type: application/vnd.ms-excel; charset=utf-8');
		// header("Content-Disposition: inline;filename=test.xls");
		// header("Content-Transfer-Encoding: binary");
		echo "<table>";
		echo $this->getQuestHeader($filtros['questionarios_id']);

		foreach ($participantes as $participante) {
			$ensino   = array("1"=>"Escola Pública","2"=>"Escola Particular","3"=>"Ambas");
			$periodo  = array("N"=>"Noturno","V"=>"Vespertino","M"=>"Matutino","I"=>"Integral");
			$etnia	  = array("1"=>"Branca","2"=>"Negra","3"=>"Parda","4"=>"Indígena","5"=>"Oriental","6"=>"Outra");
			$intencao = array("1"=>"Nenhuma Intenção","2"=>"Muito Pouca Intenção",
							  "3"=>"Pouca Intenção","4"=>"Moderada Intenção",
							  "5"=>"Muita Intenção","6"=>"Muitíssima Intenção","7"=>"Total Intenção");
			$notas 	  = array("1"=>"Bem Acima","2"=>"Bem Abaixo","3"=>"Em torno","4"=>"Abaixo","5"=>"Bem abaixo","6"=>"Ainda não tenho ideia");

			echo "<tr><td>".$this->sanitizeWords($participante["nome"])."</td>";
			echo "<td>$participante[idade]</td>";
			echo "<td>$participante[email]</td>";
			echo "<td>$participante[genero]</td>";
			echo "<td>$participante[universidade]</td>";
			echo "<td>".$participante["curso"]."</td>";
			echo "<td>$participante[semestre]</td>";
			echo "<td>".$participante["periodo_curso"]."</td>";
			echo "<td>$participante[tipo_ensino]</td>";
			echo "<td>$participante[etnia]</td>";
			echo "<td>$participante[minhas_notas]</td>";
			echo "<td>$participante[intencao_academica]</td>";
			// $resultRespostas = $this->db->exec($queryRespostas,array($questionario,$participante["participante"]));
			$resultRespostas = $relatorio->getEstatisticas($filtros['questionarios_id'],$participante['participante']);
			foreach ($resultRespostas as $resposta) {
				if($filtros['questionarios_id'] == 11){
					$resposta["texto"] = str_replace("%", "", $resposta["texto"]);
					echo "<td>$resposta[texto]</td>";
				}else{
					echo "<td>$resposta[alternativa]</td>";
				}
			}
			echo "</tr>";
		}
		echo "</table>";
		unset($relatorio);
	}

	function getQuestHeader($quest){
		//$qry = "SELECT q.titulo,qr.ordem from questionarios q JOIN questoes qr ON q.id = qr.questionarios_id WHERE q.id=? order by qr.ordem";
		// $result = $this->db->exec($qry,array($quest));
		$relatorio = new RelatoriosDAO();
		$result = $relatorio->getRelatorioHeader($quest);
		// var_dump($result);die();
		unset($relatorio);
		$string = "<tr><td colspan='".(count($result)+12)."'><strong>".$this->sanitizeWords($result[0]['titulo'],true)."</strong></td></tr>";
		// $string.="<tr><td colspan='12' align='center'><strong>Participantes</strong></td><td colspan='".count($result)."' align='center'><strong>Respostas</strong></td></tr>";
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

	function relatorio3(){
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue('A1', 'Hello World !');

		$writer = new Xlsx($spreadsheet);
		$writer->save('hello world.xlsx');		
	}

	function unir(){
		$handle = fopen("tmp/planilha_lassi.csv", "r");
		$LassiArr = array();
		$i=0;
		while($linha=fgetcsv($handle)){
			$LassiArr[$i] = $linha;
			$i++;
		}
		echo "<table>";
		foreach ($LassiArr as $key => $linha) {
			echo "<tr><td>$key</td>";
			foreach ($linha as $campo) {
				echo "<td>".$campo."</td>";
			}
			echo "</tr>";
		}

		echo "</table>";

		fclose($handle);
	}
}