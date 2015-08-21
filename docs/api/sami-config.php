<?php

use Sami\Sami;
use Symfony\Component\Finder\Finder;

$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->in('../../src');

return new Sami($iterator, [
    'build_dir' => 'html'
]);
