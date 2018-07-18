<?php

class MainController extends Controller{
	function info() {
		$classes=array(
			'Base'=>
				array(
					'hash',
					'json',
					'session',
					'mbstring'
				),
			'Cache'=>
				array(
					'apc',
					'apcu',
					'memcache',
					'memcached',
					'redis',
					'wincache',
					'xcache'
				),
			'DB\SQL'=>
				array(
					'pdo',
					'pdo_dblib',
					'pdo_mssql',
					'pdo_mysql',
					'pdo_odbc',
					'pdo_pgsql',
					'pdo_sqlite',
					'pdo_sqlsrv'
				),
			'DB\Jig'=>
				array('json'),
			'DB\Mongo'=>
				array(
					'json',
					'mongo'
				),
			'Auth'=>
				array('ldap','pdo'),
			'Bcrypt'=>
				array(
					'mcrypt',
					'openssl'
				),
			'Image'=>
				array('gd'),
			'Lexicon'=>
				array('iconv'),
			'SMTP'=>
				array('openssl'),
			'Web'=>
				array('curl','openssl','simplexml'),
			'Web\Geo'=>
				array('geoip','json'),
			'Web\OpenID'=>
				array('json','simplexml'),
			'Web\Pingback'=>
				array('dom','xmlrpc')
		);
		$this->f3->set('classes',$classes);
		$this->f3->set('content','welcome.htm');
		echo \Template::instance()->render('layout.htm');
	}

	function userref() {
		$this->f3->set('content','userref.htm');
		echo \Template::instance()->render('layout.htm');
	}

	function hello(){
		echo "Hello Fucker!";
	}

	function admin(){
		$this->f3->set('content','home.htm');
		echo View::instance()->render('tela.htm');
	}

	function demograficos(){
		if($this->f3->get('POST.nome')){
			echo $this->f3->get('POST.nome');die();
		}
		$this->f3->set('content','demograficos.html');
		echo View::instance()->render('tela.htm');		
	}

	function sysInfo(){
		phpinfo();
	}

	function convida(){
		$msgEmail = "<h4><Prezado(a) aluno(a),/h4>
		<p>
		Meu nome é Evely Boruchovitch. Sou professora da Faculdade de Educação da Universidade
		Estadual de Campinas – UNICAMP. Gostaria de convidá-lo (a) a participar de uma pesquisa
		nacional que visa conhecer melhor os fatores que favorecem ou dificultam a aprendizagem de
		estudantes universitários brasileiros. Ao participar, você receberá, por e-mail, resultados e
		informações que poderão lhe dar uma ideia geral de como você está como estudante. Os
		dados a serem obtidos contribuirão para o desenvolvimento de melhores práticas educativas e
		para o fortalecimento da capacidade de aprender dos alunos do Ensino Superior. As suas
		respostas são confidenciais e asseguro o sigilo da sua identificação pessoal.
		</p>
		<p>
			Sua colaboração é muito valiosa! Para participar da pesquisa, por favor, acesse o
			seguinte link
		</p>
		<p>Muito obrigada!</p>";
	}
	
}

