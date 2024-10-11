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
 * Build rubric table.
 *
 * @package   report_rubric_list
 * @copyright 2024 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_rubric_list;

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/tablelib.php");
require_once("$CFG->dirroot/grade/grading/form/guide/lib.php");

/**
 * Query database for rubrics and format output
 *
 * @package   report_rubric_list
 * @copyright 2024 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class table extends \table_sql {

    /**
     * Constructor
     * @param int $uniqueid all tables have to have a unique id, this is used
     *      as a key when storing table properties like sort order in the session.
     */
    public function __construct($uniqueid) {
        parent::__construct($uniqueid);
        // Define the list of columns to show.
        $columns = ['name', 'timemodified', 'modtype', 'module', 'status', 'course'];
        $this->define_columns($columns);

        // Define the titles of columns to show in header.
        $headers = [
            get_string('rubric', 'gradingform_rubric'),
            get_string('last_updated', 'report_rubric_list'),
            get_string('activity_type', 'report_rubric_list'),
            get_string('activity', 'report_rubric_list'),
            get_string('status'),
            get_string('course'),
        ];
        $this->define_headers($headers);
    }

    /**
     * This function is called for each data row to allow processing of the
     * rubric name value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return name of the rubric with link to grading area or name only
     *     when downloading.
     */
    protected function col_name($values) {
        // If the data is being downloaded than we don't want to show HTML.
        if ($this->is_downloading()) {
            return $values->name;
        } else {
            return \html_writer::link(
                new \moodle_url("/grade/grading/manage.php", ['areaid' => $values->areaid]),
                $values->name
            );
        }
    }

    /**
     * This function is called for each data row to allow processing of the
     * rubric status value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return string representation of rubric status.
     */
    protected function col_status($values) {
        $choices = [
            \gradingform_controller::DEFINITION_STATUS_DRAFT => 'statusdraft',
            \gradingform_controller::DEFINITION_STATUS_READY => 'statusready',
        ];
        return get_string($choices[$values->status], 'core_grading');
    }

    /**
     * This function is called for each data row to allow processing of the
     * course name value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return name of the course where the rubric is used with link to the course or name only
     *     when downloading.
     */
    protected function col_course($values) {
        // If the data is being downloaded than we don't want to show HTML.
        if ($this->is_downloading()) {
            return $values->course;
        } else {
            return \html_writer::link(new \moodle_url("/course/view.php", ['id' => $values->courseid]), $values->course);
        }
    }

    /**
     * This function is called for each data row to allow processing of the
     * modtype value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Returns the formatted name of the activity module type.
     */
    protected function col_modtype($values) {
        return get_string('pluginname', "mod_{$values->modtype}");
    }

    /**
     * This function is called for each data row to allow processing of the
     * module value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return name of the activity module associated with the rubric
     * with link to the module grading area or name only when downloading.
     */
    protected function col_module($values) {
        switch($values->modtype) {
            case 'assign':
                $id = $values->cmid;
                $name = $values->assignment;
                $url = "/mod/assign/view.php";
                break;
            case 'forum':
                $id = $values->cmid;
                $name = $values->forum;
                $url = "/mod/forum/view.php";
                break;
        }

        // If the data is being downloaded than we don't want to show HTML.
        if ($this->is_downloading()) {
            return $name;
        } else {
            return \html_writer::link(new \moodle_url($url, ['id' => $id]), $name);
        }
    }

    /**
     * This function is called for each data row to allow processing of the
     * timemodified value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return human readable date when the rubric was last modified.
     */
    protected function col_timemodified($values) {
        return userdate($values->timemodified);
    }
}
