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
 * Admin settings and defaults.
 *
 * @package gradeexport_nambei
 * @copyright  2017 IFSP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $warning = $OUTPUT->notification(get_string('setwsurlwarning', 'gradeexport_nambei'), 'warning');
    $settings->add(new admin_setting_heading('gradeexport_nambei/pluginname', '', $warning));

    $settings->add(new admin_setting_configtext('ws_nambei_digitanotas_url',
                    get_string('urlnambei', 'gradeexport_nambei'),
                    get_string('urlnambeidesc', 'gradeexport_nambei'), 'https://suap.ifsp.edu.br/accounts/logout', PARAM_URL));

}
