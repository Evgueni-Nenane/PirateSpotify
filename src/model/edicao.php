<?php
class Edicao
{
    private $codigoDisco;
    private $codigoEditora;
    private $dataEdicao;

    public function __construct($codigoDisco, $codigoEditora, $dataEdicao)
    { 
        $this->codigoDisco = $codigoDisco;
        $this->codigoEditora = $codigoEditora;
        $this->dataEdicao = $dataEdicao;
    }

    public function getCodigoDisco()
    {
        return $this->codigoDisco;
    }

    public function getCodigoEditora()
    {
        return $this->codigoEditora;
    }

    public function getDataEdicao()
    {
        return $this->dataEdicao;
    }
}
?>