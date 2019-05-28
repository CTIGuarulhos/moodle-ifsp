<?php

/**
 * Classe estática que possui métodos úteis.
 *
 * @author william
 */
class Criptografia {
	public static function codificar($str) {
		return base64_encode ( $str );
	}
	public static function decodificar($str) {
		return base64_decode ( $str );
	}
}

