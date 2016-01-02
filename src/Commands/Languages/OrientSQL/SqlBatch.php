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

    /** @var int variable index */
    protected $variableIndex = 0;

    /** @var array Map of Statement Types to prefix */
    protected $prefixMap = [
        self::CREATE_STATEMENT => 'c',
        self::SELECT_STATEMENT => 's',
        self::DELETE_STATEMENT => 'd',
        self::UPDATE_STATEMENT => 'u',
        self::TRANSACTION_STATEMENT => 't',
        self::UNKNOWN_STATEMENT => 'u'
    ];

    /* Constants */
    const SELECT_STATEMENT = 100;
    const UPDATE_STATEMENT = 200;
    const CREATE_STATEMENT = 300;
    const DELETE_STATEMENT = 400;
    const TRANSACTION_STATEMENT = 500;
    const UNKNOWN_STATEMENT = 600;

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
     * @param string $type of statement (c,r,u,d)
     */
    public function addStatement($statement, $type)
    {
        $this->script .= 'LET ' . $this->incrementVariables($type) . ' = ' . $statement . "\n";
    }

    /**
     * Add a new statement/operation to the batch
     * @param array $statements
     * @param string $type of statement (c,r,u,d)
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
        $this->variableIndex++;
        return $this->transactionVariables[] = $this->prefixMap[$type] . (string)$this->variableIndex;
    }

    /**
     * Get the transaction variables for the RETURN array
     * @return string
     */
    protected function getVariables()
    {
        $allVariables = array_map(function ($value) {
            return '$' . $value;
        }, $this->transactionVariables);

        $variables = implode(",", $allVariables);

        /* @todo Clearer and more programmatic way to decide what to return */
        if (count($allVariables) > 1) {
            return '[' . $variables . ']';
        }

        return $variables;
    }
}
