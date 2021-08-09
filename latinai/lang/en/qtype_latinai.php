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
 * OU latinai question type language strings.
 *
 * @package   qtype_latinai
 * @copyright  2021 Terus E-Learning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['answer'] = 'Answer: {$a}';
$string['answermustbegiven'] = 'You must enter an answer if there is a grade or feedback.';
$string['correctanswers'] = 'Correct answers';
$string['pleaseenterananswer'] = 'Please enter an answer.';
$string['pluginname'] = 'Latin AI';
$string['pluginname_help'] = 'In response to a question the respondent types a short phrase. There may be several possible correct answers, each with a different grade, the answer will be checked via Latin AI Comparation Service';
$string['pluginname_link'] = 'question/type/latinai';
$string['pluginnameadding'] = 'Adding a Latin AI question';
$string['pluginnameediting'] = 'Editing a Latin AI question';
$string['pluginnamesummary'] = 'Use AI Latin Service Api to check the translation answer';
$string['testquestionformsubmit'] = 'Test the question using these responses';
$string['testquestionformtitle'] = 'Latin AI question testing tool';
$string['testquestionheader'] = 'Testing question: {$a}';
$string['testquestionresponse'] = 'Response';
$string['testthisquestion'] = 'Test this question';
$string['answerno'] = 'Answer {$a}';
$string['addmoreanswerblanks'] = 'Blanks for {no} More Answers';
$string['latincomparasion'] = "Latin AI Service Endpoint";
$string['latincomparasionkey'] = "API Key";
$string['configcomparationendpoint'] = "Latin translation comparison Service URL.";
$string['configcomparationendpointkey'] = "Latin translation comparison Service API Key For Authentication";
$string['configcomparationnonai'] = "Use Non AI Comparation";
$string['configcomparationnonai_desc'] = "Use Smith-Waterman algorithm, Smith-Waterman is a dynamic programming algorithm. As such, it has the desirable property that it is guaranteed to find the optimal local alignment with respect to the scoring system being used (which includes the substitution matrix and the gap-scoring scheme).";
$string['filloutoneanswer'] = 'Use Latin AI Service to describe correct answers. You must provide at least one possible answer. Answers left blank will not be used. The first matching answer will be used to determine the score and feedback.';