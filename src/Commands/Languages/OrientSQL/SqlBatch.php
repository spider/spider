<?php
namespace Spider\Commands\Languages\OrientSQL;

/**
 * Class SqlBatch
 * @package Spider\Commands\Languages\OrientSQL
 */
class SqlBatch
{
    /** @var array Batch variables */
    public $transactionVariables;

    /** @var  string Working batch script */
    protected $script;

    const SELECT_STATEMENT = 100;
    const UPDATE_STATEMENT = 200;
    const CREATE_STATEMENT = 300;
    const DELETE_STATEMENT = 400;
    protected $variableIndex = 0;

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
    public function addStatement($statement, $type)
    {
        $this->script .= 'LET ' . $this->incrementVariables($type) . ' = ' . $statement . "\n";
    }

    /**
     * Add a new statement/operation to the batch
     * @param array $statements
     */
    public function addStatements(array $statements, $type)
    {
        foreach ($statements as $statement) {
            $this->addStatement($statement, $type);
        }
    }

    /**
     * Increment transaction variables
     * @param $type
     * @return string
     */
    protected function incrementVariables($type)
    {
        $typePrefixes = [
            static::SELECT_STATEMENT => 's',
            static::CREATE_STATEMENT => 'c',
            static::DELETE_STATEMENT => 'd',
            static::UPDATE_STATEMENT => 'u',
        ];

        $this->variableIndex++;
        $this->transactionVariables[$type][] = $typePrefixes[$type] . (string)$this->variableIndex;
        return $typePrefixes[$type] . (string)$this->variableIndex;
    }

    /**
     * Get the transaction variables for the RETURN array
     * @return string
     */
    protected function getVariables()
    {
        $allVariables = [];
        foreach ($this->transactionVariables as $type) {
            $allVariables = $allVariables + $type;
        }

        $allVariables = array_map(function ($value) {
            return '$' . $value;
        }, $allVariables);

        $variables = implode(",", $allVariables);

        /* @todo Clearer and more programmatic way to decide what to return */
        if (count($allVariables) > 1) {
            return '[' . $variables . ']';
        }

        return $variables;
    }
}
