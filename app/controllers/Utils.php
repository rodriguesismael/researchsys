<?php
class Utils extends Controller{
	public static function getCidades(){
		$query="SELECT id,nome FROM cidades WHERE estado_uf=?";
		$estado = $this->f3->get('GET.uf');
		$cidades = $this->db->exec($query,$estado);

		echo json_encode($cidades);
	}
}