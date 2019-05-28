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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Open ID authentication.
 *
 * @package auth_oauth2
 * @copyright 2017 Damyon Wiese
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');
//require_once(dirname(__FILE__) . '/../../config.php');

/**
 * Plugin for oauth2ifsp authentication.
 *
 * @package auth_oauth2ifsp
 * @copyright 2017 IFSP
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
class auth_plugin_oauth2ifsp extends auth_plugin_base {
    
    function auth_plugin_oauth2ifsp() {
		$this->authtype = 'oauth2ifsp';
	}
    
    function logoutpage_hook() {
		global $CFG;
		global $redirect;
		if (isset($CFG->auth_oauth2ifsp_url_logout)) {
			$redirect = $CFG->auth_oauth2ifsp_url_logout;
		} else {
            redirect('https://suap.ifsp.edu.br/accounts/logout','Você será redirecionado para o SUAP.',5);
        }
    }    
    
}


