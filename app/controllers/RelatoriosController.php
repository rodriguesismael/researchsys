<?php
//require 'vendor/autoload.php';
class RelatoriosController extends Controller{
	function home(){
		$listaQ = $this->db->exec("SELECT * FROM questionarios");
		$this->f3->set('questionarios',$listaQ);

	}

	function gerar(){

	}


	function relatorio(){
		
		// $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();  //----Spreadsheet object-----
		// $Excel_writer = new PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);  //----- Excel (Xls) Object
		// $spreadsheet->setActiveSheetIndex(0);
		// $activeSheet = $spreadsheet->getActiveSheet();
		// $activeSheet->setCellValue('A1' , 'TESTANDOOO')->getStyle('A1')->getFont()->setBold(true);

		$queryParticipantes = "SELECT r.participante, p.nome, timestampdiff(YEAR, nascimento,now()) as idade,email,genero,u.nome universidade,c.nome curso, 
					semestre, periodo_curso, tipo_ensino FROM participantes p JOIN respostas r on p.uid = r.participante JOIN questoes q on r.questao_id = q.id 
					JOIN universidades u ON p.universidades_id = u.id JOIN cursos c ON p.curso_id = c.id WHERE q.questionarios_id = ? group by r.participante";
		$questionario = 5;
		$resultParticipantes = $this->db->exec($queryParticipantes,array($questionario));
		$queryRespostas = "SELECT q.ordem,a.ordem alternativa FROM respostas r JOIN alternativas a ON r.alternativa_id = a.id JOIN 
    questoes q on r.questao_id = q.id JOIN questionarios qr on q.questionarios_id = qr.id 
    WHERE qr.id=? and r.participante=?";

		
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header('Content-Type: application/xls; charset=utf-8');
		header("Content-Disposition: attachment;filename=test.xls");
		header("Content-Transfer-Encoding: binary");
		echo "<table>";
		echo $this->getQuestHeader($questionario);
		foreach ($resultParticipantes as $participante) {

			echo "<tr><td>$participante[nome]</td>";
			echo "<td>$participante[idade]</td>";
			echo "<td>$participante[email]</td>";
			echo "<td>$participante[genero]</td>";
			echo "<td>$participante[universidade]</td>";
			echo "<td>$participante[curso]</td>";
			echo "<td>$participante[semestre]</td>";
			echo "<td>$participante[periodo_curso]</td>";
			echo "<td>$participante[tipo_ensino]</td>";
			$resultRespostas = $this->db->exec($queryRespostas,array($questionario,$participante["participante"]));
			foreach ($resultRespostas as $resposta) {
				echo "<td>$resposta[alternativa]</td>";
			}
			echo "</tr>";
		}
		echo "</table>";
	}

	function getQuestHeader($quest){
		$qry = "SELECT q.titulo,qr.ordem from questionarios q JOIN questoes qr ON q.id = qr.questionarios_id WHERE q.id=? order by qr.ordem";
		$result = $this->db->exec($qry,array($quest));
		$string = "<tr><td colspan='".(count($result)+9)."'><strong>Questionario: ".$result[0]['titulo']."</strong></td></tr>";
		$string.="<tr><td colspan='9' align='center'><strong>Participantes</strong></td><td colspan='".count($result)."' align='center'><strong>Respostas</strong></td></tr>";
		$string.="<tr><td>Nome</td><td>Idade</td><td>E-mail</td><td>Gênero</td><td>Universidade</td><td>Curso</td><td>Semestre</td><td>Periodo</td><td>Tipo de Ensino</td>";
		foreach ($result as $lista) {
			$string.="<td align='center'>$lista[ordem]</td>";
		}
		$string.="</tr>";
		return $string;

	}
}