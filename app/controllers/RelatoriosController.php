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

		$queryParticipantes = "SELECT r.participante, p.nome FROM participantes p JOIN respostas r on p.uid = r.participante JOIN questoes q on r.questao_id = q.id 
WHERE q.questionarios_id = ? group by r.participante";
		$questionario = 4;
		$resultParticipantes = $this->db->exec($queryParticipantes,array($questionario));
		$queryRespostas = "SELECT q.ordem,a.alternativa FROM respostas r JOIN alternativas a ON r.alternativa_id = a.id JOIN 
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
		$string = "<tr><td colspan='".(count($result)+1)."'><strong>Questionario: ".$result[0]['titulo']."</strong></td></tr>";
		$string.="<tr><td align='center'><strong>Participantes</strong></td><td colspan='".count($result)."' align='center'><strong>Respostas</strong></td></tr>";
		$string.="<tr><td></td>";
		foreach ($result as $lista) {
			$string.="<td align='center'>$lista[ordem]</td>";
		}
		$string.="</tr>";
		return $string;

	}
}