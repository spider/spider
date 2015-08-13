<?php
namespace Spider\Test\Stubs;

use Spider\Commands\CommandInterface;
use Spider\Drivers\AbstractDriver;
use Spider\Drivers\DriverInterface;
use Spider\Base\Collection;
use Spider\Drivers\Response;
use Spider\Exceptions\FormattingException;
use Spider\Exceptions\InvalidCommandException;
use Spider\Exceptions\NotSupportedException;


/**
 * This driver stub should pretend to receive a certan format of DB response and allow to format these.
 * DB responses will be in the following formats.
 * Set:
 * [
 *    ["id"=> 34, "label"=>"user", "properties"=>["key"=>value"]],
 *    ["id"=> 34, "label"=>"user", "properties"=>["key"=>value"]]
 * ]
 *
 * Path:
 * [
 *    [
 *      ["id"=> 34, "label"=>"user", "properties"=>["key"=>value"]],
 *      ["id"=> 34, "label"=>"user", "properties"=>["key"=>value"]]
 *    ],
 *    [
 *      ["id"=> 34, "label"=>"user", "properties"=>["key"=>value"]],
 *      ["id"=> 34, "label"=>"user", "properties"=>["key"=>value"]]
 *    ]
 * ]
 *
 * Scalar:
 * int|string
 */
class DriverStub extends AbstractDriver implements DriverInterface
{
    /**
     * @var string some unique identifier in the event of wanting to test multiple drivers
     */
    public $identifier = "one";

    public function open()
    {
        // Nothing
        return $this;
    }

    /**
     * Close the database connection
     * @return $this
     */
    public function close()
    {
        // Nothing
    }

    /**
     * Executes a Query or read command
     *
     * This is the R in CRUD
     *
     * @param CommandInterface $query
     * @return array|Record|Graph
     */
    public function executeReadCommand(CommandInterface $query)
    {
        return new Response(['_raw' => '', '_driver' => $this]);
    }

    /**
     * Executes a write command
     *
     * These are the "CUD" in CRUD
     *
     * @param CommandInterface $command
     * @return Graph|Record|array|mixed mixed values for some write commands
     */
    public function executeWriteCommand(CommandInterface $command)
    {
        $this->executeReadCommand($command);
    }

    /**
     * Executes a read command without waiting for a response
     *
     * @param CommandInterface $query
     * @return $this
     */
    public function runReadCommand(CommandInterface $query)
    {
        // Nothing
    }

    /**
     * Executes a write command without waiting for a response
     *
     * @param CommandInterface $command
     * @return $this
     */
    public function runWriteCommand(CommandInterface $command)
    {
        // Nothing
    }
        /**
     * Opens a transaction
     *
     * @return bool
     */
    public function startTransaction()
    {
        // Nothing
    }

    /**
     * Closes a transaction
     *
     * @param bool $commit whether this is a commit (TRUE) or a rollback (FALSE)
     *
     * @return bool
     */
    public function stopTransaction($commit = TRUE)
    {
        // Nothing
    }

    /**
     * Format a raw response to a set of collections
     * This is for cases where a set of Vertices or Edges is expected in the response
     *
     * @param mixed $response the raw DB response
     *
     * @return Response Spider consistent response
     */
    public function formatAsSet($response)
    {
        if (!empty($response) && $this->responseFormat($response) !== self::FORMAT_SET) {
            throw new FormattingException(
                "The response from the database was incorrectly formatted for this operation"
            );
        }
        if(empty($response))
        {
            return $response;
        }

        if(count($response) == 1)
        {
            return $this->mapToCollection($response[0]);
        }

        //several items
        $result = [];
        foreach($response as $row)
        {
            $result[] = $this->mapToCollection($row);
        }

        return $result;
    }

    /**
     * Format a raw response to a tree of collections
     * This is for cases where a set of Vertices or Edges is expected in tree format from the response
     *
     * @param mixed $response the raw DB response
     *
     * @return Response Spider consistent response
     */
    public function formatAsTree($response)
    {
        throw new NotSupportedException(__FUNCTION__ . "is not currently supported for the Gremlin Driver");
    }

    /**
     * Format a raw response to a path of collections
     * This is for cases where a set of Vertices or Edges is expected in path format from the response
     *
     * @param mixed $response the raw DB response
     *
     * @return Response Spider consistent response
     */
    public function formatAsPath($response)
    {
        if (!empty($response) && $this->responseFormat($response) !== self::FORMAT_PATH) {
            throw new FormattingException(
                "The response from the database was incorrectly formatted for this operation"
            );
        }

        if(empty($response))
        {
            return $response;
        }

        $result = [];
        foreach($response as $path)
        {
            $resultPath = [];
            foreach($path as $row)
            {
                $resultPath[] = $this->mapToCollection($row);
            }
            $result[] = $resultPath;
        }

        return $result;
    }

    /**
     * Format a raw response to a scalar
     * This is for cases where a scalar result is expected
     *
     * @param mixed $response the raw DB response
     *
     * @return Response Spider consistent response
     */
    public function formatAsScalar($response)
    {
        if (!empty($response) && $this->responseFormat($response) !== self::FORMAT_SCALAR) {
            throw new FormattingException(
                "The response from the database was incorrectly formatted for this operation"
            );
        }

        return $response;
    }

    /**
     * Returns a valid and preferred language processor
     * @return mixed
     */
    public function makeProcessor()
    {
        // TODO: Implement makeProcessor() method.
    }

    /**
     * Checks a response's format whenever possible
     *
     * @param mixed $response the response we want to get the format for
     *
     * @return int the format (FORMAT_X const) for the response
     */
    protected function responseFormat($response)
    {
        if (!is_array($response)) {
            return self::FORMAT_SCALAR;
        }

        if (isset($response[0]['id'])) {
            return self::FORMAT_SET;
        }

        if (isset($response[0]) && !isset($response[0]['id'])) {
            return self::FORMAT_PATH;
        }
        //@todo support tree.

        return self::FORMAT_CUSTOM;
    }

    protected function mapToCollection($row)
    {
        $collection = new Collection();

        if(isset($row['id']))
        {
            // We're in an Element scenario
            $collection->add($row['properties']);
            $collection->add([
                'id' => $row['id'],
                'meta.id' => $row['id'],
                'label' => $row['label'],
                'meta.label' => $row['label']
            ]);
            $collection->protect('meta');
            $collection->protect('id');
            $collection->protect('label');
        }
        else
        {
            //custom scenarios:
            $collection->add($row);
        }

        return $collection;
    }
}
