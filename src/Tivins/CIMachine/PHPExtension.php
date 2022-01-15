<?php

namespace Tivins\CIMachine;

enum PHPExtension: string
{
    case XDEBUG = 'xdebug';
    case IMAGICK = 'imagick';
    case MBSTRING = 'mbstring';
    case ZIP = 'zip';
    case GD = 'gd';
    case INTL = 'intl';
    case JSON = 'json';
    case PDO = 'pdo';
}