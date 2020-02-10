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
 * Custom Site Links block configuration form definition
 *
 * @package   block_custom_profile_links
 * @copyright Veronica Bermegui
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/lib/formslib.php');

define('DEFAULT_NUMBER_ICON_LINKS', 1);
define('DEFAULT_NUMBER_TEXT_LINKS', 1);

/**
 * Edit form class
 *
 * @package   block_custom_profile_links
 * @copyright Veronica Bermegui
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_custom_profile_links_edit_form extends block_edit_form {

    /**
     * Form definition
     *
     * @param \moodleform $mform
     * @return void
     */
    protected function specific_definition($mform) {

        /***********************
        * ICON LINKS
        ************************/
        $mform->addElement('header', 'configheader', get_string('iconlinksheader', 'block_custom_profile_links'));

        $repeatarray = array();
        $type = 'hidden';
        $name = 'config_iconlinkid';
        $value = get_string('arrayno', 'block_custom_profile_links');
        $repeatarray[] = &$mform->createElement($type, $name, $value);

        $type = 'filemanager';
        $name = 'config_iconlinkimage';
        $label = get_string('icon', 'block_custom_profile_links');
        $options = array('subdirs' => 0, 'maxbytes' => 5000000, 'maxfiles' => 1, 'accepted_types' => array('image'));
        $repeatarray[] = &$mform->createElement($type, $name, $label, null, $options);

        $type = 'text';
        $name = 'config_iconlinklabel';
        $label = get_string('label', 'block_custom_profile_links');
        $options = array('size' => '50');
        $repeatarray[] = &$mform->createElement($type, $name, $label, $options);

        $type = 'text';
        $name = 'config_iconlinkurl';
        $label = get_string('url');
        $options = array('size' => '50');
        $repeatarray[] = &$mform->createElement($type, $name, $label, $options);

        $type = 'text';
        $name = 'config_iconlinkcampusroles';
        $label = get_string('roles', 'block_custom_profile_links');
        $options = array('size' => '50');
        $repeatarray[] = &$mform->createElement($type, $name, $label, $options);

        $type = 'text';
        $name = 'config_iconlinkprofile';
        $label = get_string('profile', 'block_custom_profile_links');
        $options = array('size' => '50');
        $repeatarray[] = &$mform->createElement($type, $name, $label, $options);

        $type = 'advcheckbox';
        $name = 'config_iconlinkdelete';
        $label = get_string('delete');
        $desc = get_string('deletedesc', 'block_custom_profile_links');
        $options = array();
        $value = array(0, 1);
        $repeatarray[] = &$mform->createElement($type, $name, $label, $desc, $options, $value);

        $repeatcount = DEFAULT_NUMBER_ICON_LINKS;
        if ( isset($this->block->config->iconlinkid) ) {
            $countlinks = count($this->block->config->iconlinkid);
            if ( $countlinks > 0 ) {
                $repeatcount = $countlinks;
            }
        }

        $repeatoptions = array();

        $repeatoptions['config_iconlinkid']['type']     = PARAM_INT;
        $repeatoptions['config_iconlinklabel']['type']  = PARAM_RAW;
        $repeatoptions['config_iconlinkurl']['type']    = PARAM_RAW;
        $repeatoptions['config_iconlinktarget']['type'] = PARAM_INT;
        $repeatoptions['config_iconlinkcampusroles']['type']  = PARAM_TEXT;
        $repeatoptions['config_iconlinkprofile']['type']  = PARAM_TEXT;
        $repeatoptions['config_iconlinkdelete']['type'] = PARAM_INT;

        $repeatoptions['config_iconlinkimage']['rule']  = array(get_string('required'), 'required', null, 'server');
        $repeatoptions['config_iconlinklabel']['rule']  = array(get_string('required'), 'required', null, 'server');
        $repeatoptions['config_iconlinkurl']['rule']    = array(get_string('required'), 'required', null, 'server');
        $repeatoptions['config_iconlinkcampusroles']['rule']  = array(get_string('required'), 'required', null, 'server');
        $repeatoptions['config_iconlinkprofile']['rule']  = array(get_string('required'), 'required', null, 'server');;

        $repeatoptions['config_iconlinkimage']['disabledif']   = array('config_iconlinkdelete', 'checked');
        $repeatoptions['config_iconlinklabel']['disabledif']   = array('config_iconlinkdelete', 'checked');
        $repeatoptions['config_iconlinkurl']['disabledif']     = array('config_iconlinkdelete', 'checked');

        $repeatoptions['config_iconlinkcampusroles']['disabledif']   = array('config_iconlinkdelete', 'checked');
        $repeatoptions['config_iconlinkprofile']['disabledif']   = array('config_iconlinkdelete', 'checked');

        $this->repeat_elements($repeatarray, $repeatcount, $repeatoptions, 'iconlink_repeats', 'iconlink_add_fields',
            1, get_string('addnewiconlink', 'block_custom_profile_links'), true);
    }

    /**
     * Return submitted data.
     *
     * @return object submitted data.
     */
    public function get_data() {
        $data = parent::get_data();

        if ($data) {
            // Remove deleted icon links before saving data.
            if ( !empty($data->config_iconlinkdelete) ) {
                foreach ($data->config_iconlinkdelete as $i => $del) {
                    if ($del) {
                        $this->delete_array_element($data->config_iconlinkid, $i);
                        $this->delete_array_element($data->config_iconlinkimage, $i);
                        $this->delete_array_element($data->config_iconlinklabel, $i);
                        $this->delete_array_element($data->config_iconlinkurl, $i);
                        $this->delete_array_element($data->config_iconlinkcampusroles, $i);
                        $this->delete_array_element($data->config_iconlinkprofile, $i);
                    }
                }
                // Dont need delete array anymore.
                $data->config_iconlinkdelete = array();

                // Reindex arrays.
                $data->config_iconlinkid = array_values($data->config_iconlinkid);
                $data->config_iconlinkimage = array_values($data->config_iconlinkimage);
                $data->config_iconlinklabel = array_values($data->config_iconlinklabel);
                $data->config_iconlinkurl = array_values($data->config_iconlinkurl);
                $data->config_iconlinkcampusroles = array_values($data->config_iconlinkcampusroles);
                $data->config_iconlinkprofile = array_values($data->config_iconlinkprofile);
            }

            // Save images.
            if ( !empty($data->config_iconlinkimage) ) {

                foreach ($data->config_iconlinkimage as $i => $image) {
                    file_save_draft_area_files($image, $this->block->context->id, 'block_custom_profile_links',
                        'icons', $i);
                }
            }

        }

        return $data;
    }

    /**
     * Set form data.
     *
     * @param array $defaults
     * @return void
     */
    public function set_data($defaults) {
        if (isset($this->block->config->iconlinkimage)) {
            foreach ($this->block->config->iconlinkimage as $i => $draftitemid) {
                $newdraftitemid = ''; // Empty string force creates a new area and copy existing files into.

                // Fetch the draft file areas. On initial load this is empty and new draft areas are created.
                // On subsequent loads the draft areas are retreived.
                if (isset($_REQUEST['config_iconlinkimage'][$i])) {
                    $newdraftitemid = $_REQUEST['config_iconlinkimage'][$i];
                }

                // Copy all the files from the 'real' area, into the draft areas.
                file_prepare_draft_area($newdraftitemid, $this->block->context->id, 'block_custom_profile_links',
                    'icons', $i, array('subdirs' => true));
                $this->block->config->iconlinkimage[$i] = $newdraftitemid;
            }
        }

        // Set form data.
        parent::set_data($defaults);
    }

    /**
     * Remove fields not required if delete link is selected.
     *
     * @return void
     */
    public function definition_after_data() {
        if (!isset($this->_form->_submitValues['config_iconlinkdelete'])) {
            return;
        }
        foreach ($this->_form->_submitValues['config_iconlinkdelete'] as $i => $del) {
            // Remove the rules for the deleted link so that error is not triggered.
            if ($del) {
                unset($this->_form->_rules["config_iconlinklabel[${i}]"]);
                unset($this->_form->_rules["config_iconlinkurl[${i}]"]);
                unset($this->_form->_rules["config_iconlinkcampusroles[${i}]"]);
                unset($this->_form->_rules["config_iconlinkprofile[${i}]"]);
                unset($this->_form->_rules["config_iconlinkimage[${i}]"]);
            }
        }
    }

    /**
     * Helper to delete array element
     *
     * @param array $array
     * @param mixed $index
     * @return void
     */
    private function delete_array_element(&$array, $index) {
        // Unset element and shuffle everything down.
        if (isset($array[$index])) {
            unset($array[$index]);
        }
        if (empty($array)) {
            $array = array();
        }
    }
}