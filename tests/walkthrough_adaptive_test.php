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

namespace qtype_stack;

use qtype_stack_walkthrough_test_base;
use stack_ast_container;
use stack_boolean_input;
use stack_input_factory;
use stack_potentialresponse_node;
use stack_potentialresponse_tree_lite;
use question_state;
use question_pattern_expectation;
use question_no_pattern_expectation;
use question_display_options;
use stdClass;
use test_question_maker;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/questionlib.php');
require_once(__DIR__ . '/fixtures/test_base.php');

/**
 * Unit tests for the Stack question type.
 *
 * @package    qtype_stack
 * @copyright 2012 The Open University.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 * @group qtype_stack
 * @covers \qtype_stack
 */
final class walkthrough_adaptive_test extends qtype_stack_walkthrough_test_base {

    public function test_test0_validate_then_submit_right_first_time(): void {

        // Create the stack question 'test0'.
        $q = \test_question_maker::make_question('stack', 'test0');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->assertEquals('adaptivemultipart',
                $this->quba->get_question_attempt($this->slot)->get_behaviour_name());
        $this->render();

        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                new question_pattern_expectation('/What is/'),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );

        // Process a validate request.
        $this->process_submission(['ans1' => '2', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();

        $expected = 'Seed: 1; ans1: 2 [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '2');
        $this->check_output_contains_input_validation('ans1');
        // Since the answer is a number there are no variables.
        $this->check_output_does_not_contain_lang_string('studentValidation_listofvariables',
                'qtype_stack', '\( \left[ x \right]\)');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submit of the correct answer.
        $this->process_submission(['ans1' => '2', 'ans1_val' => '2', '-submit' => 1]);

        // Verify.
        $this->check_current_state(question_state::$complete);
        $this->check_current_mark(1);
        $this->check_prt_score('firsttree', 1, 0);
        $this->render();

        $expected = 'Seed: 1; ans1: 2 [score]; firsttree: # = 1 | firsttree-1-T';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '2');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('firsttree');
        $this->check_output_does_not_contain_stray_placeholders();
    }

    public function test_test0_validate_then_submit_right_first_time_with_forceclean(): void {

        global $CFG;
        // Turn on the forceclean.
        $CFG->forceclean = true;

        // Create the stack question 'test0'.
        $q = \test_question_maker::make_question('stack', 'test0');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->assertEquals('adaptivemultipart',
                $this->quba->get_question_attempt($this->slot)->get_behaviour_name());
        $this->render();

        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                new question_pattern_expectation('/What is/'),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );

        // Process a validate request.
        $this->process_submission(['ans1' => '2', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();

        $expected = 'Seed: 1; ans1: 2 [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '2');
        $this->check_output_contains_input_validation('ans1');
        // Since the answer is a number there are no variables.
        $this->check_output_does_not_contain_lang_string('studentValidation_listofvariables',
                'qtype_stack', '\( \left[ x \right]\)');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submit of the correct answer.
        $this->process_submission(['ans1' => '2', 'ans1_val' => '2', '-submit' => 1]);

        // Verify.
        $this->check_current_state(question_state::$complete);
        $this->check_current_mark(1);
        $this->check_prt_score('firsttree', 1, 0);
        $this->render();

        $expected = 'Seed: 1; ans1: 2 [score]; firsttree: # = 1 | firsttree-1-T';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '2');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('firsttree');
        $this->check_output_does_not_contain_stray_placeholders();
    }

    public function test_test0_validate_then_submit_wrong_answer(): void {

        // Create the stack question 'test0'.
        $q = \test_question_maker::make_question('stack', 'test0');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        $this->render();

        // Process a validate request.
        $this->process_submission(['ans1' => '3', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);

        $this->render();
        $expected = 'Seed: 1; ans1: 3 [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '3');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submit of the correct answer.
        $this->process_submission(['ans1' => '3', 'ans1_val' => '3', '-submit' => 1]);

        // Verify.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        $this->check_prt_score('firsttree', 0, 0.3);
        $this->render();

        $expected = 'Seed: 1; ans1: 3 [score]; firsttree: # = 0 | ATEqualComAss (AlgEquiv-false). | firsttree-1-F';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '3');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('firsttree');
        $this->check_output_does_not_contain_stray_placeholders();
    }

    public function test_test0_validate_then_submit_question(): void {

        // Create the stack question 'test0'.
        $q = \test_question_maker::make_question('stack', 'test0');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        $this->render();

        // Process a validate request containing a variable.
        $this->process_submission(['ans1' => 'sin(x)', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();

        $expected = 'Seed: 1; ans1: sin(x) [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', 'sin(x)');
        $expectedvarlist = get_string('studentValidation_listofvariables',
                'qtype_stack', '\( \left[ x \right]\)');
        $this->assert_content_with_maths_contains($expectedvarlist, $this->currentoutput);
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a validate request.
        $this->process_submission(['ans1' => '1+1', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();

        $expected = 'Seed: 1; ans1: 1+1 [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '1+1');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submit of the correct but unsimplified answer.
        $this->process_submission(['ans1' => '1+1', 'ans1_val' => '1+1', '-submit' => 1]);

        // Verify.
        $expected = 'Seed: 1; ans1: 1+1 [score]; firsttree: # = 0 | ATEqualComAss (AlgEquiv-true). | firsttree-1-F';
        $this->check_response_summary($expected);
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        $this->check_prt_score('firsttree', 0, 0.3);
        $this->render();

        $expected = 'Seed: 1; ans1: 1+1 [score]; firsttree: # = 0 | ATEqualComAss (AlgEquiv-true). | firsttree-1-F';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '1+1');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('firsttree');
        $this->check_output_does_not_contain_stray_placeholders();
    }

    public function test_test0_validate_then_submit_check_pm(): void {

        // Create the stack question 'test0'.
        $q = \test_question_maker::make_question('stack', 'test0');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Process a validate request.
        $this->process_submission(['ans1' => '4+ -2', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $this->check_output_contains_text_input('ans1', '4+ -2');
        $this->check_output_contains_input_validation('ans1');
        // Since the answer is a number there are no variables.
        $this->check_output_does_not_contain_lang_string('studentValidation_listofvariables',
                'qtype_stack', '\( \left[ x \right]\)');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        $this->process_submission(['ans1' => '4+ -2', 'ans1_val' => '4+ -2', '-submit' => 1]);

        // Verify.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        $this->check_prt_score('firsttree', 0, 0.3);
        $this->render();
        $expected = 'Seed: 1; ans1: 4+ -2 [score]; firsttree: # = 0 | ATEqualComAss (AlgEquiv-true). | firsttree-1-F';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '4+ -2');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('firsttree');
        $this->check_output_does_not_contain_stray_placeholders();
    }

    public function test_test0_validate_reset_vars(): void {

        // Create the stack question 'test0'.
        $q = \test_question_maker::make_question('stack', 'test0');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->assertEquals('adaptivemultipart',
            $this->quba->get_question_attempt($this->slot)->get_behaviour_name());
        $this->render();

        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_pattern_expectation('/What is/'),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_does_not_contain_num_parts_correct(),
            $this->get_no_hint_visible_expectation()
            );

        // Process a validate request.
        $this->process_submission(['ans1' => 'simplify(e^(pi*i))', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();

        $expected = 'Seed: 1; ans1: simplify(e^(pi*i)) [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', 'simplify(e^(pi*i))');
        // From the existance of e and i we can infer the e^(pi*i) has not been simplified to -1.
        $expectedvarlist = get_string('studentValidation_listofvariables',
            'qtype_stack', '\( \left[ e , i \right]\)');
        $this->assert_content_with_maths_contains($expectedvarlist, $this->currentoutput);
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
    }

    public function test_test0_validate_subscripts_questionvars(): void {

        // Create the stack question 'test0'.
        $q = \test_question_maker::make_question('stack', 'test0');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->assertEquals('adaptivemultipart',
            $this->quba->get_question_attempt($this->slot)->get_behaviour_name());
        $this->render();

        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_pattern_expectation('/What is/'),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_does_not_contain_num_parts_correct(),
            $this->get_no_hint_visible_expectation()
            );

        // Process an invalidate request.

        // This is invalid because the subscript "a" is also a question variable.
        // This is implemented in the 998_security.filter.php on line 485.
        // Or search for the language tag "stackCas_forbiddenVariable".
        $this->process_submission(['ans1' => 'x_a', '-submit' => 1]);

        $this->check_current_state(question_state::$invalid);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();

        $expected = 'Seed: 1; ans1: x_a [invalid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', 'x_a');
        // From the existance of e and i we can infer the e^(pi*i) has not been simplified to -1.
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
    }

    public function test_test1_validate_then_submit_right_first_time(): void {

        // Create the stack question 'test1'.
        $q = \test_question_maker::make_question('stack', 'test1');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->assertEquals('adaptivemultipart',
                $this->quba->get_question_attempt($this->slot)->get_behaviour_name());
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                new question_pattern_expectation('/Find/'),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );

        // @codingStandardsIgnoreStart

        /*
         * This is something we should change in a future version, not in v4.3.

        // Notice here we no longer get away with including single letter question variables in the answer.
        // This is a very welcome side effect of the new parser and cassesion2 logic.
        $this->process_submission(array('ans1' => '(v-a)^(n+1)/(n+1)+c', '-submit' => 1));

        $this->check_current_state(question_state::$invalid);
        $this->check_current_mark(null);
        $this->check_prt_score('PotResTree_1', null, null);
        $this->render();
        $this->check_output_contains_text_input('ans1', '(v-a)^(n+1)/(n+1)+c');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        */

        // @codingStandardsIgnoreEnd

        // Now use the correct answer.
        $ta = $q->get_correct_response();
        $sa = $ta['ans1'];
        $this->process_submission(['ans1' => $sa, '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('PotResTree_1', null, null);
        $this->render();

        $expected = 'Seed: 1; ans1: (x-7)^4/4+c [valid]; PotResTree_1: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', $sa);
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submit of the correct answer.
        $this->process_submission(['ans1' => $sa, 'ans1_val' => $sa, '-submit' => 1]);

        // Verify.
        $this->check_current_state(question_state::$complete);
        $this->check_current_mark(1);
        $this->check_prt_score('PotResTree_1', 1, 0);
        $this->render();

        $expected = 'Seed: 1; ans1: (x-7)^4/4+c [score]; PotResTree_1: # = 1 | ATInt_true. | PotResTree_1-0-1';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', $sa);
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('PotResTree_1');
        $this->check_output_does_not_contain_stray_placeholders();
    }

    public function test_test1_validate_wrong_validate_right_submit_right(): void {

        $q = \test_question_maker::make_question('stack', 'test1');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                new question_pattern_expectation('/Find/'),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );

        // Process a validate request.
        $ta = $q->get_correct_response();
        // Remove the constant of integration.
        $sa = substr($ta['ans1'], 0, strlen($ta['ans1']) - 2);

        $this->process_submission(['ans1' => $sa, '-submit' => 1]);

        $this->check_current_mark(null);
        $this->check_prt_score('PotResTree_1', null, null);
        $this->render();

        $expected = 'Seed: 1; ans1: (x-7)^4/4 [valid]; PotResTree_1: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', $sa);
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submit, but with a changed answer.
        $this->process_submission(['ans1' => $sa . '+c', 'ans1_val' => $sa, '-submit' => 1]);

        $this->check_current_mark(null);
        $this->check_prt_score('PotResTree_1', null, null);
        $this->render();

