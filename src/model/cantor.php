<?php
//Cantor.php
class Cantor
{
    private $codigoCantor;
    private $nomeCantor;
    private $apelidoCantor;
    private $contactoCantor;
    private $emailCantor;


    public function __construct($codigoCantor = null, $nomeCantor = null, $apelidoCantor = null, $contactoCantor = null, $emailCantor = null)
    {
        $this->codigoCantor = $codigoCantor;
        $this->nomeCantor = $nomeCantor;
        $this->apelidoCantor = $apelidoCantor;
        $this->contactoCantor = $contactoCantor;
        $this->emailCantor = $emailCantor;
    }


    public function getCodigoCantor()
    {
        return $this->codigoCantor;
    }

    public function setCodigoCantor($codigoCantor)
    {
        return $this->codigoCantor = $codigoCantor;
    }

    public function getNomeCantor()
    {
        return $this->nomeCantor;
    }

    public function setNomeCantor($nomeCantor)
    {
        return $this->nomeCantor = $nomeCantor;
    }

    public function getApelidoCantor()
    {
        return $this->apelidoCantor;
    }

    public function setApelidoCantor($apelidoCantor)
    {
        return $this->apelidoCantor = $apelidoCantor;
    }

    public function getContactoCantor()
    {
        return $this->contactoCantor;
    }

    public function setContactoCantor($contactoCantor)
    {
        return $this->contactoCantor = $contactoCantor;
    }

    public function getEmailCantor()
    {
        return $this->emailCantor;
    }

    public function setEmailCantor($emailCantor)
    {
        return $this->emailCantor = $emailCantor;
    }

    public function getNomeCompleto()
    {
        return $this->nomeCantor . " " . $this->apelidoCantor;
    }

    public function __toString()
    {
        return "Cantor:{$this->nomeCantor} {$this->apelidoCantor}";
    }
}