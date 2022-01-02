<?php

use Tivins\CIMachine\CITerminal;
use Tivins\Core\Tpl;

require 'vendor/autoload.php';

$term = new CITerminal();
$tpl  = Tpl::fromFile('readme-tpl.md')
    ->setVar('cimUsage', $term->usage())
    ->setVar('date', date('l jS F Y'))
    ;
file_put_contents('README.md', $tpl);