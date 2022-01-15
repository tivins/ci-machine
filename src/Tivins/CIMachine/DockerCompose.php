<?php

namespace Tivins\CIMachine;

class DockerCompose
{
    public function __construct(private CIMachine $machine)
    {
    }

    public function __toString(): string
    {
        $data             = [];
        $data['version']  = '3.9';
        $data['networks'] = ['dev' => null];
        if ($this->machine->getDatabaseType()) {
            $data['services']['db'] = [
                'container_name' => $this->machine->uid . '_db',
                'image' => 'mysql:latest',
                'command' => '--default-authentication-plugin=mysql_native_password',
                'networks' => ['dev'],
                'environment' => [
                    'MYSQL_ROOT_PASSWORD=rootpass',
                    'MYSQL_DATABASE=database',
                    'MYSQL_USER=admin',
                    'MYSQL_PASSWORD=adminpass',
                ],
            ];
        }


        $data['services']['php'] = [
            'container_name' => $this->getPHPContainerName(),
            'networks' => ['dev'],
            'volumes' => [
                './volume:/box',
            ],
            'depends_on' => ['db'],
            'build' => [
                'context' => 'env/',
                'args' => [
                    // 'PHP' => '8.1'
                ],
            ],
            'image' => $this->getPHPImageName(),
            'environment' => [
                'DATABASE',
                'DB_USER',
                'DB_PASS'
            ]
        ];

        return yaml_emit($data);
    }

    public function getPHPContainerName(): string
    {
        return $this->machine->uid . '_php';
    }

    public function getPHPImageName(): string
    {
        return $this->machine->uid . '_php_img';
    }
}