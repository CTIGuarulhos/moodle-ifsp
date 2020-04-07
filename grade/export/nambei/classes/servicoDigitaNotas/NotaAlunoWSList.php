<?php

class NotaAlunoWSList
{

    /**
     * @var NotaAlunoWS[] $notas
     * @access public
     */
    public $notas = [];

    /**
     * @access public
     */
    public function __construct()
    {
    
    }

    /**
     * @param string $campus
     * @param NotaAlunoWSList $listaNotas
     * @access public
     */
    public function adicionarNota(NotaAlunoWS $nota)
    {
        array_push($this->notas, $nota);
    }
}
