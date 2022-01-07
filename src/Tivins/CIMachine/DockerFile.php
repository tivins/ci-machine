<?php

namespace Tivins\CIMachine;

enum PHPModules: string
{
}

class DockerFile
{
    private string $phpVersion        = 'latest';
    private array  $phpModulesEnabled = [];
    private bool   $databaseEnabled   = false;
    private string $databaseServer    = 'mysql';
    private string $databaseVersion   = 'latest';

    private array $ext = [
        'peclInstall' => [],
        'extConfig' => [],
        'extEnable' => [],
        'extInstall' => [],
        'libs' => [],
    ];

    public function __construct()
    {
    }

    public function setPHPVersion(string $version): void
    {
        $this->phpVersion = $version;
    }

    public function addPHPExtension(string $name): bool
    {
        switch ($name) {
            case 'xdebug':
                $this->ext['peclInstall'][] = 'xdebug';
                $this->ext['extEnable'][]   = 'xdebug';
                break;
            case 'imagick':
                $this->ext['libs'][]        = 'libmagickwand-dev';
                $this->ext['peclInstall'][] = 'imagick';
                $this->ext['extEnable'][]   = 'imagick';
                break;
            case 'mbstring':
                $this->ext['extInstall'][] = 'mbstring';
                break;
            case 'zip':
                $this->ext['extInstall'][] = 'zip';
                break;
            case 'gd':
                $this->ext['extInstall'][] = 'gd';
                break;
            case 'intl':
                $this->ext['extConfig'][]  = 'intl';
                $this->ext['extInstall'][] = 'intl';
                break;
            default:
                return false;
        }
        return true;
    }

    function getFingerPrint(): string
    {
        return sha1(json_encode(get_object_vars($this)));
    }

    public function __toString(): string
    {
        $body = "FROM: FROM php:{$this->phpVersion}-fpm\n";
        $body .= "RUN apt-get update && apt install -y --no-install-recommends \\";
        $body .= implode(' ', $this->ext['libs']) . "\n";
        $body .= "RUN pecl install {$this->ext['peclInstall']} \\\n"
            . "    && docker-php-ext-configure {$this->ext['extConfig']} \\\n"
            . "    && docker-php-ext-enable {$this->ext['extEnable']} \\\n"
            . "    && docker-php-ext-install {$this->ext['extInstall']} \\\n"
            . "\n\n";

        $body .= "# Setup xdebug for code coverage\n";
        $body .= "RUN echo 'xdebug.mode=coverage' > /usr/local/etc/php/conf.d/xdebug.ini\n\n";

        $body .= $this->addComposer();

        $body .= "RUN useradd -ms /bin/bash admin && echo \"admin:admin\" | chpasswd" . "\n";
        $body .= "USER admin" . "\n\n";

        $body .= "WORKDIR /box" . "\n\n";
        return $body;
    }

    public function addComposer(): string
    {
        return "# Composer\n"
            . 'RUN curl -sS https://getcomposer.org/installer | '
            . 'php -- --install-dir=/usr/local/bin --filename=composer' . "\n"
            . 'RUN composer --version' . "\n\n";
    }
}