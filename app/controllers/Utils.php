<?php
class Utils extends Controller{
	public static function getCidades(){
		$locais = new CidadesEstadosDAO();
		$estado = $this->f3->get('GET.uf');
		$cidades = $locais->getCidadesByEstado($estado);

		echo json_encode($cidades);
	}
}