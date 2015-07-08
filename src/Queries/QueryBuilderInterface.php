<?php
namespace Michaels\Spider\Queries;

interface QueryBuilderInterface
{
    /* Operations */
    public function select($properties = []);
//    public function update();
//    public function delete();
//    public function insert();

    /* Constraints */
    public function from();

    public function where();

    public function andWhere();

    public function notWhere();

    public function limit();

    public function notIn();

    /* Traversals, sub queries */
    public function also();

    public function store();

    public function out();

    public function in();

    public function edge(); // in or out

    /* fetching */
    public function tree($toLevel);

    public function path();

    public function all();

    public function first();

    public function get(); // fires query
}
