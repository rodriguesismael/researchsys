<?php

class ParticipantesController extends Controller{

	function home(){
		if($this->f3->get('SESSION.idusr') == 1){
			pass;
		}else{
			$this->f3->reroute('/admin/login');
		}		
		$this->f3->set('content','listaParticipantes.html');
		echo \Template::instance()->render('tela.htm');
	}

	function nova(){
		$emails = $this->f3->get('POST.maillist');
		$quests = $this->f3->get('POST.escalas');

		$queryLista = "INSERT INTO listas (questionarios) VALUES (?)";
		$mapper = new \DB\SQL\Mapper($this->db, 'listas');
		try {
			$this->db->exec($queryLista,array(serialize($quests)));
			$idLista = $mapper->get('_id');
			var_export($mapper);
			//var_export($mapper->select('id'));
			
			foreach ($emails as $mail) {
				$queryEmails = "INSERT INTO convidados (idlista,email) VALUES (:idLista,:email)";
				$this->db->exec($queryEmails,array(':idLista'=>$idLista,':email'=>$mail));
			}
			
		} catch (Exception $e) {
			
		}

	}

}