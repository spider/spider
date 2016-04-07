<?php
namespace Spider\Test\Unit\Base;

use Codeception\Specify;
use Spider\Base\Validator;
use Spider\Exceptions\ValidatorException;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    use Specify;

    public function testAddRules()
    {
        $this->specify("adds a single rule", function() {
            $validator = new Validator();
            $validator->addRule(function ($validator) {
                return true;
            });

            $this->assertCount(1, $validator->getRules(), 'failed to add a single rule');
        });

        $this->specify("adds multiple rules one at a time", function() {
            $validator = new Validator();
            $validator->addRule(function ($validator) {
                return true;
            });
            $validator->addRule(function ($validator) {
                return true;
            });
            $validator->addRule(function ($validator) {
                return true;
            });

            $this->assertCount(3, $validator->getRules(), 'failed to add a single rule');
        });

        $this->specify("adds multiple rules as array", function() {
            $validator = new Validator();
            $validator->addRules([
                function ($validator) {
                    return true;
                },
                function ($validator) {
                    return true;
                },
                function ($validator) {
                    return true;
                },
            ]);


            $this->assertCount(3, $validator->getRules(), 'failed to add a single rule');
        });

        $this->specify("throws an exception for an invalid rule", function() {
            $validator = new Validator();
            $validator->addRule('not a valid rule');
        }, ['throws' => [new \Exception(), "Any rules added to Spider\\Base\\Validator must be callable or implement an approved interface"]]);
    }

    public function testValidation()
    {
        $this->specify("it passes a validation with a single rule", function() {
            $validator = new Validator();
            $validator->addRule(function ($input) {
                return true;
            });

            $actual = $validator->validate('doesnt matter');
            $this->assertTrue($actual, 'failed to validate to true');
        });

        $this->specify("it passes a validation with multiple rules", function() {
            $validator = new Validator();
            $validator->addRule(function ($input) {
                return true;
            });

            $validator->addRule(function ($input) {
                return true;
            });

            $actual = $validator->validate('doesnt matter');
            $this->assertTrue($actual, 'failed to validate to true');
        });

        $this->specify("it fails a validation with a single rule", function() {
            $validator = new Validator();
            $validator->addRule(function ($input) {
                return ['Rule did not pass'];
            });

            $validator->validate('doesnt matter');
        }, ['throws' => [new ValidatorException(), "Validation failed: \nRule did not pass"]]);

        $this->specify("it fails a validation with multiple rules", function() {
            $validator = new Validator();
            $validator->addRule(function ($input) {
                return ['Rule One did not pass'];
            });

            $validator->addRule(function ($input) {
                return ['Rule Two did not pass'];
            });

            $actual = $validator->validate('doesnt matter');
            $this->assertTrue($actual, 'failed to validate to true');
        }, ['throws' => [
            new ValidatorException(),
            "Validation failed: \nRule One did not pass\nRule Two did not pass"]
        ]);

        $this->specify("it fails some validations ", function() {
            $validator = new Validator();
            $validator->addRule(function ($input) {
                return ['Rule did not pass'];
            });

            $validator->addRule(function ($input) {
                return true;
            });

            $validator->validate('doesnt matter');
        }, ['throws' => [new ValidatorException(), "Validation failed: \nRule did not pass"]]);
    }

    public function testSilentValidation()
    {
        $this->specify("it fails a validation with a single rule silently", function() {
            $validator = new Validator();
            $validator->addRule(function ($input) {
                return ['Rule One did not pass'];
            });

            $validator->addRule(function ($input) {
                return ['Rule Two did not pass'];
            });

            $actual = $validator->validate('doesnt matter', true);
            $this->assertEquals(['Rule One did not pass', 'Rule Two did not pass'], $actual, 'failed to produce correct errors');
        });
    }

    public function testWorkingValidation()
    {
        $this->specify("it passes a scenario validation", function() {
            $validator = new Validator();
            $validator->addRule(function ($input) {
                if (!is_string($input)) {
                    return ['Input must be a string'];
                }

                return true;
            });

            $validator->addRule(function ($input) {
                if ($input !== 'testing') {
                    return ['Input must be `testing`'];
                }

                return true;
            });

            $actual = $validator->validate('testing', true);
            $this->assertTrue($actual, 'failed to produce pass validation');
        });

        $this->specify("it fails a scenario validation", function() {
            $validator = new Validator();
            $validator->addRule(function ($input) {
                if (!is_string($input)) {
                    return ['Input must be a string'];
                }

                return true;
            });

            $validator->addRule(function ($input) {
                if ($input !== 'testing') {
                    return ['Input must be `testing`'];
                }

                return true;
            });

            $actual = $validator->validate('testing will not pass', true);
            $this->assertEquals(['Input must be `testing`'], $actual, 'failed to produce correct errors');
        });

        $this->specify("it fails a scenario validation", function() {
            $validator = new Validator();
            $validator->addRule(function ($input) {
                if (!is_string($input)) {
                    return ['Input must be a string'];
                }

                return true;
            });

            $validator->addRule(function ($input) {
                if ($input !== 'testing') {
                    return ['Input must be `testing`'];
                }

                return true;
            });

            $validator->validate(3);
        }, ['throws' => [new ValidatorException(), "Validation failed: \nInput must be a string\nInput must be `testing`"]]);
    }
}
