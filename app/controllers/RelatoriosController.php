<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
class RelatoriosController extends Controller{

	private $rel=array();
	function home($hasError){
		$this->isAdmin();
		$universidade = new UniversidadeDAO();
		$questionario = new QuestionariosDAO();
		$lista = new ListaConvidadosDAO();
		$questionarios = $questionario->getList();
		$universidades = $universidade->getList();
		$cursos = $universidade->getCursos();
		$listas = $lista->getListas();
		$paramType = gettype($hasError);
		if (is_string($hasError)) {
			$this->f3->set('error',$hasError);	
		}
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

		if ($this->f3->get('FILES.upload.size') > 0) {
			$this->web = \Web::instance();
			$this->f3->set('UPLOADS','lassi_files/'); // don't forget to set an Upload directory, and make it writable!

			$overwrite = true; // set to true, to overwrite an existing file; Default: false
			$slug = false; // rename file to filesystem-friendly version
			$wrongtype = "";
			$files = $this->web->receive(function($file,$upload){
					if(strpos($file['type'], "excel") === FALSE && strpos($file['type'], "spreadsheet") === FALSE ){
						return false;
					}
			        if($file['size'] > (2 * 1024 * 1024)) // if bigger than 2 MB
			            return false; // this file is not valid, return false will skip moving it
			        // everything went fine, hurray!
			        return true; // allows the file to be moved from php tmp dir to your defined upload dir
			    },
			    $overwrite,
			    function($fileBaseName,$upload){
			    	//this callback change de filename
			        $ext = explode('.', $fileBaseName)[1];
			        return 'planilha_lassi.'.$ext;			    	
			    }
			);
			if(!$files){
				$this->f3->call('RelatoriosController->home',"O arquivo do LASSI precisa estar nos formatos xls ou xlsx.");
				return;
			}
		}

		$filtros['universidades_id'] = $this->f3->get('POST.universidade');
		$filtros['questionarios_id'] = $this->f3->get('POST.questionario');
		$filtros['c.id'] 		 	 = $this->f3->get('POST.curso');
		$filtros['genero']		 	 = $this->f3->get('POST.genero');
		$filtros['idlista']		 	 = $this->f3->get('POST.turma');

		$relatorio = new RelatoriosDAO();
		$participantes = $relatorio->getParticipantes($filtros);
		$relobj=array();
		$relobj = $participantes;
		if (empty($relobj)){
			$this->f3->call('RelatoriosController->home',"A combinação de filtros retornou um conjunto vazio");
			return;
		}
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
		\PhpOffice\PhpSpreadsheet\Cell\Cell::setValueBinder( new \PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder() );
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
				if ($header[$chave] == 'testdate') {
					$campo = date("d/m/Y",($campo - 25569)*86400);
				}
				if ($header[$chave] == 'timetaken') {
					$campo = date("H:i:s",($campo - 25569)*86400);
				}
				$LassiArr[$key][$header[$chave]] = $campo;
			}
		}
		$newArr = array();
		foreach ($this->rel as $Skey => $linhaSys) {
			foreach ($LassiArr as $Lkey=>$linhaLassi) {
				if($linhaSys["email"] == $linhaLassi["email"]){
					$newArr[] = array_merge($linhaSys, $linhaLassi);
				}
			}
		}

		$this->printArray($newArr);
	}

	function printArray($array,$generateFile=false){
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet()
					->fromArray(array_keys($array[0]));
		$sheet = $spreadsheet->getActiveSheet()
					->fromArray($array,NULL,'A2');
		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
		
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="relatorio_autorregular.xls"');
		header('Cache-Control: max-age=0');
		$writer->save('lassi_files/relatorio_autorregular.xls');
		$writer->save('php://output');
		
		$spreadsheet->disconnectWorksheets();
		unset($spreadsheet);

		// $cabecalho = array_keys($array[0]);
		// array_unshift($cabecalho, "ordem");
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

	function sendPostTestMail(){
		$this->isAdmin();
		\PhpOffice\PhpSpreadsheet\Cell\Cell::setValueBinder( new \PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder() );
		$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xls');
		$reader->setReadDataOnly(TRUE);
		$spreadsheet = $reader->load("lassi_files/posteste.xls");

		$aux = $spreadsheet->getActiveSheet()
						   ->toArray();
		$header = $aux[0];
		array_shift($aux);
		foreach ($header as $key => $value) {
			$header[$key] = strtolower($value);
		}
		
		$smtp = new SMTP ( $this->f3->get('SMTP_SERVER'), $this->f3->get('SMTP_PORT'), $this->f3->get('SMTP_SCHEME'), $this->f3->get('SMTP_MAIL'), $this->f3->get('SMTP_PASS') );

		$smtp->set('MIME-Version', '1.0');
		$smtp->set('Content-type', 'text/html;charset=UTF-8');
		$append="";
		if(strpos($this->f3->get('SMTP_MAIL'), "@") === FALSE){
			$append = "@".$this->f3->get('SMTP_DOMAIN');
		}
		$smtp->set('From', '"Pós-Teste LASSI" <celiaro-drigues@hotmail.com>');
		$smtp->set('Subject', 'Convite – Como aprender na universidade');
				
		$mailMessage = "<h4>Olá, ESTUDANTE!</h4>
						<p>Estás convidado a responder, novamente, ao questionário LASSI, uma vez que tu já és parte do grupo que está participando do meu 
						projeto de doutorado com ênfase nas estratégias de estudo e aprendizagem dos estudantes da Universidade Federal de Pelotas (UFPel).</p>
		    <p>Solicito, por favor, que respondas, com a maior sinceridade, as situações que melhor descrevem o teu estudo na universidade relativo ao segundo 
		    semestre da faculdade (2019/2). Lembra-te de que, ao final, receberás, por e-mail, o gráfico com os resultados do inventário. Assim como também, 
		    dicas das estratégias que podes utilizar para melhorar tua aprendizagem.</p>
			<p>Para responder ao inventário clique no link: 
				<a href='https://www.collegelassi.com/portuguese'>https://www.collegelassi.com/portuguese</a></p>
			<p>Ao abrir o link, siga estes passos:</p>
			<ol>
				<li>Usarás a caixa da direita, com o título <strong><i>Second Administration</i></strong>;</li>
				<li>Em <strong><i>School Number</i></strong>, digite <strong>80010</strong>;</li>
				<li>Em <strong><i>Student Key</i></strong> digite <strong>CHAVE</strong>;</li>
				<li>Clique no botão <strong>Realizar Pós-Teste</strong> e aguarde carregar o teste;</li>
			</ol>
			<p>Obrigada por aceitar participar da pesquisa!</p>
			<p>Célia Artemisa G. R. Miranda</p>
			<p>Doutoranda do Programa de Pós-graduação em Educação</p>
			<p>Universidade Federal de Pelotas</p>
			<p>(celiaro-drigues@hotmail.com)</p>";
		$replaceWhat = array("ESTUDANTE","CHAVE");
		//foreach ($aux as $key => $linha) {
		foreach ($aux as $linha) {
			/*if ($header[$chave] == 'testdate') {
				$campo = date("d/m/Y",($campo - 25569)*86400);
			}
			if ($header[$chave] == 'timetaken') {
				$campo = date("H:i:s",($campo - 25569)*86400);
			}*/
			$smtp->set('To', $linha[3]);
			echo str_replace($replaceWhat, array(utf8_decode($linha[15]),utf8_decode($linha[17])), $mailMessage)."Enviando para $linha[3]...<br>";
			$smtp->send(str_replace($replaceWhat, array(utf8_decode($linha[15]),$linha[17]), $mailMessage));
		}
		//}		
	}
}