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


	function relatorio3(){
		
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

		
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false);
		header('Content-Type: application/vnd.ms-excel; charset=utf-8');
		header("Content-Disposition: attachment;filename=test.xls");
		//header("Content-Transfer-Encoding: binary");
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

			echo "<tr><td>$participante[nome]</td>";
			echo "<td>$participante[idade]</td>";
			echo "<td>$participante[email]</td>";
			echo "<td>$participante[genero]</td>";
			echo "<td>$participante[universidade]</td>";
			echo "<td>$participante[curso]</td>";
			echo "<td>$participante[semestre]</td>";
			echo "<td>".$periodo[$participante["periodo_curso"]]."</td>";
			echo "<td>".$ensino[$participante["tipo_ensino"]]."</td>";
			echo "<td>".$etnia[$participante["etnia"]]."</td>";
			echo "<td>".$notas[$participante["minhas_notas"]]."</td>";
			echo "<td>".$intencao[$participante["intencao_academica"]]."</td>";
			// $resultRespostas = $this->db->exec($queryRespostas,array($questionario,$participante["participante"]));
			$resultRespostas = $relatorio->getEstatisticas($filtros['questionarios_id'],$participante['participante']);
			foreach ($resultRespostas as $resposta) {
				echo "<td>$resposta[alternativa]</td>";
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
		$string = "<tr><td colspan='".(count($result)+12)."'><strong>Questionario: ".$result[0]['titulo']."</strong></td></tr>";
		$string.="<tr><td colspan='12' align='center'><strong>Participantes</strong></td><td colspan='".count($result)."' align='center'><strong>Respostas</strong></td></tr>";
		$string.="<tr><td>Nome</td><td>Idade</td><td>E-mail</td><td>Gênero</td><td>Universidade</td><td>Curso</td><td>Semestre</td><td>Periodo</td>";
		$string.="<td>Cursou Ensino Médio</td><td>Etnia</td><td>Minhas Notas</td><td>Intenção Acadêmica</td>";
		foreach ($result as $lista) {
			$string.="<td align='center'>$lista[ordem]</td>";
		}
		$string.="</tr>";
		return $string;

	}

	function relatorio(){
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue('A1', 'Hello World !');

		$writer = new Xlsx($spreadsheet);
		$writer->save('hello world.xlsx');		
	}
}