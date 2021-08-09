<?php

/**
 * Privacy Subsystem implementation for tool_uploadquestion.
 *
 * @package     tool_uploadquestion
 * @copyright   2021 Terus e-Learning <khairu@teruselearning.co.uk>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_uploadquestion\privacy;

defined('MOODLE_INTERNAL') || die();

class provider implements \core_privacy\local\metadata\null_provider {
    /**
     * Get the language string identifier with the component's language
     * file to explain why this plugin stores no data.
     *
     * @return  string
     */
    public static function get_reason() : string {
        return 'privacy:metadata';
    }
}