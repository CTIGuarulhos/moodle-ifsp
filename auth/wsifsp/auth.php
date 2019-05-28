<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Authentication Plugin: WS IFSP Authentication (ADAPTADO)
 * Authenticates against a WS server.
 *
 * @package auth_wsifsp
 * @author Paulo Jose Evaristo da Silva
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
defined ( 'MOODLE_INTERNAL' ) || die ();

require_once ($CFG->libdir . '/authlib.php');
require_once ('autenticacaoWS.php');

/**
 * WS IFSP authentication plugin.
 */
class auth_plugin_wsifsp extends auth_plugin_base {
	
	/**
	 * Constructor.
	 */
	function auth_plugin_wsifsp() {
		$this->authtype = 'wsifsp';
		$this->config = get_config ( 'auth/wsifsp' );
	}
	
	/**
	 * Indica se moodle deve atualizar automaticamente os registros de usuários
	 * com dados de fontes externas usando as informações do método get_userinfo().
	 *
	 * @return bool não deve fazer atualização automática
	 */
	function is_synchronised_with_external() {
		return false;
	}
	
	/**
	 * Busca informações do usuário em fontes externas.
	 *
	 * @param string $username        	
	 *
	 * @return array Informações do usuário para cadastramento no Moodle
	 */
	function get_userinfo($username) {
		global $CFG;
		
		$campus = substr ( $username, 0, 2 );
		$prontuario = substr ( $username, 2 );
		$host = $this->config->host;
		
		$dataUser = AutenticaWS::consultarUsuario ( $host, $campus, $prontuario );
		
		$newUser = new stdClass ();
		$newUser->auth = $this->authtype;
		$newUser->confirmed = 1;
		$newUser->mnethostid = ( int ) $CFG->mnet_localhost_id;
		$newUser->username = $username;
		$newUser->firstname = strstr ( $dataUser->nome, ' ', true );
		$newUser->lastname = ltrim ( strstr ( $dataUser->nome, ' ' ) );
		$newUser->email = $dataUser->email;
		
		return ( array ) $newUser;
	}
	
	/**
	 * Returns true if the username and password work and false if they are
	 * wrong or don't exist.
	 *
	 * @param string $username
	 *        	The username
	 * @param string $password
	 *        	The password
	 * @return bool Authentication success or failure.
	 */
	function user_login($username, $password) {
		/*
		 * TODO: Verificar funcionamento modulo PHP SoapClient 
		 * 	if (! function_exists('SOAPClient')) { 
		 * 		print_error('auth_wsifspnotinstalled','auth_wsifsp'); 
		 * 		exit; 
		 * }
		 */
		global $CFG, $DB;
		$host = $this->config->host;
		
		// TODO: Validar sigla e prontuario
		$campus = substr ( $username, 0, 2 );
		$prontuario = substr ( $username, 2 );
		
		error_reporting ( 0 );
		$connection = AutenticaWS::autenticar ( $host, $campus, $prontuario, $password );
		error_reporting ( $CFG->debug );
		
		if ($connection) {
			return true;
		}
		
		return false; // No matches found
	}
	
	/**
	 * Returns true if this authentication plugin is 'internal'.
	 *
	 * @return bool
	 */
	function is_internal() {
		return false;
	}
	
	/**
	 * Returns true if this authentication plugin can change the user's
	 * password.
	 *
	 * @return bool
	 */
	function can_change_password() {
		return ! empty ( $this->config->changepasswordurl );
	}
	
	/**
	 * Returns the URL for changing the user's pw, or false if the default can
	 * be used.
	 *
	 * @return moodle_url
	 */
	function change_password_url() {
		if (! empty ( $this->config->changepasswordurl )) {
			return new moodle_url ( $this->config->changepasswordurl );
		} else {
			return null;
		}
	}
	
	/**
	 * Prints a form for configuring this authentication plugin.
	 *
	 * This function is called from admin/auth.php, and outputs a full page with
	 * a form for configuring this plugin.
	 *
	 * @param array $page
	 *        	An object containing all the data for this page.
	 */
	function config_form($config, $err, $user_fields) {
		global $OUTPUT;
		
		include "config.html";
	}
	
	/**
	 * Processes and stores configuration data for this authentication plugin.
	 */
	function process_config($config) {
		// set to defaults if undefined
		if (! isset ( $config->host )) {
			$config->host = 'http://ws.ifsp.edu.br/servicoAutenticarLDAP';
		}
		if (! isset ( $config->changepasswordurl )) {
			$config->changepasswordurl = '';
		}
		
		// save settings
		set_config ( 'host', $config->host, 'auth/wsifsp' );
		set_config ( 'changepasswordurl', $config->changepasswordurl, 'auth/wsifsp' );
		
		return true;
	}
}
