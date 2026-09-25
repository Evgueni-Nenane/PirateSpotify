<?php


class Compositor
{
    private $codigoCompositor;
    private $nomeCompositor;
    private $apelidoCompositor;
    private $contactoCompositor;
    private $emailCompositor;

    public function __construct($codigoCompositor = null, $nomeCompositor = null, $apelidoCompositor = null, $contactoCompositor = null, $emailCompositor = null)
    {
        $this->codigoCompositor = $codigoCompositor;
        $this->nomeCompositor = $nomeCompositor;
        $this->apelidoCompositor = $apelidoCompositor;
        $this->contactoCompositor = $contactoCompositor;
        $this->emailCompositor = $emailCompositor;
    }

    public function getCodigoCompositor()
    {
        return $this->codigoCompositor;
    }

    public function setCodigoCompositor($codigoCompositor)
    {
        return $this->codigoCompositor = $codigoCompositor;
    }

    public function getNomeCompositor()
    {
        return $this->nomeCompositor;
    }

    public function setNomeCompositor($nomeCompositor)
    {
        return $this->nomeCompositor = $nomeCompositor;
    }

    public function getApelidoCompositor()
    {
        return $this->apelidoCompositor;
    }

    public function setApelidoCompositor($apelidoCompositor)
    {
        return $this->apelidoCompositor = $apelidoCompositor;
    }

    public function getContactoCompositor()
    {
        return $this->contactoCompositor;
    }

    public function setContactoCompositor($contactoCompositor)
    {
        return $this->contactoCompositor = $contactoCompositor;
    }

    public function getEmailCompositor()
    {
        return $this->emailCompositor;
    }

    public function setEmailCompositor($emailCompositor)
    {
        return $this->emailCompositor = $emailCompositor;
    }

    public function getNomeCompleto()
    {
        return $this->nomeCompositor . " " . $this->apelidoCompositor;
    }


    public function __toString()
    {
        return "Compositor:{$this->nomeCompositor}";
    }
}