<?php

use Tivins\CIMachine\CICli;
use Tivins\Core\Tpl;

require 'vendor/autoload.php';

$ciCLI = new CICli();
$tpl = Tpl::fromFile('readme-tpl.md')->setVar('cimUsage', $ciCLI->usage());
file_put_contents('README.md', $tpl);