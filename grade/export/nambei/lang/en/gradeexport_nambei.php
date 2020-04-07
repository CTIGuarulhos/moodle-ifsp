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
 * Strings for component 'gradeexport_nambei', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package   gradeexport_nambei
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['eventgradeexported'] = 'nambei grade exported';
$string['pluginname'] = 'Nambei Export';

$string['nambeiexportformtitle'] = 'Configurações de Exportação';
$string['nambeiexportedgrade'] = 'Nota usada para exportar';

$string['wsuser'] = 'Usuário';
$string['wsuser_help'] = 'Usuário de acesso ao WebService do Nambei';

$string['wsresp'] = 'Prontuário do Professor';
$string['wsresp_help'] = 'Esse será usado para registrar o responsavel pela gravação das notas';

$string['wsdisc'] = 'Código da Disciplina';
$string['wsdisc_help'] = 'Código da Disciplina registrada no Nambei. Ex: LOPS4 ( Lógica Programável)';

$string['wscamp'] = 'Código do Câmpus';
$string['wscamp_help'] = 'SP sigla de 2 letras para São Paulo, GU sigla de Guarulhos, etc.';

$string['wssim'] = 'Sim';
$string['wsnao'] = 'Não';

$string['wsfaltadig'] = 'Falta Digitar Notas';
$string['wsfaltadig_help'] = 'Define se ainda falta digitar notas, se for escolhido \'Não\' as notas não podem ser reenviadas.';

$string['wsturma'] = 'Código da Turma';
$string['wsturma_help'] = 'Código da Turma da disciplina sendo exportada.';

$string['wspass'] = 'Senha';

$string['anoexp'] = 'Ano do diário';
$string['semestreexp'] = 'Semestre';

$string['setwsurlwarning'] = 'A URL do DigitaNotas precisa estar correta para a exportação funcionar corretamente.';
$string['urlnambei'] = 'Informe a URL do WebService DigitaNotas do Nambei';
$string['urlnambeidesc'] = 'Essa URL sera utilizada para exportação de dados através do plugin Nambei Export';

$string['error_req'] = 'Campo Obrigatório';

$string['nambeiexport'] = 'Exportar';

$string['privacy:metadata'] = 'The Nambei Export plugin does not store any personal data, it however sends the grades and identificaiton info to the Nambei server';
$string['timeexported'] = 'Last downloaded from this course';
$string['nambei:publish'] = 'Publish nambei grade export';
$string['nambei:view'] = 'Use Nambei grade export';
