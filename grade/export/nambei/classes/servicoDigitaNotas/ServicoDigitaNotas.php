<?php

include_once('NotaAlunoWSList.php');
include_once('NotaAlunoWS.php');
include_once('ResultSearchDigitacao.php');
include_once('../../../config.php');

class ServicoDigitaNotas extends \SoapClient
{

    /**
     * @var array $classmap The defined classes
     * @access private
     */
    private static $classmap = array(
      'NotaAlunoWSList' => '\NotaAlunoWSList',
      'NotaAlunoWS' => '\NotaAlunoWS',
      'ResultSearchDigitacao' => '\ResultSearchDigitacao');

    /**
     * @var string $digitaNotasURL URL que sera usada para chamar o Servico
     * @access private
     */
    private static $digitaNotasURL;

    /**
     * @param array $options A array of config values
     * @param string $wsdl The wsdl file to use
     * @access public
     */
    public function __construct($wsdl, array $options = array())
    {
      $this->digitaNotasURL = explode('?', $wsdl)[0];

      foreach (self::$classmap as $key => $value) {
        if (!isset($options['classmap'][$key])) {
          $options['classmap'][$key] = $value;
        }
      }
      
      parent::__construct($wsdl, $options);
    }

    /**
     * @param string $campus
     * @param NotaAlunoWSList $listaNotas
     * @access public
     * @return ResultSearchDigitacao
     */
    public function digitarNotasAlunos($campus, NotaAlunoWSList $listaNotas)
    {
      return $this->__soapCall('digitarNotasAlunos', array($campus, $listaNotas),
      array(
        'location' => $this->digitaNotasURL
      ));
    }

}
