#!/usr/bin/env php
<?php

/**
 * @file Dockerfile generator
 */

require __dir__ . '/../vendor/autoload.php';

use Tivins\CIMachine\DockerFile;
use Tivins\CIMachine\PHPExtension;

$dockerFile = new DockerFile();
$dockerFile->setPHPVersion('8.1')
->addPHPExtension(PHPExtension::XDEBUG)
->addPHPExtension(PHPExtension::IMAGICK)
->addPHPExtension(PHPExtension::INTL)
->addPHPExtension(PHPExtension::ZIP)
->addPHPExtension(PHPExtension::JSON);
echo $dockerFile;