<?php

class AutenticaWS {
	
	/**
	 * Verifica se as credencias (usuário e senha) informadas são válidas.
	 * @param string $host        	
	 * @param string $campus        	
	 * @param string $prontuario        	
	 * @param string $senha        	
	 * @return boolean
	 */
	public static function autenticar($host, $campus, $prontuario, $senha) {
		try {
			
			$cliente = new SoapClient ( $host . "?wsdl", array (
					'trace' => 1,
					'exceptions' => 1,
					'encoding' => 'UTF-8',
					'connection_timeout' => 12 
			) );
			$cliente->__setLocation ( $host );
			
			$senha = base64_encode ( $senha );
			
			$autenticacaoObj = $cliente->autenticarLDAP ( $campus, $prontuario, $senha );
			if (isset ( $autenticacaoObj ) && $autenticacaoObj->sucesso) {
				return true;
			}
		} catch ( Exception $e ) {
			print_error ( 'auth_wsifspserviceerror', 'auth_wsifsp' );
			// error_log ( "AutenticacaoWS::autenticar service general error: " . $e->getMessage () );
			// throw $e;
		}
		return false;
	}
	/**
	 * Obtém dados do usuário para cadastro no Moodle.
	 * @param string $host        	
	 * @param string $campus        	
	 * @param string $prontuario        	
	 * @return object
	 */
	public static function consultarUsuario($host, $campus, $prontuario) {
		$cliente = new SoapClient ( $host . '?wsdl', array (
				'trace' => 1,
				'exceptions' => 1,
				'encoding' => 'UTF-8',
				'connection_timeout' => 12 
		) );
		
		$cliente->__setLocation ( $host );
		
		$consultaObj = $cliente->consultarUsuarioLDAP ( $campus, $prontuario );
		
		return $consultaObj;
	}
}
