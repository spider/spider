<?php
namespace Spider\Commands\Languages\OrientSQL;

/**
 * Class SqlBatch
 * @package Spider\Commands\Languages\OrientSQL
 */
class SqlBatch
{
    /** @var array Batch variables */
    protected $transactionVariables;

    /** @var  string Working batch script */
    protected $script;

    /**
     * Returns current script
     * @return string
     */
    public function getScript()
    {
        return $this->script;
    }

    /**
     * Writes the beginning of a batch script
     */
    public function begin()
    {
        $this->script = "begin\n";
    }

    /**
     * Writes the end of a batch script with return variables
     */
    public function end()
    {
        $this->script .= "commit retry 100\n";
        $this->script .= "return " . $this->getVariables();
    }

    /**
     * Add a new statement/operation to the batch
     * @param $statement
     */
    public function addStatement($statement)
    {
        $this->script .= 'LET ' . $this->incrementVariables() . ' = ' . $statement . "\n";
    }

    /**
     * Increment transaction variables
     * @return string
     */
    protected function incrementVariables()
    {
        $newIndex = count($this->transactionVariables) + 1;
        $this->transactionVariables[] = "t" . (string)$newIndex;
        return 't' . (string)$newIndex;
    }

    /**
     * Get the transaction variables for the RETURN array
     * @return string
     */
    protected function getVariables()
    {
        $this->transactionVariables = array_map(function ($value) {
            return '$' . $value;
        }, $this->transactionVariables);

        $variables = implode(",", $this->transactionVariables);

        /* @todo Clearer and more programmatic way to decide what to return */
        if (count($this->transactionVariables) > 1) {
            return '[' . $variables . ']';
        }

        return $variables;
    }
}
