<?php
namespace Spider\Exceptions;

class ValidatorException extends \Exception
{
    /** @var array List of errors */
    protected $errors = [];

    /**
     * ValidatorException constructor.
     * @param array $errors
     */
    public function __construct(array $errors = [])
    {
        $this->setErrors($errors);
        $this->message = "Validation failed: \n" . implode("\n", $errors);
    }

    /**
     * Return the errors as an array
     * @return mixed
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Set errors
     * @param array $errors
     */
    public function setErrors(array $errors)
    {
        $this->errors = $errors;
    }
}