        $expected = 'Seed: 1; ans1: (x-7)^4/4+c [valid]; PotResTree_1: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', $sa . '+c');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submit with the correct answer.
        $this->process_submission(['ans1' => $sa . '+c', 'ans1_val' => $sa . '+c', '-submit' => 1]);

        // Verify.
        $this->check_current_mark(1);
        $this->check_prt_score('PotResTree_1', 1, 0);
        $this->render();

        $expected = 'Seed: 1; ans1: (x-7)^4/4+c [score]; PotResTree_1: # = 1 | ATInt_true. | PotResTree_1-0-1';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', $sa . '+c');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('PotResTree_1');
        $this->check_output_does_not_contain_stray_placeholders();
    }

    public function test_test1_invalid_valid_but_wrong_with_specific_feedback(): void {

        // Create a stack question.
        $q = \test_question_maker::make_question('stack', 'test1');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('PotResTree_1', null, null);
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                new question_pattern_expectation('/Find/'),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );

        // Process a validate request.
        $ia = '((x-7)^4';

        $this->process_submission(['ans1' => $ia, '-submit' => 1]);

        $this->check_current_mark(null);
        $this->check_prt_score('PotResTree_1', null, null);
        $this->render();

        $expected = 'Seed: 1; ans1: ((x-7)^4 [invalid]; PotResTree_1: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', $ia);
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_pattern_expectation('/missing right/')
        );

        // Known incorrect answer.  Avoid relying on rand by giving explicit numbers in the question.
        $sa = '3*(x-7)^2';

        // Valid answer.
        $this->process_submission(['ans1' => $sa, '-submit' => 1]);

        $this->check_current_mark(null);
        $this->check_prt_score('PotResTree_1', null, null);
        $this->render();

        $expected = 'Seed: 1; ans1: 3*(x-7)^2 [valid]; PotResTree_1: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', $sa);
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Submit known mistake - look for specific feedback.
        $this->process_submission(['ans1' => $sa, 'ans1_val' => $sa, '-submit' => 1]);

        $this->check_current_mark(0);
        $this->check_prt_score('PotResTree_1', 0, 0.25);
        $this->render();

        $expected = 'Seed: 1; ans1: 3*(x-7)^2 [score]; PotResTree_1: # = 0 | ATInt_diff. | PotResTree_1-0-0';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', $sa);
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('PotResTree_1');
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_pattern_expectation('/differentiated instead!/')
        );
    }

    public function test_test1_invalid_student_uses_question_variables(): void {

        // Create a stack question.
        $q = \test_question_maker::make_question('stack', 'test1');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('PotResTree_1', null, null);
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                new question_pattern_expectation('/Find/'),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );

        // Process a validate request.
        // Invalid answer.
        $this->process_submission(['ans1' => 'ta', '-submit' => 1]);

        $this->check_current_mark(null);
        $this->check_prt_score('PotResTree_1', null, null);
        $this->render();

        $expected = 'Seed: 1; ans1: ta [invalid]; PotResTree_1: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', 'ta');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_pattern_expectation('/Forbidden variable/')
        );

        $this->process_submission(['ans1' => 'ta1', '-submit' => 1]);

        $this->check_current_mark(null);
        $this->check_prt_score('PotResTree_1', null, null);
        $this->render();

        $expected = 'Seed: 1; ans1: ta1 [invalid]; PotResTree_1: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', 'ta1');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_pattern_expectation('/Forbidden variable/')
        );
    }

    public function test_test0_invalid_student_uses_single_letter_question_variables(): void {

        // Create a stack question.
        $q = \test_question_maker::make_question('stack', 'test0');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                new question_pattern_expectation('/What is/'),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
                );

        // Process a validate request.
        // Invalid answer.
        $this->process_submission(['ans1' => 'a', '-submit' => 1]);

        $this->check_current_state(question_state::$invalid);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();

        $expected = 'Seed: 1; ans1: a [invalid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', 'a');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                new question_pattern_expectation('/Forbidden variable/')
                );
    }

    public function test_test0_invalid_student_uses_single_letter_question_variables_permitted(): void {

        // Create a stack question.
        $q = \test_question_maker::make_question('stack', 'test0');
        // Allow the question variable "a".
        $q->inputs['ans1']->set_parameter('allowWords', 'a');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                new question_pattern_expectation('/What is/'),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
                );

        // Process a validate request.
        // We have allowed the question variable "a", to this is not invalid.
        $this->process_submission(['ans1' => 'a', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();

        $expected = 'Seed: 1; ans1: a [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', 'a');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                new question_pattern_expectation('/What is/'),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
                );
    }

    public function test_test1_invalid_student_uses_forbidden_words(): void {

        // Create a stack question.
        $q = \test_question_maker::make_question('stack', 'test1');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('PotResTree_1', null, null);
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                new question_pattern_expectation('/Find/'),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );

        // Process a validate request.
        // Invalid answer.
        $this->process_submission(['ans1' => 'int((x-6)^4,x)', '-submit' => 1]);

        $this->check_current_mark(null);
        $this->check_prt_score('PotResTree_1', null, null);
        $this->render();

        $expected = 'Seed: 1; ans1: int((x-6)^4,x) [invalid]; PotResTree_1: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', 'int((x-6)^4,x)');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_pattern_expectation('/Forbidden function/')
        );
    }

    public function test_test1_invalid_student_uses_forbidden_words_fromlist(): void {

        // Create a stack question.
        $q = \test_question_maker::make_question('stack', 'test1');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('PotResTree_1', null, null);
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                new question_pattern_expectation('/Find/'),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );

        // Process a validate request.
        // Invalid answer.
        $this->process_submission(['ans1' => 'solve((x-6)^4,x)', '-submit' => 1]);

        $this->check_current_mark(null);
        $this->check_prt_score('PotResTree_1', null, null);
        $this->render();

        $expected = 'Seed: 1; ans1: solve((x-6)^4,x) [invalid]; PotResTree_1: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', 'solve((x-6)^4,x)');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                new question_pattern_expectation('/Forbidden function/')
        );

    }

    public function test_test1_valid_student_uses_allowed_words(): void {

        // Create a stack question.
        $q = \test_question_maker::make_question('stack', 'test1');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('PotResTree_1', null, null);
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                new question_pattern_expectation('/Find/'),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );

        // Process a validate request, with a function name from the allowwords list.
        $this->process_submission(['ans1' => 'popup(x^2+c)', '-submit' => 1]);

        $this->check_current_mark(null);
        $this->check_prt_score('PotResTree_1', null, null);
        $this->render();

        $expected = 'Seed: 1; ans1: popup(x^2+c) [valid]; PotResTree_1: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', 'popup(x^2+c)');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submit of the correct answer.
        $this->process_submission(['ans1' => 'popup(x^2+c)', 'ans1_val' => 'popup(x^2+c)', '-submit' => 1]);

        // Verify.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        $this->check_prt_score('PotResTree_1', 0, 0.25);
        $this->render();

        $expected = 'Seed: 1; ans1: popup(x^2+c) [score]; PotResTree_1: # = 0 | ATInt_generic. | PotResTree_1-0-0';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', 'popup(x^2+c)');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('PotResTree_1');
        $this->check_output_does_not_contain_stray_placeholders();

    }

    public function test_test1_valid_student_uses_allowed_words_casesensitivity(): void {

        // Normally "Sin(x)" is invalid and will give the feedback from 'stackCas_unknownFunctionCase'.
        // In this question we have included 'Sin' in the inputs allowed words.
        // Create a stack question.
        $q = \test_question_maker::make_question('stack', 'test1');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('PotResTree_1', null, null);
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                new question_pattern_expectation('/Find/'),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );

        // Process a validate request, with a function name not from the allowwords list.
        $this->process_submission(['ans1' => 'Cos(x^2+c)', '-submit' => 1]);

        $this->check_current_mark(null);
        $this->check_prt_score('PotResTree_1', null, null);
        $this->render();
        $this->check_output_contains_text_input('ans1', 'Cos(x^2+c)');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a validate request, with a function name from the allowwords list.
        $this->process_submission(['ans1' => 'Sin(x^2+c)', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('PotResTree_1', null, null);
        $this->render();

        $expected = 'Seed: 1; ans1: Sin(x^2+c) [valid]; PotResTree_1: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', 'Sin(x^2+c)');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submit of the correct answer.
        $this->process_submission(['ans1' => 'Sin(x^2+c)', 'ans1_val' => 'Sin(x^2+c)', '-submit' => 1]);

        // Verify.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        $this->check_prt_score('PotResTree_1', 0, 0.25);
        $this->render();

        $expected = 'Seed: 1; ans1: Sin(x^2+c) [score]; PotResTree_1: # = 0 | ATInt_generic. | PotResTree_1-0-0';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', 'Sin(x^2+c)');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('PotResTree_1');
        $this->check_output_does_not_contain_stray_placeholders();
    }

    public function test_test3_repeat_wrong_response_only_penalised_once(): void {

        // The scenario is this: (we use only the ans3 part of test3, leaving the others blank.)
        //
        // Resp.  State Try Raw mark Mark Penalty
        // 1. x   valid -   -        -    -
        // 2. x   score X   0.5      0.5  0.1
        // 3. x   score -   -        -    -
        // 4. x+1 valid -   -        -    -
        // 5. x+1 score X   0        0.5  0.1
        // 6. x   valid -   -        -    -
        // 7. x   score X   0.5      0.5  0.0
        // 8. 0   valid -   -        -    -
        // 9. 0   score X   1        0.8  0.0
        //
        // When reading this test, note that check_prt_score checks the score
        // returned by get_prt_result, which is the low-level routine that
        // calculates the score for the PRT given the response. The
        // ...->get_behaviour_var('_tries_oddeven') bit checks to see if the
        // behaviour acutally counted this response as a try.

        // Create a stack question.

        $q = \test_question_maker::make_question('stack', 'test3_penalty0_1');

        $this->start_attempt_at_question($q, 'adaptive', 4);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('oddeven', null, null);
        $this->assertNull($this->quba->get_question_attempt($this->slot)->get_last_step()->get_behaviour_var('_tries_oddeven'));
        $this->render();
        $this->check_output_contains_text_input('ans3');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                $this->get_does_not_contain_feedback_expectation()
        );

        // Validate ans3 => 'x'.
        $this->process_submission(['ans3' => 'x', '-submit' => 1]);

        $this->check_current_mark(null);
        $this->check_prt_score('oddeven', null, null);
        $this->assertNull($this->quba->get_question_attempt($this->slot)->get_last_step()->get_behaviour_var('_tries_oddeven'));
        $this->render();

        $expected = 'Seed: 1; ans3: x [valid]; odd: !; even: !; oddeven: !; unique: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans3', 'x');
        $this->check_output_contains_input_validation('ans3');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Score ans3 => 'x'.
        $this->process_submission(['ans3' => 'x', 'ans3_val' => 'x', '-submit' => 1]);

        $this->check_current_mark(0.5);
        $this->check_prt_score('oddeven', 0.5, 0.1);
        $this->assertEquals(1, $this->quba->get_question_attempt(
                $this->slot)->get_last_step()->get_behaviour_var('_tries_oddeven'));
        $this->render();

        $expected = 'Seed: 1; ans3: x [score]; odd: !; even: !; oddeven: # = 0.5 | oddeven-0-1 | oddeven-1-0; unique: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans3', 'x');
        $this->check_output_contains_input_validation('ans3');
        $this->check_output_contains_prt_feedback('oddeven');
        $this->check_output_does_not_contain_stray_placeholders();
        $this->assert_content_with_maths_contains('Your answer is not an even function. Look,'
                .' \[ f(x)-f(-x)={2\\cdot x} \neq 0.\]', $this->currentoutput);

        // Score ans3 => 'x'. Put in an ans1 to validate, to force the creation of a new step.
        $this->process_submission(['ans3' => 'x', 'ans3_val' => 'x', 'ans1' => 'x', '-submit' => 1]);

        $this->check_current_mark(0.5);
        $this->check_prt_score('oddeven', 0.5, 0.1);
        $this->assertNull($this->quba->get_question_attempt(
                $this->slot)->get_last_step()->get_behaviour_var('_tries_oddeven'));
        $this->render();

        $expected = 'Seed: 1; ans1: x [valid]; ans3: x [score]; odd: !; even: !; ' .
            'oddeven: # = 0.5 | oddeven-0-1 | oddeven-1-0; unique: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans3', 'x');
        $this->check_output_contains_input_validation('ans3');
        $this->check_output_contains_prt_feedback('oddeven');
        $this->check_output_does_not_contain_stray_placeholders();

        // Validate ans3 => 'x + 1'.
        $this->process_submission(['ans3' => 'x + 1', 'ans3_val' => 'x', '-submit' => 1]);

        $this->check_current_mark(0.5);
        $this->check_prt_score('oddeven', null, null);
        $this->assertNull($this->quba->get_question_attempt(
                $this->slot)->get_last_step()->get_behaviour_var('_tries_oddeven'));
        $this->render();
        $this->check_output_contains_text_input('ans3', 'x + 1');
        $this->check_output_contains_input_validation('ans3');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Score ans3 => 'x + 1'.
        $this->process_submission(['ans3' => 'x + 1', 'ans3_val' => 'x + 1', '-submit' => 1]);

        $this->check_current_mark(0.5);
        $this->check_prt_score('oddeven', 0, 0.1);
        $this->assertEquals(2, $this->quba->get_question_attempt(
                $this->slot)->get_last_step()->get_behaviour_var('_tries_oddeven'));
        $this->render();
        $this->check_output_contains_text_input('ans3', 'x + 1');
        $this->check_output_contains_input_validation('ans3');
        $this->check_output_contains_prt_feedback('oddeven');
        $this->check_output_does_not_contain_stray_placeholders();

        // Validate ans3 => 'x'.
        $this->process_submission(['ans3' => 'x', 'ans3_val' => 'x + 1', '-submit' => 1]);

        $this->check_current_mark(0.5);
        $this->check_prt_score('oddeven', null, null);
        $this->assertNull($this->quba->get_question_attempt(
                $this->slot)->get_last_step()->get_behaviour_var('_tries_oddeven'));
        $this->render();

        $expected = 'Seed: 1; ans3: x [valid]; odd: !; even: !; oddeven: !; unique: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans3', 'x');
        $this->check_output_contains_input_validation('ans3');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Score ans3 => 'x'.
        $this->process_submission(['ans3' => 'x', 'ans3_val' => 'x', '-submit' => 1]);

        $this->check_current_mark(0.5);
        $this->check_prt_score('oddeven', 0.5, 0.1);
        $this->assertEquals(3, $this->quba->get_question_attempt(
                $this->slot)->get_last_step()->get_behaviour_var('_tries_oddeven'));
        $this->render();

        $expected = 'Seed: 1; ans3: x [score]; odd: !; even: !; oddeven: # = 0.5 | oddeven-0-1 | oddeven-1-0; unique: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans3', 'x');
        $this->check_output_contains_input_validation('ans3');
        $this->check_output_contains_prt_feedback('oddeven');
        $this->check_output_does_not_contain_stray_placeholders();

        // Validate ans3 => '0'.
        $this->process_submission(['ans3' => '0', 'ans3_val' => 'x', '-submit' => 1]);

        $this->check_current_mark(0.5);
        $this->check_prt_score('oddeven', null, null);
        $this->assertNull($this->quba->get_question_attempt(
                $this->slot)->get_last_step()->get_behaviour_var('_tries_oddeven'));
        $this->render();

        $expected = 'Seed: 1; ans3: 0 [valid]; odd: !; even: !; oddeven: !; unique: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans3', '0');
        $this->check_output_contains_input_validation('ans3');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Score ans3 => '0'.
        $this->process_submission(['ans3' => '0', 'ans3_val' => '0', '-submit' => 1]);

        $this->check_current_mark(0.8);
        $this->check_prt_score('oddeven', 1, 0);
        $this->assertEquals(4, $this->quba->get_question_attempt(
                $this->slot)->get_last_step()->get_behaviour_var('_tries_oddeven'));
        $this->render();

        $expected = 'Seed: 1; ans3: 0 [score]; odd: !; even: !; oddeven: # = 1 | oddeven-0-1 | oddeven-1-1; unique: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans3', '0');
        $this->check_output_contains_input_validation('ans3');
        $this->check_output_contains_prt_feedback('oddeven');
        $this->check_output_does_not_contain_stray_placeholders();
    }

    public function test_test3_submit_and_finish_before_validating(): void {

        // Create a stack question.
        $q = \test_question_maker::make_question('stack', 'test3');
        $this->start_attempt_at_question($q, 'adaptive', 4);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('odd', null, null);
        $this->check_prt_score('even', null, null);
        $this->check_prt_score('oddeven', null, null);
        $this->check_prt_score('unique', null, null);
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_contains_text_input('ans2');
        $this->check_output_contains_text_input('ans3');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                $this->get_contains_select_expectation('ans4', stack_boolean_input::get_choices(), '', true),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );
        $this->assertNull($this->quba->get_response_summary($this->slot));

        // Save a partially correct, partially complete response.
        $this->process_submission(['ans1' => 'x^3', 'ans2' => 'x^2', 'ans3' => 'x', 'ans4' => '']);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('odd', null, null);
        $this->check_prt_score('even', null, null);
        $this->check_prt_score('oddeven', null, null);
        $this->check_prt_score('unique', null, null);
        $this->render();

        $expected = '';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', 'x^3');
        $this->check_output_contains_text_input('ans2', 'x^2');
        $this->check_output_contains_text_input('ans3', 'x');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_input_validation('ans2');
        $this->check_output_contains_input_validation('ans3');
        $this->check_output_does_not_contain_input_validation('ans4');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                $this->get_contains_select_expectation('ans4', stack_boolean_input::get_choices(), 'false', true),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );
        $this->assertNull($this->quba->get_response_summary($this->slot));

        // Submit all and finish before validating.
        $this->quba->finish_all_questions();

        $this->check_current_state(question_state::$gradedpartial);
        $this->check_current_mark(2.5);
        $this->check_prt_score('odd', 1, 0, true);
        $this->check_prt_score('even', 1, 0, true);
        $this->check_prt_score('oddeven', 0.5, 0.4, true);
        $this->check_prt_score('unique', null, null, true);
        $this->render();

        $expected = 'Seed: 1; ans1: x^3 [valid]; ans2: x^2 [valid]; ans3: x [valid]; odd: !; even: !; oddeven: !; unique: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', 'x^3', false);
        $this->check_output_contains_text_input('ans2', 'x^2', false);
        $this->check_output_contains_text_input('ans3', 'x', false);
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_input_validation('ans2');
        $this->check_output_contains_input_validation('ans3');
        $this->check_output_does_not_contain_input_validation('ans4');
        $this->check_output_contains_prt_feedback('odd');
        $this->check_output_contains_prt_feedback('even');
        $this->check_output_contains_prt_feedback('oddeven');
        $this->check_output_does_not_contain_prt_feedback('unique');
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                $this->get_contains_select_expectation('ans4', stack_boolean_input::get_choices(), '', false),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );
        $this->assertEquals('Seed: 1; ans1: x^3 [valid]; ans2: x^2 [valid]; ans3: x [valid]; ' .
                'odd: !; even: !; oddeven: !; unique: !',
                $this->quba->get_response_summary($this->slot));
    }

    public function test_test3_submit_wrong_response_correct_then_stubmit(): void {

        // Create a stack question.
        $q = \test_question_maker::make_question('stack', 'test3');
        $this->start_attempt_at_question($q, 'adaptive', 4);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_contains_text_input('ans2');
        $this->check_output_contains_text_input('ans3');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
        $this->get_contains_select_expectation('ans4', stack_boolean_input::get_choices(), '', true),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );

        // Try to submit a response:
        // 1. all parts wrong but valid.
        $this->process_submission(['ans1' => 'x^2', 'ans2' => 'x^3', 'ans3' => '1+x^3', 'ans4' => 'false', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        $this->render();

        $expected = 'Seed: 1; ans1: x^2 [valid]; ans2: x^3 [valid]; ans3: 1+x^3 [valid]; ans4: false [score]; ' .
            'odd: !; even: !; oddeven: !; unique: # = 0 | unique-0-0';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', 'x^2');
        $this->check_output_contains_text_input('ans2', 'x^3');
        $this->check_output_contains_text_input('ans3', '1+x^3');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_input_validation('ans2');
        $this->check_output_contains_input_validation('ans3');
        $this->check_output_does_not_contain_prt_feedback('odd');
        $this->check_output_does_not_contain_prt_feedback('even');
        $this->check_output_does_not_contain_prt_feedback('oddeven');
        $this->check_output_contains_prt_feedback('unique');
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                $this->get_contains_select_expectation('ans4', stack_boolean_input::get_choices(), 'false', true),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );

        // Submit again without editing.
        $this->process_submission([
            'ans1' => 'x^2', 'ans2' => 'x^3', 'ans3' => '1+x^3', 'ans4' => 'false',
            'ans1_val' => 'x^2', 'ans2_val' => 'x^3', 'ans3_val' => '1+x^3', '-submit' => 1,
        ]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        $this->render();

        $expected = 'Seed: 1; ans1: x^2 [score]; ans2: x^3 [score]; ans3: 1+x^3 [score]; ans4: false [score]; odd: # = 0 | ' .
            'odd-0-0; even: # = 0 | even-0-0; oddeven: # = 0 | oddeven-0-0 | oddeven-1-0; unique: # = 0 | unique-0-0';
        $this->check_response_summary($expected);
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_input_validation('ans2');
        $this->check_output_contains_input_validation('ans3');
        $this->check_output_contains_prt_feedback('odd');
        $this->check_output_contains_prt_feedback('even');
        $this->check_output_contains_prt_feedback('oddeven');
        $this->check_output_contains_prt_feedback('unique');
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                $this->get_contains_select_expectation('ans4', stack_boolean_input::get_choices(), 'false', true),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );
        $this->check_current_output(
                new question_pattern_expectation('/Incorrect answer./')
        );
        $this->check_current_output(
                new question_no_pattern_expectation('/Your answer is partially correct./')
        );
    }

    public function test_test3_save_invalid_response_correct_then_stubmit(): void {

        // Create a stack question.
        $q = \test_question_maker::make_question('stack', 'test3');
        $this->start_attempt_at_question($q, 'adaptive', 4);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_contains_text_input('ans2');
        $this->check_output_contains_text_input('ans3');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                $this->get_contains_select_expectation('ans4', stack_boolean_input::get_choices(), '', true),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );

        // Try to submit a response:
        // 1. right, not yet validated
        // 2. invalid
        // 3. right, not yet validated
        // 4. right, validation not required.
        $this->process_submission(['ans1' => 'x^3', 'ans2' => '(x +', 'ans3' => '0', 'ans4' => 'true', '-submit' => 1]);

        $this->check_current_state(question_state::$invalid);
        $this->check_current_mark(1.0);
        $this->render();

        $expected = 'Seed: 1; ans1: x^3 [valid]; ans2: (x + [invalid]; ans3: 0 [valid]; ans4: true [score]; odd: !; ' .
                'even: !; oddeven: !; unique: # = 1 | ATLogic_True. | unique-0-1';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', 'x^3');
        $this->check_output_contains_text_input('ans2', '(x +');
        $this->check_output_contains_text_input('ans3', '0');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_input_validation('ans2');
        $this->check_output_contains_input_validation('ans3');
        $this->check_output_does_not_contain_input_validation('ans4');
        $this->check_output_does_not_contain_prt_feedback('odd');
        $this->check_output_does_not_contain_prt_feedback('even');
        $this->check_output_does_not_contain_prt_feedback('oddeven');
        $this->check_output_contains_prt_feedback('unique');
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                $this->get_contains_select_expectation('ans4', stack_boolean_input::get_choices(), 'false', true),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );

        // Submit again without editing. 1. and 3. bits should now be graded.
        $this->process_submission([
            'ans1' => 'x^3', 'ans2' => '(x +', 'ans3' => '0', 'ans4' => 'true',
            'ans1_val' => 'x^3', 'ans3_val' => '0', '-submit' => 1,
        ]);

        $this->check_current_state(question_state::$invalid);
        $this->check_current_mark(3.0);
        $this->render();

        $expected = 'Seed: 1; ans1: x^3 [score]; ans2: (x + [invalid]; ans3: 0 [score]; ans4: true [score]; odd: # = 1 | ' .
                'odd-0-1; even: !; oddeven: # = 1 | oddeven-0-1 | oddeven-1-1; unique: # = 1 | ATLogic_True. | unique-0-1';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', 'x^3');
        $this->check_output_contains_text_input('ans2', '(x +');
        $this->check_output_contains_text_input('ans3', '0');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_input_validation('ans2');
        $this->check_output_contains_input_validation('ans3');
        $this->check_output_does_not_contain_input_validation('ans4');
        $this->check_output_contains_prt_feedback('odd');
        $this->check_output_does_not_contain_prt_feedback('even');
        $this->check_output_contains_prt_feedback('oddeven');
        $this->check_output_contains_prt_feedback('unique');
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                $this->get_contains_select_expectation('ans4', stack_boolean_input::get_choices(), 'false', true),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );

        // Now fix the response to 2. and submit. Previously invalid bit should only be validated, not graded yet.
        $this->process_submission([
            'ans1' => 'x^3', 'ans2' => 'x^2', 'ans3' => '0', 'ans4' => 'true',
            'ans1_val' => 'x^3', 'ans3_val' => '0', '-submit' => 1,
        ]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(3.0);
        $this->render();

        $expected = 'Seed: 1; ans1: x^3 [score]; ans2: x^2 [valid]; ans3: 0 [score]; ans4: true [score]; odd: # = 1 | ' .
                'odd-0-1; even: !; oddeven: # = 1 | oddeven-0-1 | oddeven-1-1; unique: # = 1 | ATLogic_True. | unique-0-1';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', 'x^3');
        $this->check_output_contains_text_input('ans2', 'x^2');
        $this->check_output_contains_text_input('ans3', '0');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_input_validation('ans2');
        $this->check_output_contains_input_validation('ans3');
        $this->check_output_does_not_contain_input_validation('ans4');
        $this->check_output_contains_prt_feedback('odd');
        $this->check_output_does_not_contain_prt_feedback('even');
        $this->check_output_contains_prt_feedback('oddeven');
        $this->check_output_contains_prt_feedback('unique');
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                $this->get_contains_select_expectation('ans4', stack_boolean_input::get_choices(), 'false', true),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );

        // Submit again. Should now all be graded (and right).
        $this->process_submission([
            'ans1' => 'x^3', 'ans2' => 'x^2', 'ans3' => '0', 'ans4' => 'true',
            'ans1_val' => 'x^3', 'ans2_val' => 'x^2', 'ans3_val' => '0', '-submit' => 1,
        ]);

        $this->check_current_state(question_state::$complete);
        $this->check_current_mark(4.0);
        $this->render();

        $expected = 'Seed: 1; ans1: x^3 [score]; ans2: x^2 [score]; ans3: 0 [score]; ans4: true [score]; odd: # = 1 | ' .
                'odd-0-1; even: # = 1 | even-0-1; oddeven: # = 1 | oddeven-0-1 | oddeven-1-1; unique: # = 1 | ' .
                'ATLogic_True. | unique-0-1';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', 'x^3');
        $this->check_output_contains_text_input('ans2', 'x^2');
        $this->check_output_contains_text_input('ans3', '0');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_input_validation('ans2');
        $this->check_output_contains_input_validation('ans3');
        $this->check_output_does_not_contain_input_validation('ans4');
        $this->check_output_contains_prt_feedback('odd');
        $this->check_output_contains_prt_feedback('even');
        $this->check_output_contains_prt_feedback('oddeven');
        $this->check_output_contains_prt_feedback('unique');
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                $this->get_contains_select_expectation('ans4', stack_boolean_input::get_choices(), 'true', true),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );

        // Submit all and finish - should update state from complete to gradedright.
        $this->quba->finish_all_questions();

        $this->check_current_state(question_state::$gradedright);
        $this->check_current_mark(4.0);
        $this->render();

        $expected = 'Seed: 1; ans1: x^3 [score]; ans2: x^2 [score]; ans3: 0 [score]; ans4: true [score]; odd: # = 1 | ' .
                'odd-0-1; even: # = 1 | even-0-1; oddeven: # = 1 | oddeven-0-1 | oddeven-1-1; unique: # = 1 | ' .
                'ATLogic_True. | unique-0-1';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', 'x^3', false);
        $this->check_output_contains_text_input('ans2', 'x^2', false);
        $this->check_output_contains_text_input('ans3', '0', false);
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_input_validation('ans2');
        $this->check_output_contains_input_validation('ans3');
        $this->check_output_does_not_contain_input_validation('ans4');
        $this->check_output_contains_prt_feedback('odd');
        $this->check_output_contains_prt_feedback('even');
        $this->check_output_contains_prt_feedback('oddeven');
        $this->check_output_contains_prt_feedback('unique');
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                $this->get_contains_select_expectation('ans4', stack_boolean_input::get_choices(), 'true', false),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );
    }

    public function test_test3_complex_scenario(): void {

        // @codingStandardsIgnoreStart
        /**
         * Here are the sequence of responses we are going to test. When
         * a particular PRT generates a grades, that is shown in brackets as
         * raw fraction - penalty.
         *
         *     odd         even        oddeven       unique     Mark so far
         *  1. x^3         -           x             -          -
         *  2. x^3 (1)     -           x   (0.5)     -          1.5
         *  3. (x          (x          x+1           F (0)      1.5
         *  4. x)          x^2         x+1 (0-0.1)   -          1.5
         *  5. x^2         x           x^5           -          1.5
         *  6. x^2 (0-0)   x^2         x^5 (0.5-0.2) T (1-0.1)  2.4
         *  7. x           x^2 (1)     x+3           T (1-0.1)  3.4
         *  8. x   (1-0.1) -           x+3 (0-0.3)   T (1-0.1)  3.4
         *  9. x^3         x^2         0             T (1-0.1)  3.4
         * 10. x^3 (1-0.1) x^2 (1-0.0) 0   (1-0.4)   T (1-0.1)  3.5
         *
         * Best mark
         *     1.0         1.0         0.6           0.9        3.5
         *
         * Hopefully this summary makes the following easier to understand.
         */
        // @codingStandardsIgnoreEnd

        // Create a stack question.
        $q = \test_question_maker::make_question('stack', 'test3_penalty0_1');
        $this->start_attempt_at_question($q, 'adaptive', 4);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_contains_text_input('ans2');
        $this->check_output_contains_text_input('ans3');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                $this->get_contains_select_expectation('ans4', stack_boolean_input::get_choices(), '', true),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );
        $this->assertNull($this->quba->get_response_summary($this->slot));

        // Step 1.
        $this->process_submission(['ans1' => 'x^3', 'ans2' => '', 'ans3' => 'x', 'ans4' => '', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->render();

        $this->check_output_contains_text_input('ans1', 'x^3');
        $this->check_output_contains_text_input('ans2', '');
        $this->check_output_contains_text_input('ans3', 'x');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_input_validation('ans2');
        $this->check_output_contains_input_validation('ans3');
        $this->check_output_does_not_contain_input_validation('ans4');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                $this->get_contains_select_expectation('ans4', stack_boolean_input::get_choices(), '', true),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );
        $this->assertEquals('Seed: 1; ans1: x^3 [valid]; ans3: x [valid]; odd: !; even: !; oddeven: !; unique: !',
                $this->quba->get_response_summary($this->slot));

        // Step 2.
        $this->process_submission([
            'ans1' => 'x^3', 'ans2' => '', 'ans3' => 'x', 'ans4' => '',
            'ans1_val' => 'x^3', 'ans3_val' => 'x', '-submit' => 1,
        ]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(1.5);
        $this->render();
        $this->check_output_contains_text_input('ans1', 'x^3');
        $this->check_output_contains_text_input('ans2', '');
        $this->check_output_contains_text_input('ans3', 'x');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_input_validation('ans2');
        $this->check_output_contains_input_validation('ans3');
        $this->check_output_does_not_contain_input_validation('ans4');
        $this->check_output_contains_prt_feedback('odd');
        $this->check_output_does_not_contain_prt_feedback('even');
        $this->check_output_contains_prt_feedback('oddeven');
        $this->check_output_does_not_contain_prt_feedback('unique');
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                $this->get_contains_select_expectation('ans4', stack_boolean_input::get_choices(), '', true),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );
        $this->assertEquals('Seed: 1; ans1: x^3 [score]; ans3: x [score]; '
                . 'odd: # = 1 | odd-0-1; even: !; oddeven: # = 0.5 | oddeven-0-1 | oddeven-1-0; unique: !',
                $this->quba->get_response_summary($this->slot));

        // Step 3.
        $this->process_submission([
            'ans1' => '(x', 'ans2' => '(x', 'ans3' => 'x+1', 'ans4' => 'false',
            'ans1_val' => 'x^3', 'ans3_val' => 'x', '-submit' => 1,
        ]);

        $this->check_current_state(question_state::$invalid);
        $this->check_current_mark(1.5);
        $this->render();
        $this->check_output_contains_text_input('ans1', '(x');
        $this->check_output_contains_text_input('ans2', '(x');
        $this->check_output_contains_text_input('ans3', 'x+1');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_input_validation('ans2');
        $this->check_output_contains_input_validation('ans3');
        $this->check_output_does_not_contain_input_validation('ans4');
        $this->check_output_does_not_contain_prt_feedback('odd');
        $this->check_output_does_not_contain_prt_feedback('even');
        $this->check_output_does_not_contain_prt_feedback('oddeven');
        $this->check_output_contains_prt_feedback('unique');
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                $this->get_contains_select_expectation('ans4', stack_boolean_input::get_choices(), 'false', true),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );
        $this->assertEquals('Seed: 1; ans1: (x [invalid]; ans2: (x [invalid]; ans3: x+1 [valid]; ans4: false [score]; '
                . 'odd: !; even: !; oddeven: !; unique: # = 0 | unique-0-0',
                $this->quba->get_response_summary($this->slot));

        // Step 4.
        $this->process_submission([
            'ans1' => 'x)', 'ans2' => 'x^2', 'ans3' => 'x+1', 'ans4' => '',
            'ans3_val' => 'x+1', '-submit' => 1,
        ]);

        $this->check_current_state(question_state::$invalid);
        $this->check_current_mark(1.5);
        $this->render();
        $this->check_output_contains_text_input('ans1', 'x)');
        $this->check_output_contains_text_input('ans2', 'x^2');
        $this->check_output_contains_text_input('ans3', 'x+1');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_input_validation('ans2');
        $this->check_output_contains_input_validation('ans3');
        $this->check_output_does_not_contain_input_validation('ans4');
        $this->check_output_does_not_contain_prt_feedback('odd');
        $this->check_output_does_not_contain_prt_feedback('even');
        $this->check_output_contains_prt_feedback('oddeven');
        $this->check_output_does_not_contain_prt_feedback('unique');
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                $this->get_contains_select_expectation('ans4', stack_boolean_input::get_choices(), '', true),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );
        $this->assertEquals('Seed: 1; ans1: x) [invalid]; ans2: x^2 [valid]; ans3: x+1 [score]; '
                .'odd: !; even: !; oddeven: # = 0 | oddeven-0-0 | oddeven-1-0; unique: !',
                $this->quba->get_response_summary($this->slot));

        // Step 5.
        $this->process_submission([
            'ans1' => 'x^2', 'ans2' => 'x', 'ans3' => 'x^5', 'ans4' => '',
            'ans2_val' => 'x^2', 'ans3_val' => 'x+1', '-submit' => 1,
        ]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(1.5);
        $this->render();
        $expected = 'Seed: 1; ans1: x^2 [valid]; ans2: x [valid]; ans3: x^5 [valid]; odd: !; even: !; oddeven: !; unique: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', 'x^2');
        $this->check_output_contains_text_input('ans2', 'x');
        $this->check_output_contains_text_input('ans3', 'x^5');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_input_validation('ans2');
        $this->check_output_contains_input_validation('ans3');
        $this->check_output_does_not_contain_input_validation('ans4');
        $this->check_output_does_not_contain_prt_feedback('odd');
        $this->check_output_does_not_contain_prt_feedback('even');
        $this->check_output_does_not_contain_prt_feedback('oddeven');
        $this->check_output_does_not_contain_prt_feedback('unique');
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                $this->get_contains_select_expectation('ans4', stack_boolean_input::get_choices(), '', true),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );
        $this->assertEquals('Seed: 1; ans1: x^2 [valid]; ans2: x [valid]; ans3: x^5 [valid]; '
                . 'odd: !; even: !; oddeven: !; unique: !',
                $this->quba->get_response_summary($this->slot));

        // Step 6.
        $this->process_submission([
            'ans1' => 'x^2', 'ans2' => 'x^2', 'ans3' => 'x^5', 'ans4' => 'true',
            'ans1_val' => 'x^2', 'ans2_val' => 'x', 'ans3_val' => 'x^5', '-submit' => 1,
        ]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(2.4);
        $this->render();
        $this->check_output_contains_text_input('ans1', 'x^2');
        $this->check_output_contains_text_input('ans2', 'x^2');
        $this->check_output_contains_text_input('ans3', 'x^5');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_input_validation('ans2');
        $this->check_output_contains_input_validation('ans3');
        $this->check_output_does_not_contain_input_validation('ans4');
        $this->check_output_contains_prt_feedback('odd');
        $this->check_output_does_not_contain_prt_feedback('even');
        $this->check_output_contains_prt_feedback('oddeven');
        $this->check_output_contains_prt_feedback('unique');
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                $this->get_contains_select_expectation('ans4', stack_boolean_input::get_choices(), 'true', true),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );
        $this->assertEquals('Seed: 1; ans1: x^2 [score]; ans2: x^2 [valid]; ans3: x^5 [score]; ans4: true [score]; '
                . 'odd: # = 0 | odd-0-0; even: !; oddeven: # = 0.5 | oddeven-0-1 | oddeven-1-0; ' .
                'unique: # = 1 | ATLogic_True. | unique-0-1',
                $this->quba->get_response_summary($this->slot));

        // Step 7.
        $this->process_submission([
            'ans1' => 'x', 'ans2' => 'x^2', 'ans3' => 'x+3', 'ans4' => 'true',
            'ans1_val' => 'x^2', 'ans2_val' => 'x^2', 'ans3_val' => 'x^5', '-submit' => 1,
        ]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(3.4);
        $this->render();
        $this->check_output_contains_text_input('ans1', 'x');
        $this->check_output_contains_text_input('ans2', 'x^2');
        $this->check_output_contains_text_input('ans3', 'x+3');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_input_validation('ans2');
        $this->check_output_contains_input_validation('ans3');
        $this->check_output_does_not_contain_input_validation('ans4');
        $this->check_output_does_not_contain_prt_feedback('odd');
        $this->check_output_contains_prt_feedback('even');
        $this->check_output_does_not_contain_prt_feedback('oddeven');
        $this->check_output_contains_prt_feedback('unique');
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                $this->get_contains_select_expectation('ans4', stack_boolean_input::get_choices(), 'true', true),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );
        $this->assertEquals('Seed: 1; ans1: x [valid]; ans2: x^2 [score]; ans3: x+3 [valid]; ans4: true [score]; '
                . 'odd: !; even: # = 1 | even-0-1; oddeven: !; unique: # = 1 | ATLogic_True. | unique-0-1',
                $this->quba->get_response_summary($this->slot));

        // Step 8.
        $this->process_submission([
            'ans1' => 'x', 'ans2' => '', 'ans3' => 'x+3', 'ans4' => 'true',
            'ans1_val' => 'x', 'ans2_val' => 'x^2', 'ans3_val' => 'x+3', '-submit' => 1,
        ]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(3.4);
        $this->render();
        $this->check_output_contains_text_input('ans1', 'x');
        $this->check_output_contains_text_input('ans2', '');
        $this->check_output_contains_text_input('ans3', 'x+3');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_input_validation('ans2');
        $this->check_output_contains_input_validation('ans3');
        $this->check_output_does_not_contain_input_validation('ans4');
        $this->check_output_contains_prt_feedback('odd');
        $this->check_output_does_not_contain_prt_feedback('even');
        $this->check_output_contains_prt_feedback('oddeven');
        $this->check_output_contains_prt_feedback('unique');
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                $this->get_contains_select_expectation('ans4', stack_boolean_input::get_choices(), 'true', true),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );
        $this->assertEquals('Seed: 1; ans1: x [score]; ans3: x+3 [score]; ans4: true [score]; '
                . 'odd: # = 1 | odd-0-1; even: !; oddeven: # = 0 | oddeven-0-0 | oddeven-1-0; '
                . 'unique: # = 1 | ATLogic_True. | unique-0-1',
                $this->quba->get_response_summary($this->slot));

        // Step 9.
        $this->process_submission([
            'ans1' => 'x^3', 'ans2' => 'x^2', 'ans3' => '0', 'ans4' => 'true',
            'ans1_val' => 'x', 'ans3_val' => 'x+3', '-submit' => 1,
        ]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(3.4);
        $this->render();
        $this->check_output_contains_text_input('ans1', 'x^3');
        $this->check_output_contains_text_input('ans2', 'x^2');
        $this->check_output_contains_text_input('ans3', '0');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_input_validation('ans2');
        $this->check_output_contains_input_validation('ans3');
        $this->check_output_does_not_contain_input_validation('ans4');
        $this->check_output_does_not_contain_prt_feedback('odd');
        // Although the student removed x^2 in step 8, step 9 is unchanged from step 7 when they put it back.
        $this->check_output_contains_prt_feedback('even');
        $this->check_output_does_not_contain_prt_feedback('oddeven');
        $this->check_output_contains_prt_feedback('unique');
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                $this->get_contains_select_expectation('ans4', stack_boolean_input::get_choices(), 'true', true),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );
        $this->assertEquals('Seed: 1; ans1: x^3 [valid]; ans2: x^2 [valid]; ans3: 0 [valid]; ans4: true [score]; '
                . 'odd: !; even: !; oddeven: !; unique: # = 1 | ATLogic_True. | unique-0-1',
                $this->quba->get_response_summary($this->slot));

        // Step 10.
        $this->process_submission([
            'ans1' => 'x^3', 'ans2' => 'x^2', 'ans3' => '0', 'ans4' => 'true',
            'ans1_val' => 'x^3', 'ans2_val' => 'x^2', 'ans3_val' => '0', '-submit' => 1,
        ]);

        $this->check_current_state(question_state::$complete);
        $this->check_current_mark(3.5);
        $this->render();
        $this->check_output_contains_text_input('ans1', 'x^3');
        $this->check_output_contains_text_input('ans2', 'x^2');
        $this->check_output_contains_text_input('ans3', '0');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_input_validation('ans2');
        $this->check_output_contains_input_validation('ans3');
        $this->check_output_does_not_contain_input_validation('ans4');
        $this->check_output_contains_prt_feedback('odd');
        $this->check_output_contains_prt_feedback('even');
        $this->check_output_contains_prt_feedback('oddeven');
        $this->check_output_contains_prt_feedback('unique');
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                $this->get_contains_select_expectation('ans4', stack_boolean_input::get_choices(), 'true', true),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );
        $this->assertEquals('Seed: 1; ans1: x^3 [score]; ans2: x^2 [score]; ans3: 0 [score]; ans4: true [score]; '
                . 'odd: # = 1 | odd-0-1; even: # = 1 | even-0-1; oddeven: # = 1 | oddeven-0-1 | oddeven-1-1; '
                . 'unique: # = 1 | ATLogic_True. | unique-0-1',
                $this->quba->get_response_summary($this->slot));

        // Submit all and finish - should update state from complete to gradedright.
        $this->quba->finish_all_questions();

        $this->check_current_state(question_state::$gradedright);
        $this->check_current_mark(3.5);
        $this->render();
        $this->check_output_contains_text_input('ans1', 'x^3', false);
        $this->check_output_contains_text_input('ans2', 'x^2', false);
        $this->check_output_contains_text_input('ans3', '0', false);
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_input_validation('ans2');
        $this->check_output_contains_input_validation('ans3');
        $this->check_output_does_not_contain_input_validation('ans4');
        $this->check_output_contains_prt_feedback('odd');
        $this->check_output_contains_prt_feedback('even');
        $this->check_output_contains_prt_feedback('oddeven');
        $this->check_output_contains_prt_feedback('unique');
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                $this->get_contains_select_expectation('ans4', stack_boolean_input::get_choices(), 'true', false),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );
        $this->assertEquals('Seed: 1; ans1: x^3 [score]; ans2: x^2 [score]; ans3: 0 [score]; ans4: true [score]; '
                . 'odd: # = 1 | odd-0-1; even: # = 1 | even-0-1; oddeven: # = 1 | oddeven-0-1 | oddeven-1-1; '
                . 'unique: # = 1 | ATLogic_True. | unique-0-1',
                $this->quba->get_response_summary($this->slot));
    }

    public function test_divide_by_0(): void {

        // Create a stack question.
        $q = \test_question_maker::make_question('stack', 'divide');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('prt1', null, null);
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );

        // Validate the response 0.
        $this->process_submission(['ans1' => '0', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('prt1', null, null);
        $this->render();
        $expected = 'Seed: 1; ans1: 0 [valid]; prt1: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '0');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Now submit the response 0. Causes a divide by 0.
        $this->process_submission(['ans1' => '0', 'ans1_val' => '0', '-submit' => 1]);

        $this->check_current_state(question_state::$invalid);
        $this->check_current_mark(null);
        $this->check_prt_score('prt1', null, null);
        $this->render();
        $expected = 'Seed: 1; ans1: 0 [score]; prt1: [RUNTIME_ERROR] Division by zero.!';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '0');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('prt1');
        $this->check_output_does_not_contain_stray_placeholders();
        // The error message on the next line in intentionally truncated, because
        // different versions of Maxima report this as 'Division by zero.' or
        // 'Division by 0'. Fortunately this check is good enough.
        $this->check_current_output(
            new question_pattern_expectation('/Division by/')
        );

        // Validate the response 1/2 (correct).
        $this->process_submission(['ans1' => '1/2', 'ans1_val' => '0', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('prt1', null, null);
        $this->render();
        $this->check_output_contains_text_input('ans1', '1/2');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Now submit the response 1/2.
        $this->process_submission(['ans1' => '1/2', 'ans1_val' => '1/2', '-submit' => 1]);

        $this->check_current_state(question_state::$complete);
        $this->check_current_mark(1); // No penalties applied.
        $this->check_prt_score('prt1', 1, 0);
        $this->render();
        $expected = 'Seed: 1; ans1: 1/2 [score]; prt1: # = 1 | prt1-1-T';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '1/2');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('prt1');
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_output_does_not_contain_lang_string('TEST_FAILED', 'qtype_stack', ['errors' => 'Division by zero.']);
    }

    public function test_divide_by_7(): void {

        // This tests use of errcatch in the feedback variables.
        $q = test_question_maker::make_question('stack', 'divide');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('prt1', null, null);
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_does_not_contain_num_parts_correct(),
            $this->get_no_hint_visible_expectation()
            );

        // Validate the response 7.
        $this->process_submission(['ans1' => '7', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('prt1', null, null);
        $this->render();
        $expected = 'Seed: 1; ans1: 7 [valid]; prt1: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '7');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Now submit the response 7. Causes a divide by 0 in the feedback variables.
        $this->process_submission(['ans1' => '7', 'ans1_val' => '7', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        $this->check_prt_score('prt1', 0, 0.333);
        $this->render();
        // Note, there is no error recorded in this response as the 1/0 in the feedback variables was caught.
        $expected = 'Seed: 1; ans1: 7 [score]; prt1: # = 0 | prt1-1-F';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '7');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('prt1');
        $this->check_output_does_not_contain_stray_placeholders();
    }

    public function test_numsigfigs_validate_then_submit_right_first_time(): void {

        // Create the stack question 'test0'.
        $q = \test_question_maker::make_question('stack', 'numsigfigs');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->assertEquals('adaptivemultipart',
                $this->quba->get_question_attempt($this->slot)->get_behaviour_name());
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                new question_pattern_expectation('/Please round/'),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );

        // Process a validate request.
        $this->process_submission(['ans1' => '3.14', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $expected = 'Seed: 1; ans1: 3.14 [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '3.14');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submit of the correct answer.
        $this->process_submission(['ans1' => '3.14', 'ans1_val' => '3.14', '-submit' => 1]);
        // Verify.
        $this->check_current_state(question_state::$complete);
        $this->check_current_mark(1);
        $this->check_prt_score('firsttree', 1, 0);
        $this->render();
        $expected = 'Seed: 1; ans1: 3.14 [score]; firsttree: # = 1 | firsttree-1-T';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '3.14');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('firsttree');
        $this->check_output_does_not_contain_stray_placeholders();
    }

    public function test_numsigfigs_trailing_zero(): void {

        $q = \test_question_maker::make_question('stack', 'numsigfigszeros');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->assertEquals('adaptivemultipart',
                $this->quba->get_question_attempt($this->slot)->get_behaviour_name());
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                new question_pattern_expectation('/Please type in/'),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
                );

        // Process a validate request.
        $this->process_submission(['ans1' => '0.04', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $expected = 'Seed: 1; ans1: 0.04 [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '0.04');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submit of the answer without a trailing zero.
        $this->process_submission(['ans1' => '0.04', 'ans1_val' => '0.04', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        $this->check_prt_score('firsttree', 0, 0.2);
        $this->render();
        $expected = 'Seed: 1; ans1: 0.04 [score]; firsttree: # = 0 | ATNumSigFigs_WrongDigits. | firsttree-1-F';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '0.04');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('firsttree');
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a validation of the correct answer.
        $this->process_submission(['ans1' => '0.040', 'ans1_val' => '0.04', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        $this->render();
        $expected = 'Seed: 1; ans1: 0.040 [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '0.040');
        $this->check_output_contains_input_validation('ans1');

        // The line below currently fails.  It should not.
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submit of the correct answer.
        $this->process_submission(['ans1' => '0.040', 'ans1_val' => '0.040', '-submit' => 1]);

        $this->check_current_state(question_state::$complete);
        $this->check_current_mark(0.8);
        $this->check_prt_score('firsttree', 1, 0);
        $this->render();
        $expected = 'Seed: 1; ans1: 0.040 [score]; firsttree: # = 1 | firsttree-1-T';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '0.040');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('firsttree');
        $this->check_output_does_not_contain_stray_placeholders();

        $question = $this->quba->get_question($this->slot);
        $expected = [];
        $this->assertEquals($expected, $question->validate_warnings());
    }

    public function test_numdpsfeedbackvars_basic(): void {

        $q = \test_question_maker::make_question('stack', 'numdpsfeedbackvars');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->assertEquals('adaptivemultipart',
            $this->quba->get_question_attempt($this->slot)->get_behaviour_name());
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_contains_text_input('ans2');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_pattern_expectation('/Give me two random numbers/'),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_does_not_contain_num_parts_correct(),
            $this->get_no_hint_visible_expectation()
            );

        // Process a validate request.
        $this->process_submission(['ans1' => '0.04', 'ans2' => '3.14', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('prt1', null, null);
        $this->render();
        $expected = 'Seed: 1; ans1: 0.04 [valid]; ans2: 3.14 [valid]; prt1: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '0.04');
        $this->check_output_contains_text_input('ans2', '3.14');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_input_validation('ans2');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submit of the answer.
        $this->process_submission([
            'ans1' => '0.04', 'ans1_val' => '0.04',
            'ans2' => '3.14', 'ans2_val' => '3.14', '-submit' => 1,
        ]);

        $expected = 'Seed: 1; ans1: 0.04 [score]; ans2: 3.14 [score]; prt1: # = 0 | ' .
            'ATNumDecPlaces_Wrong_DPs. ATNumDecPlaces_Not_equiv. | prt1-1-F';
        $this->check_response_summary($expected);
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        $this->check_prt_score('prt1', 0, 0.3);
        $this->render();

        $this->check_output_contains_text_input('ans1', '0.04');
        $this->check_output_contains_text_input('ans2', '3.14');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_input_validation('ans2');
        $this->check_output_contains_prt_feedback('prt1');
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a validate request.
        $this->process_submission([
            'ans1' => '7.04', 'ans1_val' => '0.04',
            'ans2' => '3.14', 'ans2_val' => '3.14', '-submit' => 1,
        ]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        $this->check_prt_score('prt1', null, null);
        $this->render();
        $expected = 'Seed: 1; ans1: 7.04 [valid]; ans2: 3.14 [score]; prt1: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '7.04');
        $this->check_output_contains_text_input('ans2', '3.14');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_input_validation('ans2');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submit of the answer.
        $this->process_submission([
            'ans1' => '7.04', 'ans1_val' => '7.04',
            'ans2' => '3.14', 'ans2_val' => '3.14', '-submit' => 1,
        ]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        $this->check_prt_score('prt1', 0, 0.3);
        $this->render();
        $expected = 'Seed: 1; ans1: 7.04 [score]; ans2: 3.14 [score]; prt1: # = 0 | ' .
            'ATNumDecPlaces_Wrong_DPs. ATNumDecPlaces_Equiv. | prt1-1-F';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '7.04');
        $this->check_output_contains_text_input('ans2', '3.14');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_input_validation('ans2');
        $this->check_output_contains_prt_feedback('prt1');
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a validate request.
        $this->process_submission([
            'ans1' => '7.04', 'ans1_val' => '7.04',
            'ans2' => '3.140', 'ans2_val' => '3.14', '-submit' => 1,
        ]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        $this->check_prt_score('prt1', null, null);
        $this->render();
        $expected = 'Seed: 1; ans1: 7.04 [score]; ans2: 3.140 [valid]; prt1: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '7.04');
        $this->check_output_contains_text_input('ans2', '3.140');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_input_validation('ans2');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submit of the answer.
        $this->process_submission([
            'ans1' => '7.04', 'ans1_val' => '7.04',
            'ans2' => '3.140', 'ans2_val' => '3.140', '-submit' => 1,
        ]);

        // At this point the answer of 3.140 should be correct! It has 3 decimal places.
        // The tests below pass (erroneously) because of the "min" function in the feedbackvariables.
        // The trailing zero has been stripped off.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        $this->check_prt_score('prt1', 0, 0.3);
        $this->render();
        $expected = 'Seed: 1; ans1: 7.04 [score]; ans2: 3.140 [score]; prt1: # = 0 | ' .
            'ATNumDecPlaces_Wrong_DPs. ATNumDecPlaces_Equiv. | prt1-1-F';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '7.04');
        // Note, the trailing zero is in the validation feedback (numerical input type).
        $this->check_output_contains_text_input('ans2', '3.140');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_input_validation('ans2');
        $this->check_output_contains_prt_feedback('prt1');
        $this->check_output_does_not_contain_stray_placeholders();

        // We can really see this, as the SAns argument of the answer test is displayed in the feedback.
        $expected = 'Your answer was received as \({3.14}\).';
        $this->assert_content_with_maths_contains($expected, $this->currentoutput);

        $question = $this->quba->get_question($this->slot);
        $expected = [
            '<i class="icon fa fa-exclamation-circle text-danger fa-fw " ' .
            'title="Some answer tests rely on the raw input from a student, and so the "SAns" ' .
            'field of the node should be the name of a question input.  Please check the following ' .
            '(prt.node) which looks like a calculated value instead: prt1-1" ' .
            'aria-label="Some answer tests rely on the raw input from a student, and so the "SAns" ' .
            'field of the node should be the name of a question input.  Please check the following (prt.node) ' .
            'which looks like a calculated value instead: prt1-1"></i>Some answer tests rely on the raw input from ' .
            'a student, and so the "SAns" field of the node should be the name of a question input.  ' .
            'Please check the following (prt.node) which looks like a calculated value instead: prt1-1',
        ];
        $this->assertEquals($expected, $question->validate_warnings());
    }

    public function test_test0_save_does_validate_but_does_not_submit(): void {

        // Create the stack question 'test0'.
        $q = \test_question_maker::make_question('stack', 'test0');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->assertEquals('adaptivemultipart',
                $this->quba->get_question_attempt($this->slot)->get_behaviour_name());
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                new question_pattern_expectation('/What is/'),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );

        // Just save a response. This will validate.
        $this->process_submission(['ans1' => '2']);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $this->check_output_contains_text_input('ans1', '2');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Just save again. Nothing visible should change.
        $lastoutput = $this->currentoutput;
        $this->process_submission(['ans1' => '2', 'ans1_val' => '2']);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->render();
        $this->assertEquals(str_replace('sequencecheck" value="2"', 'sequencecheck" value="3"', $lastoutput),
                    $this->currentoutput);
    }

    public function test_test_boolean_validate_then_submit_right_first_time(): void {

        // Create the stack question 'test_boolean'.
        $q = \test_question_maker::make_question('stack', 'test_boolean');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->assertEquals('adaptivemultipart',
                $this->quba->get_question_attempt($this->slot)->get_behaviour_name());
        $this->render();
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
        new question_pattern_expectation('/What is/'),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_does_not_contain_num_parts_correct(),
            $this->get_no_hint_visible_expectation()
        );

        // Process an incorrect answer.
        $this->process_submission(['ans1' => 'false', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_prt_score('firsttree', 0.2, 0.3);
        // This question also checks marks can be defined by question variables.
        $this->check_current_mark(0.2);
        $this->render();
        $expected = 'Seed: 1; ans1: false [score]; firsttree: # = 0.2 | firsttree-1-F';
        $this->check_response_summary($expected);
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_contains_prt_feedback('firsttree');
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submit of the correct answer.
        $this->process_submission(['ans1' => 'true', 'ans1_val' => 'false', '-submit' => 1]);

        // Verify.
        $this->check_current_state(question_state::$todo);
        $this->check_prt_score('firsttree', 0.9, 0.3);
        $this->check_current_mark(0.6);
        $this->render();
        $expected = 'Seed: 1; ans1: true [score]; firsttree: # = 0.9 | ATLogic_True. | firsttree-1-T';
        $this->check_response_summary($expected);
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_contains_prt_feedback('firsttree');
        $this->check_output_does_not_contain_stray_placeholders();
    }

    public function test_1input2prts_specific_feedback_handling(): void {

        // Create a stack question.
        $q = \test_question_maker::make_question('stack', '1input2prts');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the right behaviour is used.
        $this->assertEquals('adaptivemultipart', $this->quba->get_question_attempt($this->slot)->get_behaviour_name());

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );

        // Submit the correct response.
        $this->process_submission(['ans1' => '12', 'ans1_val' => '12', '-submit' => 1]);

        $this->check_current_state(question_state::$complete);
        $this->check_current_mark(1);
        $this->render();
        $expected = 'Seed: 1; ans1: 12 [score]; prt1: # = 1 | prt1-0-1; prt2: # = 1 | prt2-0-1';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '12');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('prt1');
        $this->check_output_contains_prt_feedback('prt2');
        $this->check_output_does_not_contain_stray_placeholders();
        $this->assertMatchesRegularExpression('~' . preg_quote($q->prtcorrect, '~') . '~', $this->currentoutput);
        $this->check_current_output(
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );
    }

    public function test_1input2prts_different_prt_values(): void {

        // Create a stack question.
        $q = \test_question_maker::make_question('stack', '1input2prts');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the right behaviour is used.
        $this->assertEquals('adaptivemultipart', $this->quba->get_question_attempt($this->slot)->get_behaviour_name());

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_does_not_contain_num_parts_correct(),
            $this->get_no_hint_visible_expectation()
            );

        // Submit partially correct response.
        $this->process_submission(['ans1' => '3', 'ans1_val' => '3', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0.8);
        $this->render();
        $expected = 'Seed: 1; ans1: 3 [score]; prt1: # = 0 | prt1-0-0; prt2: # = 1 | prt2-0-1';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '3');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('prt1');
        $this->check_output_contains_prt_feedback('prt2');
        $this->check_output_does_not_contain_stray_placeholders();
        $this->assertMatchesRegularExpression('~' . preg_quote($q->prtincorrect, '~') . '~', $this->currentoutput);
        $this->assertMatchesRegularExpression('~' . preg_quote($q->prtcorrect, '~') . '~', $this->currentoutput);
        $this->check_current_output(
            $this->get_does_not_contain_num_parts_correct(),
            $this->get_no_hint_visible_expectation()
            );

        // Submit partially correct response.
        $this->process_submission(['ans1' => '8', 'ans1_val' => '8', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        // How do we get to 0.95?
        // Take 0.8 for the correct first part (which remains in place as the highest mark).
        // The penalty for this question is 0.25.
        // Add 0.2*(1-penalty) = 0.15 marks.
        // A real question author would probably adjust the penalties for this question.
        $this->check_current_mark(0.95);
        $this->render();
        $expected = 'Seed: 1; ans1: 8 [score]; prt1: # = 1 | prt1-0-1; prt2: # = 0 | prt2-0-0';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '8');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('prt1');
        $this->check_output_contains_prt_feedback('prt2');
        $this->check_output_does_not_contain_stray_placeholders();
        $this->assertMatchesRegularExpression('~' . preg_quote($q->prtincorrect, '~') . '~', $this->currentoutput);
        $this->assertMatchesRegularExpression('~' . preg_quote($q->prtcorrect, '~') . '~', $this->currentoutput);
        $this->check_current_output(
            $this->get_does_not_contain_num_parts_correct(),
            $this->get_no_hint_visible_expectation()
            );
    }

    public function test_test0_adaptive_nopenalties_wrong_then_right_then_regrade(): void {

        // Create the stack question 'test0'.
        $q = \test_question_maker::make_question('stack', 'test0');
        $this->start_attempt_at_question($q, 'adaptivenopenalty', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->assertEquals('adaptivemultipart',
                $this->quba->get_question_attempt($this->slot)->get_behaviour_name());
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                new question_pattern_expectation('/What is/'),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );

        // Submit a wrong answer with valdiation.
        $this->process_submission(['ans1' => '1', 'ans1_val' => '1', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        $this->check_prt_score('firsttree', 0, 0.3);
        $this->render();
        $expected = 'Seed: 1; ans1: 1 [score]; firsttree: # = 0 | ATEqualComAss (AlgEquiv-false). | firsttree-1-F';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '1');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('firsttree');
        $this->check_output_does_not_contain_stray_placeholders();

        // Submit the right answer with validation.
        $this->process_submission(['ans1' => '2', 'ans1_val' => '2', '-submit' => 1]);

        // Verify.
        $this->check_current_state(question_state::$complete);
        $this->check_current_mark(1);
        $this->check_prt_score('firsttree', 1, 0);
        $this->render();
        $expected = 'Seed: 1; ans1: 2 [score]; firsttree: # = 1 | firsttree-1-T';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '2');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('firsttree');
        $this->check_output_does_not_contain_stray_placeholders();

        // Now regrade.
        $this->quba->regrade_all_questions();

        // Verify.
        $this->check_current_state(question_state::$complete);
        $this->check_current_mark(1);
        $this->check_prt_score('firsttree', 1, 0);
        $this->render();
        $expected = 'Seed: 1; ans1: 2 [score]; firsttree: # = 1 | firsttree-1-T';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '2');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('firsttree');
        $this->check_output_does_not_contain_stray_placeholders();
    }

    public function test_single_char_vars(): void {

        // Create the stack question 'test-single-char_vars'.
        $q = \test_question_maker::make_question('stack', 'single_char_vars');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        $this->render();

        // Process a validate request.
        $this->process_submission(['ans1' => 'sin(x)', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $expected = 'Seed: 1; ans1: sin(x) [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', 'sin(x)');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submit of the incorrect answer.
        $this->process_submission(['ans1' => 'sin(x)', 'ans1_val' => 'sin(x)', '-submit' => 1]);

        // Verify.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        $this->check_prt_score('firsttree', 0, 0.3);
        $this->render();
        $expected = 'Seed: 1; ans1: sin(x) [score]; firsttree: # = 0 | firsttree-1-F';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', 'sin(x)');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('firsttree');
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submit of the correct answer and validate.
        $this->process_submission(['ans1' => 'sin(xy)', '-submit' => 1]);

        // Verify.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $expected = 'Seed: 1; ans1: sin(xy) [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', 'sin(xy)');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submit of the correct answer.
        $this->process_submission(['ans1' => 'sin(xy)', 'ans1_val' => 'sin(xy)', '-submit' => 1]);

        // Verify.
        $this->check_current_state(question_state::$complete);
        $this->check_current_mark(0.7);
        $this->check_prt_score('firsttree', 1, 0);
        $this->render();
        $expected = 'Seed: 1; ans1: sin(xy) [score]; firsttree: # = 1 | firsttree-1-T';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', 'sin(xy)');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('firsttree');
        $this->check_output_does_not_contain_stray_placeholders();
    }

    public function test_guard_clause_prt_ok(): void {

        $q = \test_question_maker::make_question('stack', 'runtime_prt_err');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        $this->render();

        // Process a validate request.
        $this->process_submission(['ans1' => '[3*x+1+5]', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('Result', null, null);
        $this->render();
        $this->check_output_contains_text_input('ans1', '[3*x+1+5]');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submit of the incorrect answer.
        $this->process_submission(['ans1' => '[3*x+1+5]', 'ans1_val' => '[3*x+1+5]', '-submit' => 1]);

        // Verify.
        $expected = 'Seed: 1; ans1: [3*x+1+5] [score]; Result: # = 0 | Result-0-F';
        $this->check_response_summary($expected);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        // The penalty here should be zero because a runtime error has occured.
        $this->check_prt_score('Result', 0, 0);

        $this->render();
        $this->check_output_contains_text_input('ans1', '[3*x+1+5]');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('Result');
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submit of the correct answer and validate.
        $this->process_submission(['ans1' => '[3*x+1=5]', '-submit' => 1]);

        // Verify.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        $this->check_prt_score('Result', null, null);
        $this->render();
        $expected = 'Seed: 1; ans1: [3*x+1=5] [valid]; Result: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '[3*x+1=5]');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submit of the correct answer.
        $this->process_submission(['ans1' => '[3*x+1=5]', 'ans1_val' => '[3*x+1=5]', '-submit' => 1]);

        $this->check_current_state(question_state::$complete);
        $this->check_current_mark(1);
        $this->check_prt_score('Result', 1, 0);
        $this->render();
        $expected = 'Seed: 1; ans1: [3*x+1=5] [score]; Result: # = 1 | ATLogic_True. | Result-0-T | ' .
                'ATList_wronglen. | Result-1-F | Result-2-T';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '[3*x+1=5]');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('Result');
        $this->check_output_does_not_contain_stray_placeholders();
    }

    public function test_guard_clause_prt_err(): void {

        $q = \test_question_maker::make_question('stack', 'runtime_prt_err');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        $this->render();

        // Process a validate request.
        $this->process_submission(['ans1' => '[2*sin(x)*y=1,x+y=1]', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('Result', null, null);
        $this->render();
        $this->check_output_contains_text_input('ans1', '[2*sin(x)*y=1,x+y=1]');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submit of the incorrect answer.
        $this->process_submission(['ans1' => '[2*sin(x)*y=1,x+y=1]', 'ans1_val' => '[2*sin(x)*y=1,x+y=1]', '-submit' => 1]);

        // Verify.
        $this->check_current_state(question_state::$invalid);
        $this->check_current_mark(null);
        $this->check_prt_score('Result', null, null);
        $this->render();
        // Note from version 5.37.0 of Maxima the precise form of the error message changed.
        $expected = "Seed: 1; ans1: [2*sin(x)*y=1,x+y=1] [score]; Result: [RUNTIME_ERROR] " .
            "algsys: Couldn't reduce system to a polynomial in one variable." .
            "|apply: found %_TMP evaluates to 0 where";
        // Different versions of Maxima end with " an array was expected." or " a function was expected".
        $this->check_response_summary_contains($expected);
        $this->check_output_contains_text_input('ans1', '[2*sin(x)*y=1,x+y=1]');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('Result');
        $this->check_output_does_not_contain_stray_placeholders();
        // Note from version 5.37.0 of Maxima the precise form of the error message changed.

        $this->check_current_output(
                // Some inconsistencey in Maxima error messages, so shortening search string.
                new question_pattern_expectation('/reduce system to a polynomial/'),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );
    }

    public function test_runtime_err_feedback_variables(): void {

        $q = \test_question_maker::make_question('stack', 'runtime_prt_err');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        $this->render();

        // Process a validate request.
        $this->process_submission(['ans1' => '[x=7,2*sin(x)*y=1]', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('Result', null, null);
        $this->render();
        $this->check_output_contains_text_input('ans1', '[x=7,2*sin(x)*y=1]');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submit of the incorrect answer.
        $this->process_submission(['ans1' => '[x=7,2*sin(x)*y=1]', 'ans1_val' => '[x=7,2*sin(x)*y=1]', '-submit' => 1]);

        // Note, errors in the feedback variables themselves do not cause an invalid request, or stop the PRT.
        // That is the intended behaviour, unlike entries to PRT nodes themselves which must be error free.

        // Verify.
        $this->check_current_state(question_state::$complete);
        $this->render();
        $this->check_current_mark(1);
        // The PRT executed, so this generates the normal score and penalty.
        $this->check_prt_score('Result', 1, 0);
        // We do expect to see that a runtime error has occured in the trace for debugging other people's questions!
        $expected = 'Seed: 1; ans1: [x=7,2*sin(x)*y=1] [score]; Result: ' .
            '[RUNTIME_FV_ERROR] # = 1 | Division by zero. | ATLogic_True. | Result-0-T | ' .
            'ATList_wronglen. | Result-1-F | Result-2-T';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '[x=7,2*sin(x)*y=1]');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('Result');
        $this->check_output_does_not_contain_stray_placeholders();
        // Note from version 5.37.0 of Maxima the precise form of the error message changed.

        // We ignore errors in feedback variables, so "Division by zero." no longer appears in output.
        $this->check_current_output(
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
                );
    }

    public function test_runtime_error_session(): void {

        $q = \test_question_maker::make_question('stack', 'runtime_ses_err');
        $this->start_attempt_at_question($q, 'adaptive', 1);
        $this->render();

        $rte = implode(' ', array_keys($q->runtimeerrors));
        $err = 'Error(s) in question-variables: Expected "#pm#", "%not ", "\'", "\'\'", ' .
                '"(", "+", "+-", "-", "? ", "?", "?? ", "[", "do", "for", "from", "if", "in", "next", "not ", "not", ' .
                '"nounnot ", "nounnot", "step", "thru", "unless", "while", "{", "|", boolean, comment, end of input, float, ' .
                'identifier, integer, string or whitespace but ")" found. (At about line 1 character 11.)';
        $this->assertEquals($err, $rte);
    }

    public function test_runtime_error_cas(): void {

        $q = \test_question_maker::make_question('stack', 'runtime_cas_err');
        $this->start_attempt_at_question($q, 'adaptive', 1);
        $this->render();

        $rte = implode(' ', array_keys($q->runtimeerrors));
        $err = 'Division by zero. The field ""Question text"" generated the following error: Division by zero. ' .
            'The field ""Question variables"" generated the following error: Division by zero.';
        $this->assertEquals($err, $rte);
    }

    public function test_test0_validate_then_submit_wrong_answer_default_penalty(): void {

        // Create the stack question based on 'test0'.
        $q = \test_question_maker::make_question('stack', 'test0');

        $prt = new stdClass;
        $prt->name              = 'firsttree';
        $prt->id                = 0;
        $prt->value             = 1;
        $prt->feedbackstyle     = 1;
        $prt->feedbackvariables = '';
        $prt->firstnodename     = '0';
        $prt->nodes             = [];
        $prt->autosimplify      = true;

        $newnode = new stdClass;
        $newnode->id                  = '0';
        $newnode->nodename            = '0';
        $newnode->sans                = 'ans1';
        $newnode->tans                = '2';
        $newnode->answertest          = 'EqualComAss';
        $newnode->testoptions         = '';
        $newnode->quiet               = false;
        $newnode->falsescore          = '0';
        $newnode->falsescoremode      = '=';
        $newnode->falsepenalty        = $q->penalty;
        $newnode->falsefeedback       = '';
        $newnode->falsefeedbackformat = '1';
        $newnode->falseanswernote     = 'firsttree-1-F';
        $newnode->falsenextnode       = '1';
        $newnode->truescore           = '1';
        $newnode->truescoremode       = '=';
        $newnode->truepenalty         = $q->penalty;
        $newnode->truefeedback        = '';
        $newnode->truefeedbackformat  = '1';
        $newnode->trueanswernote      = 'firsttree-1-T';
        $newnode->truenextnode        = '-1';
        $prt->nodes[] = $newnode;
        $newnode = new stdClass;
        $newnode->id                  = '1';
        $newnode->nodename            = '1';
        $newnode->sans                = 'ans1';
        $newnode->tans                = '3';
        $newnode->answertest          = 'EqualComAss';
        $newnode->testoptions         = '';
        $newnode->quiet               = false;
        $newnode->falsescore          = '0';
        $newnode->falsescoremode      = '=';
        $newnode->falsepenalty        = $q->penalty;
        $newnode->falsefeedback       = '';
        $newnode->falsefeedbackformat = '1';
        $newnode->falseanswernote     = 'firsttree-2-F';
        $newnode->falsenextnode       = '1';
        $newnode->truescore           = '0.5';
        $newnode->truescoremode       = '=';
        $newnode->truepenalty         = $q->penalty;
        $newnode->truefeedback        = '';
        $newnode->truefeedbackformat  = '1';
        $newnode->trueanswernote      = 'firsttree-2-T';
        $newnode->truenextnode        = '-1';
        $prt->nodes[] = $newnode;
        $q->prts[$prt->name] = new stack_potentialresponse_tree_lite($prt, $prt->value, $q);

        $this->start_attempt_at_question($q, 'adaptive', 1);

        $this->render();

        // Process a validate request.
        $this->process_submission(['ans1' => '3', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);

        $this->render();
        $expected = 'Seed: 1; ans1: 3 [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '3');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submit of the correct answer.
        $this->process_submission(['ans1' => '3', 'ans1_val' => '3', '-submit' => 1]);
        // Verify.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0.5);
        $this->check_answer_note('firsttree', 'ATEqualComAss (AlgEquiv-false). | firsttree-1-F | firsttree-2-T');
        $this->check_prt_score('firsttree', 0.5, 0.3);
        $this->render();
        $expected = 'Seed: 1; ans1: 3 [score]; firsttree: # = 0.5 | ATEqualComAss (AlgEquiv-false). | firsttree-1-F | ' .
                'firsttree-2-T';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '3');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('firsttree');
        $this->check_output_does_not_contain_stray_placeholders();

        // Submit again and check penalty.
        $this->process_submission(['ans1' => '2', 'ans1_val' => '3', '-submit' => 1]);
        $this->check_current_state(question_state::$todo);
        // Mark from previous attempt is non-zero, even at the validate stage.
        $this->check_current_mark(0.5);
        $this->check_prt_score('firsttree', null, null);

        $this->process_submission(['ans1' => '2', 'ans1_val' => '2', '-submit' => 1]);
        $this->check_current_state(question_state::$complete);
        $this->check_current_mark(0.7);
        $this->check_answer_note('firsttree', 'firsttree-1-T');
        $this->check_prt_score('firsttree', 1, 0);
        $this->render();
        $expected = 'Seed: 1; ans1: 2 [score]; firsttree: # = 1 | firsttree-1-T';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '2');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('firsttree');
        $this->check_output_does_not_contain_stray_placeholders();
    }

    public function test_test0_validate_then_submit_wrong_answer_explicit_penalty(): void {

        // Create the stack question 'test0'.
        $q = \test_question_maker::make_question('stack', 'test0');
        // Modify the PRT to that the penalty on the false branch is 0.1.
        $prt = new stdClass;
        $prt->name              = 'firsttree';
        $prt->id                = 0;
        $prt->value             = 1;
        $prt->feedbackstyle     = 1;
        $prt->feedbackvariables = '';
        $prt->firstnodename     = '0';
        $prt->nodes             = [];
        $prt->autosimplify      = true;

        $newnode = new stdClass;
        $newnode->id                  = '0';
        $newnode->nodename            = '0';
        $newnode->sans                = 'ans1';
        $newnode->tans                = '2';
        $newnode->answertest          = 'EqualComAss';
        $newnode->testoptions         = '';
        $newnode->quiet               = false;
        $newnode->falsescore          = '0';
        $newnode->falsescoremode      = '=';
        $newnode->falsepenalty        = '0.1';
        $newnode->falsefeedback       = '';
        $newnode->falsefeedbackformat = '1';
        $newnode->falseanswernote     = 'firsttree-1-F';
        $newnode->falsenextnode       = '-1';
        $newnode->truescore           = '1';
        $newnode->truescoremode       = '=';
        $newnode->truepenalty         = '0.1';
        $newnode->truefeedback        = '';
        $newnode->truefeedbackformat  = '1';
        $newnode->trueanswernote      = 'firsttree-1-T';
        $newnode->truenextnode        = '-1';
        $prt->nodes[] = $newnode;

        $q->prts[$prt->name] = new stack_potentialresponse_tree_lite($prt, $prt->value, $q);

        $this->start_attempt_at_question($q, 'adaptive', 1);

        $this->render();

        // Process a validate request.
        $this->process_submission(['ans1' => '3', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);

        $this->render();
        $expected = 'Seed: 1; ans1: 3 [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '3');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submit of the correct answer.
        $this->process_submission(['ans1' => '3', 'ans1_val' => '3', '-submit' => 1]);

        // Verify.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        $this->check_prt_score('firsttree', 0, 0.1);
        $this->render();
        $expected = 'Seed: 1; ans1: 3 [score]; firsttree: # = 0 | ATEqualComAss (AlgEquiv-false). | firsttree-1-F';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '3');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('firsttree');
        $this->check_output_does_not_contain_stray_placeholders();

        // Submit again and check penalty.
        $this->process_submission(['ans1' => '2', 'ans1_val' => '3', '-submit' => 1]);
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        $this->check_prt_score('firsttree', null, null);

        $this->process_submission(['ans1' => '2', 'ans1_val' => '2', '-submit' => 1]);
        $this->check_current_state(question_state::$complete);
        $this->check_current_mark(0.9);
        $this->check_prt_score('firsttree', 1, 0);
        $this->render();
        $expected = 'Seed: 1; ans1: 2 [score]; firsttree: # = 1 | firsttree-1-T';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '2');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('firsttree');
        $this->check_output_does_not_contain_stray_placeholders();
    }

    public function test_test0_validate_then_submit_wrong_answer_no_penalty(): void {

        // This test creates a situation where we have partial credit, but the attempt
        // accrues no penalty.  This makes use of the PRT "penalty" field.

        // Create the stack question based on 'test0'.
        $q = \test_question_maker::make_question('stack', 'test0');
        $prt = new stdClass;
        $prt->name              = 'firsttree';
        $prt->id                = 0;
        $prt->value             = 1;
        $prt->feedbackstyle     = 1;
        $prt->feedbackvariables = '';
        $prt->firstnodename     = '0';
        $prt->nodes             = [];
        $prt->autosimplify      = false;

        $newnode = new stdClass;
        $newnode->id                  = '0';
        $newnode->nodename            = '0';
        $newnode->sans                = 'ans1';
        $newnode->tans                = '2';
        $newnode->answertest          = 'EqualComAss';
        $newnode->testoptions         = '';
        $newnode->quiet               = false;
        $newnode->falsescore          = '0';
        $newnode->falsescoremode      = '=';
        $newnode->falsepenalty        = '0.1';
        $newnode->falsefeedback       = '';
        $newnode->falsefeedbackformat = '1';
        $newnode->falseanswernote     = 'firsttree-1-F';
        $newnode->falsenextnode       = '1';
        $newnode->truescore           = '1';
        $newnode->truescoremode       = '=';
        $newnode->truepenalty         = $q->penalty;
        $newnode->truefeedback        = '';
        $newnode->truefeedbackformat  = '1';
        $newnode->trueanswernote      = 'firsttree-1-T';
        $newnode->truenextnode        = '-1';
        $prt->nodes[] = $newnode;
        $newnode = new stdClass;
        $newnode->id                  = '1';
        $newnode->nodename            = '1';
        $newnode->sans                = 'ans1';
        $newnode->tans                = '3';
        $newnode->answertest          = 'EqualComAss';
        $newnode->testoptions         = '';
        $newnode->quiet               = false;
        $newnode->falsescore          = '0';
        $newnode->falsescoremode      = '=';
        $newnode->falsepenalty        = '0.2';
        $newnode->falsefeedback       = '';
        $newnode->falsefeedbackformat = '1';
        $newnode->falseanswernote     = 'firsttree-2-F';
        $newnode->falsenextnode       = '1';
        $newnode->truescore           = '0.5';
        $newnode->truescoremode       = '=';
        $newnode->truepenalty         = '0';
        $newnode->truefeedback        = '';
        $newnode->truefeedbackformat  = '1';
        $newnode->trueanswernote      = 'firsttree-2-T';
        $newnode->truenextnode        = '-1';
        $prt->nodes[] = $newnode;
        $q->prts[$prt->name] = new stack_potentialresponse_tree_lite($prt, $prt->value, $q);

        $this->start_attempt_at_question($q, 'adaptive', 1);

        $this->render();

        // Process a validate request.
        $this->process_submission(['ans1' => '3', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);

        $this->render();
        $expected = 'Seed: 1; ans1: 3 [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '3');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submit of the correct answer.
        $this->process_submission(['ans1' => '3', 'ans1_val' => '3', '-submit' => 1]);
        // Verify.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0.5);
        $this->check_answer_note('firsttree', 'ATEqualComAss (AlgEquiv-false). | firsttree-1-F | firsttree-2-T');
        // This is the point of the test: we expect a zero penalty here in the 3rd argument.
        $this->check_prt_score('firsttree', 0.5, 0);
        $this->render();
        $expected = 'Seed: 1; ans1: 3 [score]; firsttree: # = 0.5 | ATEqualComAss (AlgEquiv-false). ' .
                '| firsttree-1-F | firsttree-2-T';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '3');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('firsttree');
        $this->check_output_does_not_contain_stray_placeholders();

        // Submit again and check penalty.
        $this->process_submission(['ans1' => '2', 'ans1_val' => '3', '-submit' => 1]);
        $this->check_current_state(question_state::$todo);
        // Mark from previous attempt is non-zero, even at the validate stage.
        $this->check_current_mark(0.5);
        $this->check_prt_score('firsttree', null, null);

        $this->process_submission(['ans1' => '2', 'ans1_val' => '2', '-submit' => 1]);
        $this->check_current_state(question_state::$complete);
        $this->check_current_mark(1);
        $this->check_answer_note('firsttree', 'firsttree-1-T');
        $this->check_prt_score('firsttree', 1, 0);
        $this->render();
        $expected = 'Seed: 1; ans1: 2 [score]; firsttree: # = 1 | firsttree-1-T';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '2');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('firsttree');
        $this->check_output_does_not_contain_stray_placeholders();
    }

    public function test_test0_validate_then_submit_two_wrong_answers_one_no_penalty(): void {

         // This test creates a situation where we have partial credit, but the attempt
         // accrues no penalty.  This makes use of the PRT "penalty" field.

        // Create the stack question based on 'test0'.
        $q = \test_question_maker::make_question('stack', 'test0');
        $prt = new stdClass;
        $prt->name              = 'firsttree';
        $prt->id                = 0;
        $prt->value             = 1;
        $prt->feedbackstyle     = 1;
        $prt->feedbackvariables = '';
        $prt->firstnodename     = '0';
        $prt->nodes             = [];
        $prt->autosimplify      = false;

        $newnode = new stdClass;
        $newnode->id                  = '0';
        $newnode->nodename            = '0';
        $newnode->sans                = 'ans1';
        $newnode->tans                = '2';
        $newnode->answertest          = 'EqualComAss';
        $newnode->testoptions         = '';
        $newnode->quiet               = false;
        $newnode->falsescore          = '0';
        $newnode->falsescoremode      = '=';
        $newnode->falsepenalty        = '0.1';
        $newnode->falsefeedback       = '';
        $newnode->falsefeedbackformat = '1';
        $newnode->falseanswernote     = 'firsttree-1-F';
        $newnode->falsenextnode       = '1';
        $newnode->truescore           = '1';
        $newnode->truescoremode       = '=';
        $newnode->truepenalty         = $q->penalty;
        $newnode->truefeedback        = '';
        $newnode->truefeedbackformat  = '1';
        $newnode->trueanswernote      = 'firsttree-1-T';
        $newnode->truenextnode        = '-1';
        $prt->nodes[] = $newnode;
        $newnode = new stdClass;
        $newnode->id                  = '1';
        $newnode->nodename            = '1';
        $newnode->sans                = 'ans1';
        $newnode->tans                = '3';
        $newnode->answertest          = 'EqualComAss';
        $newnode->testoptions         = '';
        $newnode->quiet               = false;
        $newnode->falsescore          = '0';
        $newnode->falsescoremode      = '=';
        $newnode->falsepenalty        = '0.2';
        $newnode->falsefeedback       = '';
        $newnode->falsefeedbackformat = '1';
        $newnode->falseanswernote     = 'firsttree-2-F';
        $newnode->falsenextnode       = '1';
        $newnode->truescore           = '0.5';
        $newnode->truescoremode       = '=';
        $newnode->truepenalty         = '0';
        $newnode->truefeedback        = '';
        $newnode->truefeedbackformat  = '1';
        $newnode->trueanswernote      = 'firsttree-2-T';
        $newnode->truenextnode        = '-1';
        $prt->nodes[] = $newnode;
        $q->prts[$prt->name] = new stack_potentialresponse_tree_lite($prt, $prt->value, $q);

        $this->start_attempt_at_question($q, 'adaptive', 1);

        $this->render();

        // Process a validate request.
        $this->process_submission(['ans1' => '4', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);

        $this->render();
        $expected = 'Seed: 1; ans1: 4 [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '4');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submit of the wrong response, which attracts a penalty.
        $this->process_submission(['ans1' => '4', 'ans1_val' => '4', '-submit' => 1]);
        // Verify.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        $this->check_answer_note('firsttree',
                'ATEqualComAss (AlgEquiv-false). | firsttree-1-F | ATEqualComAss (AlgEquiv-false). | firsttree-2-F');
        $this->check_prt_score('firsttree', 0, 0.2);
        $this->render();
        $expected = 'Seed: 1; ans1: 4 [score]; firsttree: # = 0 | ATEqualComAss (AlgEquiv-false). | firsttree-1-F | ' .
                'ATEqualComAss (AlgEquiv-false). | firsttree-2-F';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '4');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('firsttree');
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a validate request of an incorrect response with no penalty.
        $this->process_submission(['ans1' => '3', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        $this->check_prt_score('firsttree', null, null);

        $this->render();
        $this->check_output_contains_text_input('ans1', '3');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submit of the correct answer.
        $this->process_submission(['ans1' => '3', 'ans1_val' => '3', '-submit' => 1]);
        // Verify.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0.3);
        $this->check_answer_note('firsttree', 'ATEqualComAss (AlgEquiv-false). | firsttree-1-F | firsttree-2-T');
        // This is the point of the test: we expect a zero penalty here in the 3rd argument.
        $this->check_prt_score('firsttree', 0.5, 0);
        $this->render();
        $expected = 'Seed: 1; ans1: 3 [score]; firsttree: # = 0.5 | ATEqualComAss (AlgEquiv-false). | ' .
                'firsttree-1-F | firsttree-2-T';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '3');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('firsttree');
        $this->check_output_does_not_contain_stray_placeholders();

        // Submit again and check penalty.
        $this->process_submission(['ans1' => '2', 'ans1_val' => '3', '-submit' => 1]);
        $this->check_current_state(question_state::$todo);
        // Mark from previous attempt is non-zero, even at the validate stage.
        $this->check_current_mark(0.3);
        $this->check_prt_score('firsttree', null, null);

        $this->process_submission(['ans1' => '2', 'ans1_val' => '2', '-submit' => 1]);
        $this->check_current_state(question_state::$complete);
        $this->check_current_mark(0.8);
        $this->check_answer_note('firsttree', 'firsttree-1-T');
        $this->check_prt_score('firsttree', 1, 0);
        $this->render();
        $expected = 'Seed: 1; ans1: 2 [score]; firsttree: # = 1 | firsttree-1-T';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '2');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('firsttree');
        $this->check_output_does_not_contain_stray_placeholders();
    }

    public function test_unitsoptions(): void {

        $q = \test_question_maker::make_question('stack', 'unitsoptions');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->assertEquals('adaptivemultipart',
                $this->quba->get_question_attempt($this->slot)->get_behaviour_name());
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                new question_pattern_expectation('/gravity/'),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );

        // Process a validate request.
        // Notice here we get away with including single letter question variables in the answer.
        $this->process_submission(['ans1' => '9.8100*m/s^2', '-submit' => 1]);
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $expected = 'Seed: 1; ans1: 9.8100*m/s^2 [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '9.8100*m/s^2');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submit of the incorrect answer (too many sig figs).
        $this->process_submission(['ans1' => '9.8100*m/s^2', 'ans1_val' => '9.8100*m/s^2', '-submit' => 1]);
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        $this->check_prt_score('firsttree', 0, 0.2);
        $this->render();
        $expected = 'Seed: 1; ans1: 9.8100*m/s^2 [score]; firsttree: # = 0 | ATNumSigFigs_WrongDigits. ' .
                'ATUnits_units_match. | firsttree-1-F';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '9.8100*m/s^2');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('firsttree');
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a validate request.
        $this->process_submission(['ans1' => '9.81*m/s^2', '-submit' => 1]);
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $expected = 'Seed: 1; ans1: 9.81*m/s^2 [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '9.81*m/s^2');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submit of the incorrect answer (too many sig figs).
        $this->process_submission(['ans1' => '9.81*m/s^2', 'ans1_val' => '9.81*m/s^2', '-submit' => 1]);
        $this->check_current_state(question_state::$complete);
        $this->check_current_mark(0.8);
        $this->check_prt_score('firsttree', 1, 0);
        $this->render();
        $expected = 'Seed: 1; ans1: 9.81*m/s^2 [score]; firsttree: # = 1 | ATUnits_units_match. | firsttree-1-T';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '9.81*m/s^2');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('firsttree');
        $this->check_output_does_not_contain_stray_placeholders();
    }

    public function test_unitsmulti(): void {

        $q = \test_question_maker::make_question('stack', 'unitsmulti');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->assertEquals('adaptivemultipart',
            $this->quba->get_question_attempt($this->slot)->get_behaviour_name());
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_contains_text_input('ans2');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_pattern_expectation('/rate law for the reaction/'),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_does_not_contain_num_parts_correct(),
            $this->get_no_hint_visible_expectation()
            );

        // Process a validate request.
        $this->process_submission(['ans1' => 'A*k', 'ans2' => '', '-submit' => 1]);
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $expected = 'Seed: 1; ans1: A*k [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', 'A*k');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        $this->process_submission(['ans1' => 'A*k', 'ans1_val' => 'A*k', 'ans2' => '', '-submit' => 1]);
        // This example is complete because the PRT depends only on ans1.
        $this->check_current_state(question_state::$complete);
        $this->check_current_mark(1);
        $this->check_prt_score('firsttree', 1.0, 0.0);
        $this->render();
        $expected = 'Seed: 1; ans1: A*k [score]; firsttree: # = 1 | firsttree-1-T';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', 'A*k');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('firsttree');
        $this->check_output_does_not_contain_stray_placeholders();
    }

    public function test_equiv_quad_1(): void {

        // Create the stack question 'equiv_quad'.
        $q = \test_question_maker::make_question('stack', 'equiv_quad');
        $this->start_attempt_at_question($q, 'adaptive', 1);
        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->assertEquals('adaptivemultipart',
                $this->quba->get_question_attempt($this->slot)->get_behaviour_name());
        $this->render();
        $this->check_output_contains_textarea_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                new question_pattern_expectation('/Solve/'),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );

        $this->process_submission(['ans1' => 'x^2-3*x+2=0', '-submit' => 1]);
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $expected = 'Seed: 1; ans1: [x^2-3*x+2=0] [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_textarea_input('ans1', 'x^2-3*x+2=0');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        $this->process_submission(['ans1' => "x^2-3*x+2=0\n(x-2)*(x-1)=0", '-submit' => 1]);
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $expected = 'Seed: 1; ans1: [x^2-3*x+2=0,(x-2)*(x-1)=0] [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_textarea_input('ans1', "x^2-3*x+2=0\n(x-2)*(x-1)=0");
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        $this->process_submission(['ans1' => "x^2-3*x+2=0\n(x-2)*(x-1)=0\nx=-1 and x=-2", '-submit' => 1]);
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $expected = 'Seed: 1; ans1: [x^2-3*x+2=0,(x-2)*(x-1)=0,x=-1 and x=-2] [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_textarea_input('ans1', "x^2-3*x+2=0\n(x-2)*(x-1)=0\nx=-1 and x=-2");
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $expectedvalidation = '\\[ \\begin{array}{lll} &x^2-3\cdot x+2=0& \\cr '.
            '\\color{green}{\Leftrightarrow}&\\left(x-2\\right)\cdot \\left(x-1\\right)=0& \\cr '.
            '\\color{red}{?}&\\left\{\\begin{array}{l}x=-1\\cr x=-2\\cr \\end{array}\\right.& \\cr \\end{array} \]';
        $this->assert_content_with_maths_contains($expectedvalidation, $this->currentoutput);

        // @codingStandardsIgnoreStart
        $this->process_submission(array('ans1' => "x^2-3*x+2=0\n(x-2)*(x-1)=0\nx=-1 and x=-2",
                 'ans1_val' => "[x^2-3*x+2=0,(x-2)*(x-1)=0,x=-1 and x=-2]",'-submit' => 1));
        // @codingStandardsIgnoreEnd
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        $this->check_prt_score('firsttree', 0, 0.2);
        $this->check_answer_note('firsttree', '(EMPTYCHAR,EQUIVCHAR,QMCHAR) | firsttree-1-F');
        $this->render();
        $this->check_output_contains_textarea_input('ans1', "x^2-3*x+2=0\n(x-2)*(x-1)=0\nx=-1 and x=-2");
        $this->check_output_does_not_contain_stray_placeholders();

        $this->process_submission(['ans1' => "x^2-3*x+2=0\n(x-2)*(x-1)=0\nx=1 or x=2", '-submit' => 1]);
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $expected = 'Seed: 1; ans1: [x^2-3*x+2=0,(x-2)*(x-1)=0,x=1 or x=2] [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_textarea_input('ans1', "x^2-3*x+2=0\n(x-2)*(x-1)=0\nx=1 or x=2");
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $expectedvalidation = '\\[ \\begin{array}{lll} &x^2-3\\cdot x+2=0& \\cr '.
                '\\color{green}{\\Leftrightarrow}&\\left(x-2\\right)\\cdot \\left(x-1\\right)=0& \\cr '.
                '\\color{green}{\\Leftrightarrow}&x=1\\,{\\text{ or }}\\, x=2& \\cr \\end{array} \\]';
        $this->assert_content_with_maths_contains($expectedvalidation, $this->currentoutput);

        // @codingStandardsIgnoreStart
        $this->process_submission(array('ans1' => "x^2-3*x+2=0\n(x-2)*(x-1)=0\nx=1 or x=2",
                 'ans1_val' => "[x^2-3*x+2=0,(x-2)*(x-1)=0,x=1 or x=2]",'-submit' => 1));
        // @codingStandardsIgnoreEnd
        $this->check_current_state(question_state::$complete);
        $this->check_current_mark(0.8);
        $this->check_prt_score('firsttree', 1, 0);
        $this->check_answer_note('firsttree', '(EMPTYCHAR,EQUIVCHAR,EQUIVCHAR) | firsttree-1-T');
        $this->render();
        $expected = 'Seed: 1; ans1: [x^2-3*x+2=0,(x-2)*(x-1)=0,x=1 or x=2] [score]; firsttree: # = 1 | ' .
                '(EMPTYCHAR,EQUIVCHAR,EQUIVCHAR) | firsttree-1-T';
        $this->check_response_summary($expected);
        $this->check_output_contains_textarea_input('ans1', "x^2-3*x+2=0\n(x-2)*(x-1)=0\nx=1 or x=2");
        $this->check_output_does_not_contain_stray_placeholders();
        $expectedvalidation = '\\[ \\begin{array}{lll} &x^2-3\\cdot x+2=0& \\cr '.
                '\\color{green}{\\Leftrightarrow}&\\left(x-2\\right)\\cdot \\left(x-1\\right)=0& \\cr '.
                '\\color{green}{\\Leftrightarrow}&x=1\\,{\\text{ or }}\\, x=2& \\cr \\end{array} \\]';
        $this->assert_content_with_maths_contains($expectedvalidation, $this->currentoutput);
    }

    public function test_equiv_quad_first_line(): void {

        // Create the stack question 'equiv_quad'.
        $q = \test_question_maker::make_question('stack', 'equiv_quad');

        // Add in the option to force a particular first line.
        $q->inputs['ans1'] = stack_input_factory::make(
                'equiv', 'ans1', 'ta', null,
                ['boxWidth' => 20, 'forbidFloats' => false, 'options' => 'firstline']);

        $this->start_attempt_at_question($q, 'adaptive', 1);
        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->assertEquals('adaptivemultipart',
                $this->quba->get_question_attempt($this->slot)->get_behaviour_name());
        $this->render();
        $this->check_output_contains_textarea_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                new question_pattern_expectation('/Solve/'),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );

        // Get first line wrong.
        $this->process_submission(['ans1' => 'x^2-3*x+1=0', '-submit' => 1]);
        $this->check_current_state(question_state::$invalid);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $expected = 'Seed: 1; ans1: [x^2-3*x+1=0] [invalid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_textarea_input('ans1', 'x^2-3*x+1=0');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Get first line right.
        $this->process_submission(['ans1' => 'x^2-3*x+2=0', '-submit' => 1]);
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $expected = 'Seed: 1; ans1: [x^2-3*x+2=0] [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_textarea_input('ans1', 'x^2-3*x+2=0');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Get first line right up to commutativity.
        $this->process_submission(['ans1' => '2+x^2-3*x=0', '-submit' => 1]);
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $expected = 'Seed: 1; ans1: [2+x^2-3*x=0] [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_textarea_input('ans1', '2+x^2-3*x=0');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Get first line right up to algebraic equivalence.  This is not enough!
        $this->process_submission(['ans1' => '(x-1)*(x-2)=0', '-submit' => 1]);
        $this->check_current_state(question_state::$invalid);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $expected = 'Seed: 1; ans1: [(x-1)*(x-2)=0] [invalid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_textarea_input('ans1', '(x-1)*(x-2)=0');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
    }

    public function test_equiv_quad_hideequiv(): void {

        // Create the stack question 'equiv_quad'.
        $q = \test_question_maker::make_question('stack', 'equiv_quad');

        // Add in the option to suppress equivalence feedback.
        $q->inputs['ans1'] = stack_input_factory::make(
                'equiv', 'ans1', 'ta', null,
                ['boxWidth' => 20, 'forbidFloats' => false, 'options' => 'hideequiv']);

        $this->start_attempt_at_question($q, 'adaptive', 1);
        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->assertEquals('adaptivemultipart',
                $this->quba->get_question_attempt($this->slot)->get_behaviour_name());
        $this->render();
        $this->check_output_contains_textarea_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                new question_pattern_expectation('/Solve/'),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );

        $this->process_submission(['ans1' => "x^2-3*x+2=0\n(x-2)*(x-1)=0", '-submit' => 1]);
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $expected = 'Seed: 1; ans1: [x^2-3*x+2=0,(x-2)*(x-1)=0] [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_textarea_input('ans1', "x^2-3*x+2=0\n(x-2)*(x-1)=0");
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        // This non-trivial sumbission should be shown without equivalence symbols.
        $expectedvalidation = '\\[ \\begin{array}{lll}x^2-3\cdot x+2=0& \\cr '.
                '\\left(x-2\\right)\cdot \\left(x-1\\right)=0& \\cr \\end{array} \]';
        $this->assert_content_with_maths_contains($expectedvalidation, $this->currentoutput);
    }

    public function test_checkbox_empty(): void {

        $q = \test_question_maker::make_question('stack', 'checkbox_all_empty');

        $this->start_attempt_at_question($q, 'adaptive', 1);
        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->assertEquals('adaptivemultipart',
                $this->quba->get_question_attempt($this->slot)->get_behaviour_name());
        $this->render();
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                new question_pattern_expectation('/Which of/'),
                new question_pattern_expectation('/diamond/'),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
                );
    }

    public function test_checkbox_union(): void {

        $q = \test_question_maker::make_question('stack', 'checkbox_union');

        $this->start_attempt_at_question($q, 'adaptive', 1);
        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->assertEquals('adaptivemultipart',
            $this->quba->get_question_attempt($this->slot)->get_behaviour_name());
        $this->render();
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_pattern_expectation('/are is the domain/'),
            new question_pattern_expectation('/cup/'),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_does_not_contain_num_parts_correct(),
            $this->get_no_hint_visible_expectation()
            );
    }

    public function test_checkbox_noun_diff(): void {

        $q = \test_question_maker::make_question('stack', 'checkbox_noun_diff');

        $this->start_attempt_at_question($q, 'adaptive', 1);
        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->assertEquals('adaptivemultipart',
            $this->quba->get_question_attempt($this->slot)->get_behaviour_name());
        $this->render();
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_pattern_expectation('/operation should you use/'),
            new question_pattern_expectation('/mathrm/'),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_does_not_contain_num_parts_correct(),
            $this->get_no_hint_visible_expectation()
            );

        // Process a validate request.
        $this->process_submission(['ans1_1' => '1', 'submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', 1, 0);
        $this->render();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submition of the correct answer.
        $this->process_submission(['ans1_1' => '1', 'ans1_val' => '[\'diff(f,x,1)]', '-submit' => 1]);
        $this->check_current_state(question_state::$complete);
        $this->check_current_mark(1);
        $this->check_prt_score('firsttree', 1, 0);
        $this->render();
        $expected = 'Seed: 1; ans1: [noundiff(f,x)] [score]; firsttree: # = 1 | firsttree-1-T';
        $this->check_response_summary($expected);
        $this->check_output_contains_prt_feedback('firsttree');
        $this->check_output_does_not_contain_stray_placeholders();
    }

    public function test_test0_do_not_show_penalties(): void {

        // Create the stack question 'test0'.
        $q = \test_question_maker::make_question('stack', 'test0');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->assertEquals('adaptivemultipart',
                $this->quba->get_question_attempt($this->slot)->get_behaviour_name());
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                new question_pattern_expectation('/What is/'),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );

        // Process a validate request.
        $this->process_submission(['ans1' => '3', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $this->check_output_contains_text_input('ans1', '3');
        $this->check_output_contains_input_validation('ans1');
        // Since the answer is a number there are no variables.
        $this->check_output_does_not_contain_lang_string('studentValidation_listofvariables',
                'qtype_stack', '\( \left[ x \right]\)');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submition of an incorrect answer.
        $this->process_submission(['ans1' => '3', 'ans1_val' => '3', '-submit' => 1]);

        // Verify.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        $this->check_prt_score('firsttree', 0, 0.3);

        $this->displayoptions->marks = question_display_options::MARK_AND_MAX;
        $this->render();
        $expected = 'Seed: 1; ans1: 3 [score]; firsttree: # = 0 | ATEqualComAss (AlgEquiv-false). | firsttree-1-F';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '3');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('firsttree');
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_output_contains_lang_string('gradingdetails', 'quiz', ['raw' => '0.00', 'max' => '1.00']);
        $this->check_output_contains_lang_string('gradingdetailspenalty', 'quiz', '0.30');

        // Change the display options.
        $this->displayoptions->marks = question_display_options::MAX_ONLY;
        $this->render();
        $expected = 'Seed: 1; ans1: 3 [score]; firsttree: # = 0 | ATEqualComAss (AlgEquiv-false). | firsttree-1-F';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '3');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('firsttree');
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_output_does_not_contain_lang_string('gradingdetails', 'quiz', ['raw' => '0.00', 'max' => '1.00']);
        $this->check_output_does_not_contain_lang_string('gradingdetailspenalty', 'quiz', '0.30');
    }

    public function test_test_stringsloppy(): void {

        // Create the stack question 'stringsloppy'.
        $q = \test_question_maker::make_question('stack', 'stringsloppy');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->assertEquals('adaptivemultipart',
                $this->quba->get_question_attempt($this->slot)->get_behaviour_name());
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                new question_pattern_expectation('/side lengths of a right angled/'),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );

        // Process a validate request.
        $this->process_submission(['ans1' => 'Thales Theorem', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $this->check_output_contains_text_input('ans1', 'Thales Theorem');
        $this->check_output_contains_input_validation('ans1');
        // Since the answer is a number there are no variables.
        $this->check_output_does_not_contain_lang_string('studentValidation_listofvariables',
                'qtype_stack', '\( \left[ x \right]\)');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submition of an incorrect answer.
        $this->process_submission(['ans1' => 'Thales Theorem', 'ans1_val' => '"Thales Theorem"', '-submit' => 1]);
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        $this->check_prt_score('firsttree', 0, 0.4);
        $this->check_answer_note('firsttree', 'firsttree-1-F | firsttree-2-F');

        // Process the correct answer.
        $this->process_submission(['ans1' => 'Pythagoras\' Theorem', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $expected = 'Seed: 1; ans1: "Pythagoras\' Theorem" [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', 'Pythagoras\' Theorem');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        $this->process_submission([
            'ans1' => 'Pythagoras\' Theorem', 'ans1_val' => '"Pythagoras\' Theorem"',
            '-submit' => 1,
        ]);
        $this->check_current_state(question_state::$complete);
        $this->check_current_mark(0.6);
        $this->check_prt_score('firsttree', 1, 0);
        $this->check_answer_note('firsttree', 'firsttree-1-T');

        // Process the correct answer in lower case.
        $this->process_submission(['ans1' => 'pythagoras\' theorem', '-submit' => 1]);

        $this->check_current_state(question_state::$complete);
        $this->check_current_mark(0.6);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $expected = 'Seed: 1; ans1: "pythagoras\' theorem" [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', 'pythagoras\' theorem');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        $this->process_submission([
            'ans1' => 'pythagoras\' theorem', 'ans1_val' => '"pythagoras\' theorem"',
            '-submit' => 1,
        ]);
        $this->check_current_state(question_state::$complete);
        $this->check_current_mark(0.6);
        $this->check_prt_score('firsttree', 0.75, 0.4);
        $this->check_answer_note('firsttree', 'firsttree-1-F | firsttree-2-T');
    }

    public function test_test_sregexp(): void {

        // Create the stack question 'stringsloppy'.
        $q = \test_question_maker::make_question('stack', 'sregexp');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->assertEquals('adaptivemultipart',
                $this->quba->get_question_attempt($this->slot)->get_behaviour_name());
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                new question_pattern_expectation('/Input a word of the language decribed by/'),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
        );

        // Process a validate request.
        $this->process_submission(['ans1' => 'acde', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $expected = 'Seed: 1; ans1: "acde" [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', 'acde');
        $this->check_output_contains_input_validation('ans1');
        // Since the answer is a number there are no variables.
        $this->check_output_does_not_contain_lang_string('studentValidation_listofvariables',
                'qtype_stack', '\( \left[ x \right]\)');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submition of an incorrect answer.
        $this->process_submission(['ans1' => 'acde', 'ans1_val' => '"acde"', '-submit' => 1]);
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        $this->check_prt_score('firsttree', 0, 0.4);
        $this->check_answer_note('firsttree', 'firsttree-1-F');

        // Process the correct answer.
        $this->process_submission(['ans1' => 'ccccb', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $expected = 'Seed: 1; ans1: "ccccb" [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', 'ccccb');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        $this->process_submission(['ans1' => 'ccccb', 'ans1_val' => '"ccccb"', '-submit' => 1]);
        $this->check_current_state(question_state::$complete);
        $this->check_current_mark(0.6);
        $this->check_prt_score('firsttree', 1, 0);
        $this->check_answer_note('firsttree', 'ATSRegExp: ["cccb","ccc"]. | firsttree-1-T');
    }

    public function test_test_feedbackstyle(): void {

        // Create the stack question 'feedbackstyle'.
        $q = \test_question_maker::make_question('stack', 'feedbackstyle');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->assertEquals('adaptivemultipart',
                $this->quba->get_question_attempt($this->slot)->get_behaviour_name());
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_contains_text_input('ans2');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                new question_pattern_expectation('/Give two examples of odd functions./'),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
                );

        // Process a validate request, with two wrong answers.
        $this->process_submission(['ans1' => 'x^2', 'ans2' => 'x^4', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('prt1', null, null);
        $this->check_prt_score('prt2', null, null);
        $this->check_prt_score('prt3', null, null);
        $this->render();
        $expected = 'Seed: 1; ans1: x^2 [valid]; ans2: x^4 [valid]; prt1: !; prt2: !; prt3: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', 'x^2');
        $this->check_output_contains_text_input('ans2', 'x^4');
        $this->check_output_contains_input_validation_compact('ans1');
        $this->check_output_contains_input_validation_compact('ans2');
        $this->check_output_does_not_contain_lang_string('studentValidation_listofvariables',
                'qtype_stack', '\( \left[ x \right]\)');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                new question_pattern_expectation('/Give two examples of odd functions./'),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
                );

        // Process a submition of an incorrect answer.
        $this->process_submission([
            'ans1' => 'x^2', 'ans1_val' => 'x^2',
            'ans2' => 'x^4', 'ans2_val' => 'x^4', '-submit' => 1,
        ]);
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        $this->check_prt_score('prt1', 0, 0.4);
        $this->check_answer_note('prt1', 'prt1-1-F');
        $this->check_prt_score('prt2', 0, 0.4);
        $this->check_answer_note('prt2', 'prt2-1-F');
        $this->check_prt_score('prt3', 0.5, 0.2);
        $this->check_answer_note('prt3', 'ATLogic_True. | prt3-1-T');
        $this->render();
        $expected = 'Seed: 1; ans1: x^2 [score]; ans2: x^4 [score]; prt1: # = 0 | prt1-1-F; prt2: # = 0 | ' .
                'prt2-1-F; prt3: # = 0.5 [formative] | ATLogic_True. | prt3-1-T';
        $this->check_response_summary($expected);
        $this->check_current_output(
                new question_pattern_expectation('/Give two examples of odd functions./'),
                // The first PRT is "Compact", so the feedback is there.
                new question_pattern_expectation('/Your first function is not odd/'),
                // The second  PRT is "Symbolic", so the feedback is not there, but the symbol is.
                new question_pattern_expectation('/stackprtfeedback stackprtfeedback-prt2/'),
                new question_pattern_expectation('/Try to think of something more imaginative than just polynomials/'),
                $this->get_no_hint_visible_expectation()
                );

        // Process the correct answer (without a validation step).
        $this->process_submission([
            'ans1' => 'x^3', 'ans1_val' => 'x^3',
            'ans2' => 'sin(x)', 'ans2_val' => 'sin(x)', '-submit' => 1,
        ]);

        $this->check_current_state(question_state::$complete);
        $this->check_current_mark(0.6);
        $this->check_prt_score('prt1', 1, 0);
        $this->check_answer_note('prt1', 'prt1-1-T');
        $this->check_prt_score('prt2', 1, 0);
        $this->check_answer_note('prt2', 'prt2-1-T');
        $this->check_prt_score('prt3', 0.4, 0.2);
        $this->check_answer_note('prt3', 'prt3-1-F');
        $this->render();
        $expected = 'Seed: 1; ans1: x^3 [score]; ans2: sin(x) [score]; prt1: # = 1 | prt1-1-T; prt2: # = 1 | ' .
                'prt2-1-T; prt3: # = 0.4 [formative] | prt3-1-F';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', 'x^3');
        $this->check_output_contains_text_input('ans2', 'sin(x)');
        $this->check_output_contains_input_validation_compact('ans1');
        $this->check_output_contains_input_validation_compact('ans2');
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                new question_pattern_expectation('/Give two examples of odd functions./'),
                new question_pattern_expectation('/Non-polynomials included./'),
                $this->get_no_hint_visible_expectation()
                );
    }

    public function test_test_contextvars(): void {

        $q = \test_question_maker::make_question('stack', 'contextvars');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        $generalfeedback = $q->get_generalfeedback_castext();
        $expected = 'You should be able to type in \\({\diamond}\\) as <code>blob</code>.';
        $this->assertEquals($expected, $generalfeedback->get_rendered());

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->assertEquals('adaptivemultipart',
                $this->quba->get_question_attempt($this->slot)->get_behaviour_name());
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                new question_pattern_expectation('/diamond/'),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
                );

        // Process a validate request with a texput function.
        $this->process_submission(['ans1' => 'vec(x)', '-submit' => 1]);
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $this->check_output_contains_text_input('ans1', 'vec(x)');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->assert_content_with_maths_contains('\[ {\bf x} \]', $this->currentoutput);
        $expected = 'Seed: 1; ans1: vec(x) [valid]; firsttree: !';
        $this->check_response_summary($expected);

        // Process a validate request with a texput function.
        $this->process_submission(['ans1' => 'tup(3,4)', '-submit' => 1]);
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $this->check_output_contains_text_input('ans1', 'tup(3,4)');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->assert_content_with_maths_contains('\[ \left[3,4\right) \]', $this->currentoutput);
        $expected = 'Seed: 1; ans1: tup(3,4) [valid]; firsttree: !';
        $this->check_response_summary($expected);

        // Process a validate request.
        $this->process_submission(['ans1' => 'log(blob)', '-submit' => 1]);
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $this->check_output_contains_text_input('ans1', 'log(blob)');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->assert_content_with_maths_contains('\[ \log \left( \diamond \right) \]', $this->currentoutput);
        $expected = 'Seed: 1; ans1: log(blob) [valid]; firsttree: !';
        $this->check_response_summary($expected);

        // Process a submition of an incorrect answer.
        $this->process_submission(['ans1' => 'log(blob)', 'ans1_val' => 'log(blob)', '-submit' => 1]);
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        $this->check_prt_score('firsttree', 0, 0.35);
        $this->check_answer_note('firsttree', 'firsttree-1-F | firsttree-2-F');
        $this->assert_content_with_maths_contains('\[ \log \left( \diamond \right) \]', $this->currentoutput);
        $expected = 'Seed: 1; ans1: log(blob) [score]; firsttree: # = 0 | firsttree-1-F | firsttree-2-F';
        $this->check_response_summary($expected);

        // Process the correct answer.  Needs the assumption x>2 for ATAlgEquiv to correctly work.
        $this->process_submission(['ans1' => '6*((x-2)^2)^k', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $expected = 'Seed: 1; ans1: 6*((x-2)^2)^k [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '6*((x-2)^2)^k');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        $this->process_submission([
            'ans1' => '6*((x-2)^2)^k', 'ans1_val' => '6*((x-2)^2)^k',
            '-submit' => 1,
        ]);
        $this->check_current_state(question_state::$complete);
        $this->check_current_mark(0.65);
        $this->check_prt_score('firsttree', 1, 0);
        $this->check_answer_note('firsttree', 'firsttree-1-T');
    }

    public function test_test_contextvars_feedbackvars(): void {

        // Create a situation which requires contextvars defined only in the feedbackvars.
        $q = \test_question_maker::make_question('stack', 'contextvars');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->assertEquals('adaptivemultipart',
                $this->quba->get_question_attempt($this->slot)->get_behaviour_name());
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                new question_pattern_expectation('/diamond/'),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
                );

        // Process a validate request.
        $this->process_submission(['ans1' => '(a^x)^y', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $this->check_output_contains_text_input('ans1', '(a^x)^y');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submition of an answer which is only partially correct
        // because of an assume in the feedback variables.
        $this->process_submission(['ans1' => '(a^x)^y', 'ans1_val' => '(a^x)^y', '-submit' => 1]);
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0.6);
        $this->check_prt_score('firsttree', 0.6, 0.35);
        $this->check_answer_note('firsttree', 'firsttree-1-F | firsttree-2-T');

    }

    public function test_multilang(): void {

        $q = \test_question_maker::make_question('stack', 'multilang');

        $this->start_attempt_at_question($q, 'adaptive', 1);
        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->assertEquals('adaptivemultipart',
            $this->quba->get_question_attempt($this->slot)->get_behaviour_name());
        $this->render();
        // Castext syntax hint fills the initial matrix with zeros.
        $this->check_output_contains_text_input('ans1_sub_0_0', '0');
        $this->check_output_contains_text_input('ans1_sub_0_1', '0');
        $this->check_output_contains_text_input('ans1_sub_1_0', '0');
        $this->check_output_contains_text_input('ans1_sub_1_1', '0');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_pattern_expectation('/Compute the sum/'),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_does_not_contain_num_parts_correct(),
            $this->get_no_hint_visible_expectation()
            );

        $expected = [
            'questiontext' => 'The language tags found in your question are: en, fi.',
            0 => '<i class="icon fa fa-exclamation-circle text-danger fa-fw " title="There are potential '
                . 'language problems in your question." aria-label="There are potential language problems '
                . 'in your question."></i>There are potential language problems in your question.',
            1 => 'The language tag fi is missing from the following: firsttree-1-F.',
        ];
        $warnings = $q->validate_warnings();
        $this->assertEquals($expected, $warnings);
    }

    public function test_lang_blocks_en(): void {

        // TO-DO: how do we explicitly set the user's preferences, i.e. language?
        $q = \test_question_maker::make_question('stack', 'lang_blocks');
        $this->start_attempt_at_question($q, 'adaptive', 1);
        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->assertEquals('adaptivemultipart',
            $this->quba->get_question_attempt($this->slot)->get_behaviour_name());
        $this->render();
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_output_contains_text_input('ans1', 'An expression, e.g. x^2');
        $this->check_current_output(
            new question_pattern_expectation('/Give an example of a function/'),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_does_not_contain_num_parts_correct(),
            $this->get_no_hint_visible_expectation()
            );
        $this->check_output_does_not_contain_text('Giv et eksempel');

        // Process a validate request.
        $this->process_submission(['ans1' => 'x^3', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('prt1', null, null);
        $this->render();
        $this->check_output_contains_text_input('ans1', 'x^3');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a submition of an answer which is only partially correct.
        $this->process_submission(['ans1' => 'x^3', 'ans1_val' => 'x^3', '-submit' => 1]);
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(0);
        $this->check_prt_score('prt1', 0.0, 0.35);
        $this->check_response_summary('Seed: 1; ans1: x^3 [score]; prt1: # = 0 | prt1-1-F');
        $this->check_answer_note('prt1', 'prt1-1-F');
        $this->render();
        $this->check_current_output(
            new question_pattern_expectation('/Give an example of a function/'),
            new question_pattern_expectation('/However, in your answer/'),
            $this->get_no_hint_visible_expectation()
            );
        // Danish should not be seen in the output.
        $this->check_output_does_not_contain_text('Men i dit svar er');
    }

    public function test_block_locals(): void {

        $q = \test_question_maker::make_question('stack', 'block_locals');

        $this->start_attempt_at_question($q, 'adaptive', 1);
        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->assertEquals('adaptivemultipart',
                $this->quba->get_question_attempt($this->slot)->get_behaviour_name());
        $this->render();
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
                new question_pattern_expectation('/with input/'),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_no_hint_visible_expectation()
                );

        $this->process_submission(['ans1' => 'p^2+p+1', '-submit' => 1]);

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $this->check_output_contains_text_input('ans1', 'p^2+p+1');
        $this->check_output_contains_input_validation('ans1');

        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $expected = 'Seed: 1; ans1: p^2+p+1 [valid]; firsttree: !';
        $this->check_response_summary($expected);

        // Process a submit of the correct answer.
        $this->process_submission(['ans1' => 'p^2+p+1', 'ans1_val' => 'p^2+p+1', '-submit' => 1]);

        // Verify.
        $this->check_current_state(question_state::$complete);
        $this->check_current_mark(1);
        $this->check_prt_score('firsttree', 1, 0);
        $this->render();
        $this->check_output_contains_text_input('ans1', 'p^2+p+1');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_contains_prt_feedback('firsttree');
        $this->check_output_does_not_contain_stray_placeholders();
        $expected = 'Seed: 1; ans1: p^2+p+1 [score]; firsttree: # = 1 | firsttree-0-1';
        $this->check_response_summary($expected);
    }

    public function test_test0_debug(): void {

        // Create the stack question 'test0'.
        $q = test_question_maker::make_question('stack', 'test0');
        $q->questiontext = $q->questiontext . ' [[ debug /]]';
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->assertEquals('adaptivemultipart',
            $this->quba->get_question_attempt($this->slot)->get_behaviour_name());
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_pattern_expectation('/Simplified displayed value/'),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_does_not_contain_num_parts_correct(),
            $this->get_no_hint_visible_expectation()
            );
    }

    public function test_test3_debug(): void {

        $q = test_question_maker::make_question('stack', 'test3');
        $q->questiontext = $q->questiontext . ' [[ debug /]]';
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->assertEquals('adaptivemultipart',
            $this->quba->get_question_attempt($this->slot)->get_behaviour_name());
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_pattern_expectation(
                '/This question has no variables to debug here! ' .
                'Display question variables in question text and feedback variables in node feedback./'
            ),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_does_not_contain_num_parts_correct(),
            $this->get_no_hint_visible_expectation()
            );
    }

    public function test_input_validator(): void {

        global $USER;
        $this->resetAfterTest();
        $USER->lang = '';
        set_config('lang', 'en');

        $q = test_question_maker::make_question('stack', 'validator');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_pattern_expectation('/What is/'),
            new question_no_pattern_expectation('/Was ist/'),
            new question_no_pattern_expectation('/Mikä on/'),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_does_not_contain_num_parts_correct(),
            $this->get_no_hint_visible_expectation()
            );

        // Process an invalidate request.
        $ia = 'x^2-1';
        $this->process_submission(['ans1' => $ia, '-submit' => 1]);

        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();

        $expected = 'Seed: 1; ans1: x^2-1 [invalid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', $ia);
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_pattern_expectation('/Your answer <span class="nolink">/'),
            new question_pattern_expectation('/{x\^2-1}/'),
            new question_pattern_expectation('/contains the wrong variables/'),
            new question_no_pattern_expectation('/Vastauksesi sisältää/')
            );

        // Process a validate request.
        $ia = 'phi^2-1';
        $this->process_submission(['ans1' => $ia, '-submit' => 1]);

        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();

        $expected = 'Seed: 1; ans1: phi^2-1 [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', $ia);
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a score request.
        $ia = 'phi^2-1';
        $this->process_submission(['ans1' => $ia, 'ans1_val' => $ia, '-submit' => 1]);

        $this->check_current_mark(1);
        $this->check_prt_score('firsttree', 1, 0);
        $this->render();

        $expected = 'Seed: 1; ans1: phi^2-1 [score]; firsttree: # = 1 | firsttree-0-1';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', $ia);
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_pattern_expectation('/true answer/'),
            new question_no_pattern_expectation('/richtig/'),
            new question_no_pattern_expectation('/oikea/')
            );
    }

    public function test_input_validator_jp(): void {

        global $USER;
        // This language is not in the question, so should default back to English.
        $this->resetAfterTest();
        $USER->lang = '';
        set_config('lang', 'jp');

        $q = test_question_maker::make_question('stack', 'validator');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_pattern_expectation('/What is/'),
            new question_no_pattern_expectation('/Was ist/'),
            new question_no_pattern_expectation('/Mikä on/'),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_does_not_contain_num_parts_correct(),
            $this->get_no_hint_visible_expectation()
            );

        // Process an invalidate request.
        $ia = 'x^2-1';
        $this->process_submission(['ans1' => $ia, '-submit' => 1]);

        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();

        $expected = 'Seed: 1; ans1: x^2-1 [invalid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', $ia);
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_pattern_expectation('/Your answer <span class="nolink">/'),
            new question_pattern_expectation('/{x\^2-1}/'),
            new question_pattern_expectation('/contains the wrong variables/'),
            new question_no_pattern_expectation('/Vastauksesi sisältää/')
            );

        // Process a validate request.
        $ia = 'phi^2-1';
        $this->process_submission(['ans1' => $ia, '-submit' => 1]);

        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();

        $expected = 'Seed: 1; ans1: phi^2-1 [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', $ia);
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a score request.
        $ia = 'phi^2-1';
        $this->process_submission(['ans1' => $ia, 'ans1_val' => $ia, '-submit' => 1]);

        $this->check_current_mark(1);
        $this->check_prt_score('firsttree', 1, 0);
        $this->render();

        $expected = 'Seed: 1; ans1: phi^2-1 [score]; firsttree: # = 1 | firsttree-0-1';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', $ia);
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_pattern_expectation('/true answer/'),
            new question_no_pattern_expectation('/richtig/'),
            new question_no_pattern_expectation('/oikea/')
        );
    }

    public function test_input_validator_fi(): void {

        global $USER;
        // This language is in the question.
        $this->resetAfterTest();
        $USER->lang = '';
        set_config('lang', 'fi');

        $q = test_question_maker::make_question('stack', 'validator');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_no_pattern_expectation('/What is/'),
            new question_no_pattern_expectation('/Was ist/'),
            // The full string expectation is Mikä on.
            // However, SBCL on github actions does not support unicode, so the accents do not show.
            new question_pattern_expectation('/Mik/'),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_does_not_contain_num_parts_correct(),
            $this->get_no_hint_visible_expectation()
        );

        // Process an invalidate request.
        $ia = 'x^2-1';
        $this->process_submission(['ans1' => $ia, '-submit' => 1]);

        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();

        $expected = 'Seed: 1; ans1: x^2-1 [invalid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', $ia);
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_no_pattern_expectation('/Your answer contains the wrong variables/'),
            // The full string expectation is Vastauksesi sisältää.
            // However, SBCL on github actions does not support unicode, so the accents do not show.
            new question_pattern_expectation('/Vastauksesi/')
            );

        // Process a validate request.
        $ia = 'phi^2-1';
        $this->process_submission(['ans1' => $ia, '-submit' => 1]);

        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();

        $expected = 'Seed: 1; ans1: phi^2-1 [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', $ia);
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();

        // Process a score request.
        $ia = 'phi^2-1';
        $this->process_submission(['ans1' => $ia, 'ans1_val' => $ia, '-submit' => 1]);

        $this->check_current_mark(1);
        $this->check_prt_score('firsttree', 1, 0);
        $this->render();

        $expected = 'Seed: 1; ans1: phi^2-1 [score]; firsttree: # = 1 | firsttree-0-1';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', $ia);
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_no_pattern_expectation('/true answer/'),
            new question_no_pattern_expectation('/richtig/'),
            new question_pattern_expectation('/oikea/')
        );
    }

    public function test_input_validator_seed(): void {

        $q = test_question_maker::make_question('stack', 'validator_seed');
        $this->start_attempt_at_question($q, 'adaptive', 1, 2);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_pattern_expectation('/The value of ta is/'),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_does_not_contain_num_parts_correct(),
            $this->get_no_hint_visible_expectation()
        );

        // Process an invalid request.
        $ia = 'x';
        $this->process_submission(['ans1' => $ia, '-submit' => 1]);

        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();

        $expected = 'Seed: 1729; ans1: x [invalid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', $ia);
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_pattern_expectation('/The value of ta in the validator is 21, using seed 1729/')
        );
    }

    public function test_input_feedback(): void {

        $q = test_question_maker::make_question('stack', 'feedback');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('ans', null, null);
        $this->render();
        $this->check_output_contains_text_input('ans1', '{?,?,...,?}');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_pattern_expectation('/Find all the complex solutions of the equation/'),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_does_not_contain_num_parts_correct(),
            $this->get_no_hint_visible_expectation()
            );

        // Process an invalidate request.
        $ia = '1+i';
        $this->process_submission(['ans1' => $ia, '-submit' => 1]);

        $this->check_current_mark(null);
        $this->check_prt_score('ans', null, null);
        $this->render();

        $expected = 'Seed: 1; ans1: 1+i [invalid]; ans: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', $ia);
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_pattern_expectation('/Your answer should be a set, but is not./'),
            new question_pattern_expectation('/Remember to enter sets!/'),
            );

        // Process a validate request.
        $ia = '{4}';
        $this->process_submission(['ans1' => $ia, '-submit' => 1]);

        $this->check_current_mark(null);
        $this->check_prt_score('ans', null, null);
        $this->render();

        $expected = 'Seed: 1; ans1: {4} [valid]; ans: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', $ia);
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_pattern_expectation('/Remember to enter sets!/'),
            );

        // Process a score request.
        $ia = '{4}';
        $this->process_submission(['ans1' => $ia, 'ans1_val' => $ia, '-submit' => 1]);

        $this->check_current_mark(0.3);
        $this->check_prt_score('ans', 0.3, 0.1);
        $this->render();

        $expected = 'Seed: 1; ans1: {4} [score]; ans: # = 0.3 | ATSets_missingentries. | ans-0-F | ans-1-T';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', $ia);
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_pattern_expectation('/Remember to enter sets!/'),
            new question_pattern_expectation('/There are more answers that just the single real number./'),
            );

        // Process incorrect answer..
        $ia = '{4,4*((-(sqrt(3)*%i)/2)-1/2),4*((sqrt(3)*%i)/2-1/2)}';
        $this->process_submission(['ans1' => $ia, '-submit' => 1]);

        $this->check_current_mark(0.3);
        $this->check_prt_score('ans', null, null);
        $this->render();

        $expected = 'Seed: 1; ans1: {4,4*((-(sqrt(3)*%i)/2)-1/2),4*((sqrt(3)*%i)/2-1/2)} [valid]; ans: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', $ia);
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_pattern_expectation('/Remember to enter sets!/'),
            );

        // Process a score request.
        $ia = '{4,4*((-(sqrt(3)*%i)/2)-1/2),4*((sqrt(3)*%i)/2-1/2)}';
        $this->process_submission(['ans1' => $ia, 'ans1_val' => $ia, '-submit' => 1]);
        $expected = 'Seed: 1; ans1: {4,4*((-(sqrt(3)*%i)/2)-1/2),4*((sqrt(3)*%i)/2-1/2)} [score]; ans: # = 0 | ' .
            'ATSets_wrongentries. ATSets_missingentries. | ans-0-F | ATSet_wrongsz. | ans-1-F | ATSet_wrongsz. | ans-2-F';
        $this->check_response_summary($expected);

        $this->check_current_mark(0.3);
        $this->check_prt_score('ans', 0, 0.1);
        $this->render();

        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', $ia);
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_pattern_expectation('/The following are missing from your set./'),
            new question_pattern_expectation('/Remember to enter sets!/'),
            );

        // Jump to a score request.
        $ia = '{4,4*i,-4*i,-4}';
        $this->process_submission(['ans1' => $ia, 'ans1_val' => $ia, '-submit' => 1]);
        $expected = 'Seed: 1; ans1: {4,4*i,-4*i,-4} [score]; ans: # = 1 | ans-0-T';
        $this->check_response_summary($expected);

        $this->check_current_mark(0.8);
        $this->check_prt_score('ans', 1, 00);
        $this->render();

        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', $ia);
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_no_pattern_expectation('/The following are missing from your set./'),
            new question_pattern_expectation('/Remember to enter sets!/'),
            );
    }

    public function test_input_validator_texput(): void {

        global $USER;
        $this->resetAfterTest();
        $USER->lang = '';
        set_config('lang', 'en');

        $q = test_question_maker::make_question('stack', 'validator');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_pattern_expectation('/What is/'),
            new question_no_pattern_expectation('/Was ist/'),
            new question_no_pattern_expectation('/Mikä on/'),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_does_not_contain_num_parts_correct(),
            $this->get_no_hint_visible_expectation()
            );

        // Process a validate request.
        $ia = 'foo(x,y)';
        $this->process_submission(['ans1' => $ia, '-submit' => 1]);

        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();

        // This is invalid because it has the wrong variables!
        $expected = 'Seed: 1; ans1: foo(x,y) [invalid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', $ia);
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        // This vaidation output is the result of a texput command with a lambda function.
        $this->assert_content_with_maths_contains('\\frac{x}{y}', $this->currentoutput);
    }

    public function test_input_validator_ordergreat(): void {

        $q = test_question_maker::make_question('stack', 'ordergreat');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $this->check_output_contains_text_input('ansq');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_pattern_expectation('/What is/'),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_does_not_contain_num_parts_correct(),
            $this->get_no_hint_visible_expectation()
            );
        $this->assert_content_with_maths_contains('\({3\cdot x+5\cdot y=1}\)', $this->currentoutput);

        // Process a validate request.
        $ia = '7*y+3*x=1';
        $this->process_submission(['ansq' => $ia, '-submit' => 1]);

        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();

        // Check order of variables in validation of answer matches what students type.
        $expected = 'Seed: 1; ansq: 7*y+3*x=1 [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ansq', $ia);
        $this->check_output_contains_input_validation('ansq');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->assert_content_with_maths_contains('\[ 7\cdot y+3\cdot x=1', $this->currentoutput);
        // Checks {@pi@} is the value of the constant.
        $this->assert_content_with_maths_contains('\({3.1416}\)', $this->currentoutput);

        // Jump to score requests for different answers.
        $ia = '7*y+3*x=1';
        $this->process_submission(['ansq' => $ia, 'ansq_val' => $ia, '-submit' => 1]);
        $this->render();
        $this->assert_content_with_maths_contains('\[ 7\cdot y+3\cdot x=1', $this->currentoutput);
        $expected = 'Seed: 1; ansq: 7*y+3*x=1 [score]; firsttree: # = 0 | ATCASEqual_false. | firsttree-0-0';
        $this->check_response_summary($expected);

        $ia = '5*y+3*x=1';
        $this->process_submission(['ansq' => $ia, 'ansq_val' => $ia, '-submit' => 1]);
        $this->render();
        $this->assert_content_with_maths_contains('\[ 5\cdot y+3\cdot x=1', $this->currentoutput);
        $expected = 'Seed: 1; ansq: 5*y+3*x=1 [score]; firsttree: # = 0 | ATCASEqual (AlgEquiv-true)ATEquation_sides. ' .
            '| firsttree-0-0';
        $this->check_response_summary($expected);

        $ia = '3*x+5*y=1';
        $this->process_submission(['ansq' => $ia, 'ansq_val' => $ia, '-submit' => 1]);
        $this->render();
        $this->assert_content_with_maths_contains('\[ 3\cdot x+5\cdot y=1', $this->currentoutput);
        $expected = 'Seed: 1; ansq: 3*x+5*y=1 [score]; firsttree: # = 1 | ATCASEqual_true. | firsttree-0-1';
        $this->check_response_summary($expected);
    }

    public function test_input_validator_exdowncase(): void {

        $q = test_question_maker::make_question('stack', 'exdowncase');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $this->check_output_contains_text_input('ansq');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_pattern_expectation('/What is/'),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_does_not_contain_num_parts_correct(),
            $this->get_no_hint_visible_expectation()
            );
        $this->assert_content_with_maths_contains('\({13\cdot x^2-8\cdot y\cdot x+5\cdot y^2=49}\)',
            $this->currentoutput);

        // Process a validate request.
        $ia = '13*x^2-8*y*x+5*y^2 = 49';
        $this->process_submission(['ansq' => $ia, '-submit' => 1]);

        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();

        // Check order of variables in validation of answer matches what students type.
        $expected = 'Seed: 1; ansq: 13*x^2-8*y*x+5*y^2 = 49 [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ansq', $ia);
        $this->check_output_contains_input_validation('ansq');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->assert_content_with_maths_contains('\[ 13\cdot x^2-8\cdot y\cdot x+5\cdot y^2=49',
            $this->currentoutput);
        $ia = '13*x^2-8*y*x+5*y^2 = 49';
        $this->process_submission(['ansq' => $ia, 'ansq_val' => $ia, '-submit' => 1]);
        $this->render();
        $this->assert_content_with_maths_contains('\[ 13\cdot x^2-8\cdot y\cdot x+5\cdot y^2=49',
            $this->currentoutput);
        $expected = 'Seed: 1; ansq: 13*x^2-8*y*x+5*y^2 = 49 [score]; firsttree: # = 1 | ' .
            'ATCASEqual_true. | firsttree-0-1 | ATEquation_sides | firsttree-1-1';
        $this->check_response_summary($expected);

        // New answer with upper case.
        $ia = '13*X^2-8*y*x+5*y^2 = 49';
        $this->process_submission(['ansq' => $ia, 'ansq_val' => $ia, '-submit' => 1]);
        $this->render();
        $this->assert_content_with_maths_contains('\[ 13\cdot X^2-8\cdot y\cdot x+5\cdot y^2=49',
            $this->currentoutput);
        $expected = 'Seed: 1; ansq: 13*X^2-8*y*x+5*y^2 = 49 [score]; firsttree: # = 1 | ' .
            'ATCASEqual_true. | firsttree-0-1 | ATEquation_sides | firsttree-1-1';
        $this->check_response_summary($expected);

        // New answer which is wrong.
        $ia = '13*X^2-8*y*x+5*y^2 = 48';
        $this->process_submission(['ansq' => $ia, 'ansq_val' => $ia, '-submit' => 1]);
        $this->render();
        $this->assert_content_with_maths_contains('\[ 13\cdot X^2-8\cdot y\cdot x+5\cdot y^2=48',
            $this->currentoutput);
        $expected = 'Seed: 1; ansq: 13*X^2-8*y*x+5*y^2 = 48 [score]; firsttree: # = 0 | ' .
            'ATCASEqual_false. | firsttree-0-0 | ATEquation_lhs_notrhs | firsttree-1-0';
        $this->check_response_summary($expected);
    }

    public function test_input_validator_bailout(): void {

        $q = test_question_maker::make_question('stack', 'bailout');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_pattern_expectation('/What is/'),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_does_not_contain_num_parts_correct(),
            $this->get_no_hint_visible_expectation()
            );
        $this->assert_content_with_maths_contains('\({\left \{1 , 2 , 3 \right \}}\)',
            $this->currentoutput);

        // Process a validate request.
        $this->process_submission(['ans1' => '3', '-submit' => 1]);

        $this->check_current_mark(null);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        // Check order of variables in validation of answer matches what students type.
        $expected = 'Seed: 1; ans1: 3 [valid]; firsttree: !';
        $this->check_response_summary($expected);
        $this->check_output_contains_text_input('ans1', '3');
        $this->check_output_contains_input_validation('ans1');
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->assert_content_with_maths_contains('\[ 3',
            $this->currentoutput);
        $ia = '3';
        $this->process_submission(['ans1' => $ia, 'ans1_val' => $ia, '-submit' => 1]);
        $this->render();
        $this->assert_content_with_maths_contains('\[ 3',
            $this->currentoutput);
        $expected = 'Seed: 1; ans1: 3 [score]; firsttree: # = 1 | firsttree-0-1';
        $this->check_response_summary($expected);

        // New answer which bailed out.
        $ia = '5';
        $this->process_submission(['ans1' => $ia, 'ans1_val' => $ia, '-submit' => 1]);
        $this->check_current_mark(1);
        $this->check_prt_score('firsttree', null, null);
        $this->render();
        $this->assert_content_with_maths_contains('\[ 5',
            $this->currentoutput);
        $expected = 'Seed: 1; ans1: 5 [score]; firsttree: # =  | firsttree-bail';
        $this->check_response_summary($expected);

        // New answer which is wrong to check score and penalty.
        $ia = '2';
        $this->process_submission(['ans1' => $ia, 'ans1_val' => $ia, '-submit' => 1]);
        $this->check_current_mark(1);
        $this->check_prt_score('firsttree', 0, 0.25);
        $this->render();
        $this->assert_content_with_maths_contains('\[ 2',
            $this->currentoutput);
        $expected = 'Seed: 1; ans1: 2 [score]; firsttree: # = 0 | firsttree-0-0';
        $this->check_response_summary($expected);
    }

    public function test_input_validator_adaptblock(): void {

        $q = test_question_maker::make_question('stack', 'adaptblock');
        $this->start_attempt_at_question($q, 'adaptive', 1);

        // Most of this question is JS which needs the behat tests.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_prt_score('prt1', null, null);
        $this->render();
        $this->check_output_contains_text_input('ans1');
        $this->check_output_does_not_contain_input_validation();
        $this->check_output_does_not_contain_prt_feedback();
        $this->check_output_does_not_contain_stray_placeholders();
        $this->check_current_output(
            new question_pattern_expectation('/Shown until the adaptbutton has been clicked/'),
            new question_pattern_expectation('/Click me/'),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_does_not_contain_num_parts_correct(),
            $this->get_no_hint_visible_expectation()
            );
    }
}
