<?php

namespace Tivins\CIMachine;

enum PHPModules: string {

}

class DockerFile
{

    private string $phpVersion = 'latest';
    private array $phpModulesEnabled = [];
    private bool $databaseEnabled = false;
    private string $databaseServer = 'mysql';
    private string $databaseVersion = 'latest';

    public function __construct()
    {
    }

    public function __toString(): string
    {
        $body = "FROM: FROM php:{$this->phpVersion}-fpm\n";
        $body .= "RUN apt-get update && apt install -y --no-install-recommends \\";
        $body .= implode(' ', ['libzip-dev','zip','unzip','git','libmagickwand-dev','libonig-dev'])."\n";
        $body .= "RUN useradd -ms /bin/bash admin && echo \"admin:admin\" | chpasswd" . "\n";
        $body .= "USER admin" . "\n";
        $body .= "WORKDIR /box" . "\n";
        return $body;
    }
}