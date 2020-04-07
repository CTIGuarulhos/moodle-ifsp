<?php

class NotaAlunoWS
{

    /**
     * @var int $ano
     * @access public
     */
    public $ano = null;

    /**
     * @var string $bimestre
     * @access public
     */
    public $bimestre = 1;

    /**
     * @var string $campus
     * @access public
     */
    public $campus = null;

    /**
     * @var string $codigoDisciplina
     * @access public
     */
    public $codigoDisciplina = null;

    /**
     * @var string $dataGravacao
     * @access public
     */
    public $dataGravacao = null;

    /**
     * @var int $eventoTod
     * @access public
     */
    public $eventoTod = null;

    /**
     * @var int $falta
     * @access public
     */
    public $falta = 0;

    /**
     * @var string $flagDigitacaoNota
     * @access public
     */
    public $flagDigitacaoNota = null;

    /**
     * @var float $nota
     * @access public
     */
    public $nota = null;

    /**
     * @var string $prontuarioAluno
     * @access public
     */
    public $prontuarioAluno = null;

    /**
     * @var string $prontuarioUsuario
     * @access public
     */
    public $prontuarioUsuario = null;

    /**
     * @var string $semestre
     * @access public
     */
    public $semestre = null;

    /**
     * @var int $turma
     * @access public
     */
    public $turma = null;

    /**
     * @access public
     */
    public function __construct()
    {
        $this->dataGravacao = date("dmY");
    }

    /**
     * Checa se o objeto nota é valido
     * @access public
     */
    public function isValid()
    {
        return $this->prontuarioAluno != null;
    }

    /**
     * Define a nota do aluno efetuando os arredondamenetos aproprieados
     * @access public
     */
    public function setNota($nota)
    {
        return $this->nota = ceil($nota * 2) / 2;
    }
}
