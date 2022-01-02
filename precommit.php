<?php

use Tivins\CIMachine\CLI;
use Tivins\Core\Tpl;

require 'vendor/autoload.php';

$ciCLI = new CLI();
$tpl = Tpl::fromFile('readme-tpl.md')->setVar('cimUsage', $ciCLI->usage());
file_put_contents('README.md', $tpl);