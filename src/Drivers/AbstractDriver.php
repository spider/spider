<?php
namespace Spider\Drivers;

use Spider\Base\Collection;

abstract class AbstractDriver extends Collection implements DriverInterface
{
    /**
     * set of possible formats for responses.
     */
    const FORMAT_SET = 10;
    const FORMAT_TREE = 20;
    const FORMAT_PATH = 30;
    const FORMAT_SCALAR = 40;
    const FORMAT_CUSTOM = 50;

    /**
     * @var bool whether or not the driver is currently handling an open transaction
     */
    public $inTransaction = false;

    public function __destruct()
    {
        //rollback changes
        if ($this->inTransaction) {
            $this->StopTransaction(false);
        }
        //close driver
        $this->close();
    }
}
