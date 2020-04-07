<?php

class ResultSearchDigitacao
{

    /**
     * @var string $motivo
     * @access public
     */
    public $motivo = null;

    /**
     * @var boolean $sucesso
     * @access public
     */
    public $sucesso = null;

    /**
     * @param boolean $sucesso
     * @access public
     */
    public function __construct($sucesso)
    {
      $this->sucesso = $sucesso;
    }

}
