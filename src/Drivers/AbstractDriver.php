<?php
namespace Spider\Drivers;

use Spider\Base\Collection;

abstract class AbstractDriver extends Collection implements DriverInterface
{
    public $inTransaction = FALSE;

    public function __destruct()
    {
        //rollback changes
        if($this->inTransaction)
        {
            $this->StopTransaction(FALSE);
        }
        //close driver
        $this->close();
    }

}
