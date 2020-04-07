<?php

/**
 * Dados de Acesso ao Webservice
 */
define("DIGITANOTAS_WS_URL", "servicoDigitaNotas.xml");
define("DIGITANOTAS_WS_USUARIO", "servicoDigitaNotas.xml");
define("DIGITANOTAS_WS_SENHA", base64_encode("servicoDigitaNotas.xml"));

/**
 * Determina se as notas serao enviadas para o Webservice
 */
define("DIGITANOTAS_WS_ATIVAR",  FALSE);

/**
 * Configuracoes de importacao
 */
define("DIGITANOTAS_WS_CAMPUS", "SP");

// define("DB_HOST", "192.168.2.10");

?>