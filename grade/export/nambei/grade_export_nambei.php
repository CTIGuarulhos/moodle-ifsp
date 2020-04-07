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

require_once($CFG->dirroot.'/grade/export/lib.php');
require_once('classes/servicoDigitaNotas/ServicoDigitaNotas.php');

class grade_export_nambei extends grade_export {

    public $plugin = 'nambei';

    /**
     * @var NotaAlunoWSList $notas
     * @access public
     */
    public $listaNotas;
    
    /**
     * @var ServicoDigitaNotas $digitaNotas
     * @access public
     */
    public $digitaNotas;

    /**
     * @var stdClass $dadosForm
     * @access private
     */
    private $dadosForm;

    /**
     * Constructor should set up all the private variables ready to be pulled
     * @param object $course
     * @param int $groupid id of selected group, 0 means all
     * @param stdClass $formdata The validated data from the grade export form.
     */
    public function __construct($course, $groupid, $formdata) {
        parent::__construct($course, $groupid, $formdata);

        // Overrides.
        $this->usercustomfields = true;

        //Inicializa objeto listaNotas
        $this->listaNotas = new NotaAlunoWSList();

        $this->dadosForm = $formdata;

        
    }

    /**
     * To be implemented by child classes
     */
    public function print_grades() {
        global $CFG;
        require_once($CFG->dirroot.'/lib/excellib.class.php');


        $export_tracking = $this->track_exports();

        $strgrades = get_string('grades');

        // Calculate file name
        $shortname = format_string($this->course->shortname, true, array('context' => context_course::instance($this->course->id)));

        $downloadfilename = clean_filename("$shortname $strgrades.nambei");
        
        // Creating a workbook
        $workbook = new MoodleExcelWorkbook("-");
        // Sending HTTP headers
        $workbook->send($downloadfilename);
        // Adding the worksheet
        $myxls = $workbook->add_worksheet($strgrades);

        // Print names of all the fields
        $profilefields = grade_helper::get_user_profile_fields($this->course->id, $this->usercustomfields);

        //Coloca os titulos da planilha da parte dos dados do usuario
        foreach ($profilefields as $id => $field) {
            $myxls->write_string(0, $id, $field->fullname);
        }

        //Define a posicao atual do numero de colunas baseado na quantidade de campos impressos por profilefields
        $pos = count($profilefields);

        //Se onlyactive for true vai mostrar mais uma coluna indiando se o usuario esta ativo ou não
        if (!$this->onlyactive) {
            $myxls->write_string(0, $pos++, get_string("suspended"));
        }

        //Vai colocar o nome das colunas das notas dos usuario
        $grade_item = $this->columns[$this->dadosForm->exportedgrade];
        $myxls->write_string(0, $pos++, $this->format_column_name($grade_item, false, 'Nota'));

        // Add a column_feedback column
        if ($this->export_feedback) {
            $myxls->write_string(0, $pos++, $this->format_column_name($grade_item, true));
        }

        // Last downloaded column header.
        // $myxls->write_string(0, $pos++, get_string('timeexported', 'gradeexport_nambei'));
        
        //TODO: PEgar String so Arquivo de Strings
        $myxls->write_string(0, $pos++, 'Nota Arredondada');
        $myxls->write_string(0, $pos++, 'Status');
        $myxls->write_string(0, $pos++, 'Mensagem');

        // Print all the lines of data.
        $i = 0;
        $geub = new grade_export_update_buffer();
        $gui = new graded_users_iterator($this->course, $this->columns, $this->groupid);
        $gui->require_active_enrolment($this->onlyactive);

        $gui->allow_user_custom_fields($this->usercustomfields);

        $gui->init();


        if(isset($CFG->ws_nambei_digitanotas_url)) {
            $this->digitaNotas = new ServicoDigitaNotas($CFG->ws_nambei_digitanotas_url,
            array( "trace" => 1, 
                "exception" => 1, 
                "encoding" => "UTF-8", 
                "login" => $this->dadosForm->ws_user, 
                "password" => base64_encode($this->dadosForm->ws_password))
            ); 
        }

        //Looping iterando pelos alunos da disciplina
        while ($userdata = $gui->next_user()) {
            $i++;
            $user = $userdata->user;

            //Objeto de Notas do aluno
            $notas = new NotaAlunoWS();

            $notas->falta = 0;

            $notas->ano = $this->dadosForm->ws_ano;
            $notas->campus = $this->dadosForm->ws_campus;
            $notas->codigoDisciplina = $this->dadosForm->ws_disciplina;
            $notas->flagDigitacaoNota = $this->dadosForm->ws_faltadigitar;
            
            $notas->prontuarioUsuario = $this->dadosForm->ws_responsavel;

            $notas->semestre =$this->dadosForm->ws_semestre;
            $notas->turma = $this->dadosForm->ws_turma;

            // $notas->eventoTod = $notas->ano.$notas->turma;
            $notas->eventoTod = $notas->turma;

            foreach ($profilefields as $id => $field) {
                $fieldvalue = grade_helper::get_user_field_value($user, $field);

                //Protuario do Aluno
                if($id == 2) {
                    //Remove os dois primeiros caracteres
                    $notas->prontuarioAluno = substr($fieldvalue,2);
                }

                // $myxls->write_string($i, $id, print_r($id, true). " - " . print_r($profilefields, true). " - " .$fieldvalue);
                $myxls->write_string($i, $id, $fieldvalue);
            }

            $j = count($profilefields);
            if (!$this->onlyactive) {
                $issuspended = ($user->suspendedenrolment) ? get_string('yes') : '';
                $myxls->write_string($i, $j++, $issuspended);
            }

            $grateObj = $userdata->grades[$this->dadosForm->exportedgrade];

            if ($export_tracking) {
                $status = $geub->track($grateObj);
            }

            //arredonda notas
            // $grade = round($grade, 1, PHP_ROUND_HALF_UP);

            $gradestr = $this->format_grade($grateObj, 1);
            
            //Se a nota for do ID selecionado ela sera usada na exportacao
            $notas->setNota($gradestr); //= round($gradestr, 1, PHP_ROUND_HALF_UP);

            //Debug, mostra os IDs das notas na planilha
            // $myxls->write_string($i, $j++, print_r($itemid, true). " - " . print_r($gradedisplayconst, true). " - " . $gradestr);

            if (is_numeric($gradestr)) {
                $myxls->write_number($i, $j++, $gradestr);
            } else {
                $myxls->write_string($i, $j++, $gradestr);
            }
            //Imprime a Nota do Aluno
            $myxls->write_string($i, $j++, $notas->nota);

            // writing feedback if requested
            // if ($this->export_feedback) {
            //     $myxls->write_string($i, $j++, $this->format_feedback($userdata->feedbacks[$itemid]));
            // }

            // Time exported.
            // $myxls->write_string($i, $j++, time());
            
            // $myxls->write_string($i, $j++, print_r($notas, true));

            //Adiciona a nota a lista para exportar somente se a nota for valida
            if($notas->isValid()){
                // $this->listaNotas->adicionarNota();

                $this->listaNotas->notas[0] = $notas;

                if(isset($CFG->ws_nambei_digitanotas_url)) {

                    try {
                        $result = $this->digitaNotas->digitarNotasAlunos($this->dadosForm->ws_campus, $this->listaNotas);
                    } catch (Exception $e) {
                        throw new Exception('Falha ao Enviar notas do Aluno Prontuário: '.$notas.' a operação foi abortada! \n\n'.$e->getMessage());
                    }

                    if($result->sucesso) {
                        $myxls->write_string($i, $j++, 'Sucesso');
                    }
                    else {
                        $myxls->write_string($i, $j++, 'Falha');
                    }

                    if($result->motivo) {
                        $myxls->write_string($i, $j++, $result->motivo);
                    }
                }
            }
            else {
                $myxls->write_string($i, $j++, 'Falha');
                $myxls->write_string($i, $j++, 'Dados Insuficientes');
            }
            // break;
        }

        // $myxls->write_string($i++, 0, print_r($this->dadosForm, true));

        $gui->close();
        $geub->close();

    /// Close the workbook
        $workbook->close();

        exit;
    }
}


