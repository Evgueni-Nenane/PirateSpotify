<?php 
require_once 'connection.php';
require_once 'Edicao.php';

class EdicaoDAO {
	   public function inserir($edicao) {
        $conn = connection::connectionDB();
	        $sql = "INSERT INTO Edicao (Data_Edicao, Codigo_DC, Codigo_Editora) VALUES (?, ?, ?)";
	      $ps = $conn->prepare($sql);
            $Data_Edicao = $edicao->getDataEdicao();
            $Codigo_DC = $edicao->getCodigoDC();
            $Codigo_Editora = $edicao->getCodigoEditora();
            $ps->bind_param("sii", $Data_Edicao, $Codigo_DC, $Codigo_Editora);
            if ($ps->execute()) {
                return $conn->insert_id;
            }
            return -1;
	    }
	   public function buscarPorCodigoDisco($codigoDisco) {
        $conn = connection::connectionDB();
	        $sql = "SELECT * FROM Edicao WHERE Codigo_DC = ?";
	        $ps = $conn->prepare($sql);
	        $ps->bind_param("i", $codigoDisco);
	        $ps->execute();
	        $row = $ps->get_result()->fetch_assoc();
	        if ($row) {
	            return new Edicao(
	                $row['Codigo_DC'],
	                $row['Codigo_Editora'],
	                $row['Data_Edicao']
	            );
	        }
	        return null;
	   }
	}
	          