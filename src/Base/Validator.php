<?php
namespace Spider\Base;
use Spider\Exceptions\ValidatorException;

/**
 * General, small object and value validator
 * @package Spider\Base
 */
class Validator extends Object
{
    /** @var array Any validation errors */
    protected $errors = [];

    /** @var array Any validation rules (callables) */
    protected $rules = [];

    /**
     * Run the set rules on any input
     *
     * By default, will return true for passed or throw an exception on failure. You may
     * switch $silent to TRUE to return an array of errors on failure instead.
     *
     * @param mixed $input The input to be tested. Any kind of input.
     * @param bool|false $silent Fail silently or throw exceptions
     * @return array|bool True for pass, array of errors on failure
     * @throws ValidatorException if silent is set to FALSE and validation fails
     * @throws \Exception if validator encounters an error
     */
    public function validate($input, $silent = false)
    {
        $this->validateRules($input);

        if (!empty($this->errors)) { // We failed the validation
            if ($silent) {
                return $this->errors;
            }

            throw new ValidatorException($this->errors);
        }

        // We passed the validation
        return true;
    }

    /**
     * Adds a rule to validator
     * @param callable $rule A callable that receives an $input and returns true for pass or an array of errors for failure
     * @throws \Exception if rule is invalid
     */
    public function addRule(callable $rule)
    {
        $this->checkRule($rule);
        $this->rules[] = $rule;
    }

    /**
     * Add an array of rules
     * @param array $rules
     */
    public function addRules(array $rules)
    {
        foreach ($rules as $rule) {
            $this->addRule($rule);
        }
    }

    /**
     * Make sure a rule is callable
     * @param $rule
     * @throws \Exception
     */
    protected function checkRule($rule)
    {
        /* ToDo: Can easily add support for Respect/Validator and Symfony/Validator rules */
        if (!is_callable($rule) ) {
            throw new \Exception("Any rules added to Spider\\Base\\Validator must be callable or implement an approved interface");
        }
    }

    /**
     * Add validation failure errors to the current stack
     * @param $errors
     */
    public function addErrors($errors)
    {
        $this->errors = array_merge($this->errors, $errors);
    }

    /**
     * Return all the validation errors
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Return the current rules
     * @return array
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * Validate each rule in turn
     * @param $input
     * @throws \Exception
     */
    protected function validateRules($input)
    {
        foreach ($this->rules as $rule) {
            $result = $rule($input);

            if (is_array($result)) {
                $this->addErrors($result);

            } elseif ($result !== true) {
                throw new \Exception("Validator rules must return `true` or an array of error messages");
            }
        }
    }
}
