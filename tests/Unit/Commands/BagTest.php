<?php
namespace Spider\Test\Unit\Commands;

use Codeception\Specify;
use Spider\Commands\Bag;
use Spider\Exceptions\ValidatorException;

class BagTest extends \PHPUnit_Framework_TestCase
{
    use Specify;

    public function testValidateBagPerformsOperations()
    {
        $this->specify("The bag has at least one action: CREATE", function () {
            $bag = new Bag();
            $bag->create = [[Bag::ELEMENT_TYPE => Bag::ELEMENT_VERTEX, Bag::ELEMENT_LABEL => 'person']];
            $actual = $bag->validate();

            $this->assertTrue($actual, 'failed to validate valid bag');
        });

        $this->specify("The bag has at least one action: RETRIEVE", function () {
            $bag = new Bag();
            $bag->retrieve = [];
            $actual = $bag->validate();

            $this->assertTrue($actual, 'failed to validate valid bag');
        });

        $this->specify("The bag has at least one action: UPDATE", function () {
            $bag = new Bag();
            $bag->update = [];
            $actual = $bag->validate();

            $this->assertTrue($actual, 'failed to validate valid bag');
        });

        $this->specify("The bag with multiple actions", function () {
            $bag = new Bag();
            $bag->retrieve = [];
            $bag->update = [];
            $actual = $bag->validate();

            $this->assertTrue($actual, 'failed to validate valid bag');
        });

        $this->specify("it throws exceptions for a bag with no actions", function () {

            $bag = new Bag();
            $bag->validate();
        }, ['throws' => [new ValidatorException(), "Validation failed: \nThe Command Bag must perform at least one operation - create, retrieve, or update"]]);
    }

    public function testValidateBagEdgeCreationHasInsAndOuts()
    {
        $this->specify("A valid edge creation passes", function () {
            $bag = new Bag();
            $bag->create = [
                Bag::ELEMENT_TYPE => Bag::ELEMENT_EDGE,
                Bag::EDGE_INV => 3,
                Bag::EDGE_OUTV => 2
            ];
            $actual = $bag->validate();

            $this->assertTrue($actual, 'failed to validate valid bag');
        });

        $this->specify("An edge creation without an IN fails", function () {
            $bag = new Bag();
            $bag->create = [[
                Bag::ELEMENT_TYPE => Bag::ELEMENT_EDGE,
                Bag::EDGE_OUTV => 2
            ]];
            $actual = $bag->validate();

            $this->assertTrue($actual, 'failed to validate valid bag');
        }, ['throws' => [new ValidatorException(), "Validation failed: \nAny edges created MUST include both an EDGE_INV and EDGE_OUTV"]]);

        $this->specify("An edge creation without an OUT fails", function () {
            $bag = new Bag();
            $bag->create = [[
                Bag::ELEMENT_TYPE => Bag::ELEMENT_EDGE,
                Bag::EDGE_INV => 2
            ]];
            $actual = $bag->validate();

            $this->assertTrue($actual, 'failed to validate valid bag');
        }, ['throws' => [new ValidatorException(), "Validation failed: \nAny edges created MUST include both an EDGE_INV and EDGE_OUTV"]]);
    }
}
