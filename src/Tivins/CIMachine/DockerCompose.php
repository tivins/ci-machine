<?php

namespace Tivins\CIMachine;

class DockerCompose
{
    public function __construct(private CIMachine $machine)
    {

    }

    public function __toString(): string
    {
        $data = [];
        $data['networks'] = ['dev'=>[]];
        $data['version'] = '3.9';

        $data['services']['db'] = [
            'container_name' => 'ci_' . $this->machine->uid . '_db',
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

        $data['services']['php'] = [
            'container_name' => 'ci_' . $this->machine->uid . '_php',
            'networks' => ['dev'],
            'volumes' => ['./tmp' => '/box___'],
            'depends_on' => ['db'],
            'build' => [
                'context' => 'dir___',
                'args' => [
                    // 'PHP' => '8.1'
                ],
            ],
        ];

        // $data['title'] = 'Title';
        // $data['jobs'][] = ['php'=>'8.1','mysql'=>'latest','php-ext'=>['pdo','json','yaml','intl']];


        return yaml_emit($data);
    }
}