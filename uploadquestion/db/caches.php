<?php

/**
 * Cache definitions.
 *
 * @package     tool_uploadquestion
 * @category    upgrade
 * @copyright   2021 Terus e-Learning <khairu@teruselearning.co.uk>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$definitions = array(
    'helper' => array(
        'mode' => cache_store::MODE_REQUEST,
    )
);
