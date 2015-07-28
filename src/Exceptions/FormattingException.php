<?php
namespace Spider\Exceptions;

class FormattingException extends \Exception
{
    protected $format;

    /**
     * Return format
     * @return mixed
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Set format
     * @param mixed $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }
}
