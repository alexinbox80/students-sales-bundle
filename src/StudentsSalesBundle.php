<?php

namespace alexinbox80\StudentsSalesBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class StudentsSalesBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml', 'yaml');
    }

    public function configure(DefinitionConfigurator $definition): void
    {

    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->prependExtensionConfig(
            'doctrine',
            [
                'orm' => [
                    'mappings' => [
                        'alexinbox80\\StudentsSalesBundle' => [
                            'type' => 'attribute',
                            'dir' => '%kernel.project_dir%/studentsSalesBundle/src/Domain/Model',
                            'prefix' => 'alexinbox80\StudentsSalesBundle\Domain\Model',
                            'alias' => 'alexinbox80\\StudentsSalesBundle'
                        ]
                    ]
                ]
            ]
        );
    }
}
