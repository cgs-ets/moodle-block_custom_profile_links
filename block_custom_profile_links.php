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
     * Responsive icon and text links list with visibilty based on user profile fields.
     *
     * @package   block_custom_profile_links
     * @copyright Veronica Bermegui
     * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
defined('MOODLE_INTERNAL') || die();

    /**
     * Block Custom profile Links class definition.
     *
     * @package    block_custom_profile_links
     * @copyright  Veronica Bermegui
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
class block_custom_profile_links extends block_base {

        /**
         * Core function used to initialize the block.
         */
    public function init() {
        $this->title = get_string('title', 'block_custom_profile_links');
    }

        /**
         * Controls whether multiple instances of the block are allowed on a page
         *
         * @return bool
         */
    public function instance_allow_multiple() {
            return false;
    }

        /**
         * Controls whether the block is configurable
         *
         * @return bool
         */
    public function instance_allow_config() {
            return true;
    }

        /**
         * Defines where the block can be added
         *
         * @return array
         */
    public function applicable_formats() {

            return array(
                'course-view' => false,
                'mod' => false,
                'my' => false,
                'site' => false,
                'user-profile' => true,
            );
    }

    public function has_config() {
            return true;
    }


        /**
         * Used to generate the content for the block.
         * @return object
         */
    public function get_content() {
            global $USER, $OUTPUT, $DB, $PAGE, $COURSE;

            // Determing which user role we are rendering to.
            // This block assumes users have custom profile fields for CampusRoles.
            // Load user profile.
            $userroles = array();
            $userprofile = '';
            $allowed = true;
            $profiles = '';

        if (null !== $PAGE->url->get_param('id')) {
            $userprofile = $DB->get_record('user', ['id' => $PAGE->url->get_param('id')]);

            profile_load_custom_fields($userprofile);
            $profiles = explode(',', $userprofile->profile['CampusRoles']);
        }

        if (isset($USER->profile['CampusRoles'])) {
            $userroles = explode(',', $USER->profile['CampusRoles']);
        }

            // If content has already been generated, don't waste time generating it again.
        if ($this->content !== null) {
                return $this->content;
        }
            $this->content = new stdClass;
            $this->content->text = '';
            $this->content->footer = '';
            $iconimages = array();

            $instanceid = $DB->get_record('block_instances', ['parentcontextid' => 1, 'blockname' => get_string('blockname', 'block_custom_profile_links')]);
            $contextid = $DB->get_record('context', ['instanceid' => $instanceid->id]);

            $fs = get_file_storage();

            $files = $fs->get_area_files($contextid->id, 'block_custom_profile_links', 'icons');

        foreach ($files as $file) {
            $id = $file->get_contenthash();
            $filename = $file->get_filename();
            if ($filename <> '.') {

                    $src = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(),
                                    $file->get_itemid(), $file->get_filepath(), $filename);
                    $iconimages[] = $src;
            }
        }

            $data = [
                'instanceid' => $this->instance->id,
                'iconlinks' => array(),
                'textlinks' => array(),
                'linktypes' => '',
                'linknumber' => '',
            ];

            if (isset($this->config->iconlinkurl)) {
                foreach ($this->config->iconlinkurl as $i => $url) {
                    if ($url == '') {
                        continue;
                    }

                    if (isset($userprofile->profile)) {
                        $url = str_replace('[username]', $userprofile->username, $url);
                        $allowed = ($PAGE->pagetype == "user-profile") &&
                                $this->checkrollesallowed($this->config->iconlinkcampusroles[$i], $userroles) &&
                                $this->checkprofilesallowed($this->config->iconlinkprofile[$i], $profiles);
                    }

                    if ($allowed) {
                        $icon = isset($iconimages[$i]) ? $iconimages[$i] : '';
                        $label = isset($this->config->iconlinklabel[$i]) ?
                                    $this->config->iconlinklabel[$i] : '';
                        $target = isset($this->config->iconlinktarget[$i]) ?
                                    '_blank' : '';
                        $data['iconlinks'][] = [
                            'icon' => $icon,
                            'label' => $label,
                            'url' => $url,
                            'target' => '_blank',
                        ];
                    }
                }
            }

            if (!empty($data['iconlinks'])) {
                $data['linktypes'] = 'types-one types-icons';
            }

            // Render links if any.
            if ($data['linktypes'] != '') {
                $this->content->text = $OUTPUT->render_from_template('block_custom_profile_links/content', $data);
            }

            return $this->content;
    }

    public function checkrollesallowed($linkroles, $userroles) {

        $linkrolesarr = array_map('trim', explode(',', $linkroles));
        $rolesallowed = array_intersect($userroles, $linkrolesarr);
        $userrolesstr = implode(',', $userroles);

        if ($linkroles == "*" || $rolesallowed || is_siteadmin()) {
            return true;
        }
            // Do regex checks.
        foreach ($linkrolesarr as $reg) {
            $regex = "/${reg}/i";
            if ($reg && (preg_match($regex, $userrolesstr) === 1)) {
                return true;
            }
        }
            return false;
    }

    public function checkprofilesallowed($linkprofiles, $userprofile) {

        $linkprofilesarr = array_map('trim', explode(',', $linkprofiles));
        $profilesallowed = array_intersect($userprofile, $linkprofilesarr);
        $userprofilesstr = implode(',', $userprofile);

        if ($linkprofiles == "*" || $profilesallowed || is_siteadmin()) {
            return true;
        }
           // Do regex checks.
        foreach ($linkprofilesarr as $reg) {
            $regex = "/${reg}/i";
            if ($reg && (preg_match($regex, $userprofilesstr) === 1)) {
                return true;
            }
        }
            return false;
    }
}
