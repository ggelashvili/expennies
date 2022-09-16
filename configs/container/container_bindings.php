<?php

declare(strict_types = 1);

use App\Config;
use App\Enum\AppEnvironment;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Slim\Views\Twig;
use Twig\Extra\Intl\IntlExtension;

use function DI\create;

return [
    Config::class        => create(Config::class)->constructor(require CONFIG_PATH . '/app.php'),
    EntityManager::class => fn(Config $config) => EntityManager::create(
        $config->get('doctrine.connection'),
        ORMSetup::createAttributeMetadataConfiguration(
            $config->get('doctrine.entity_dir'),
            $config->get('doctrine.dev_mode')
        )
    ),
    Twig::class          => function (Config $config) {
        $twig = Twig::create(VIEW_PATH, [
            'cache'       => STORAGE_PATH . '/cache/templates',
            'auto_reload' => AppEnvironment::isDevelopment($config->get('app_environment')),
        ]);

        $twig->addExtension(new IntlExtension());

        return $twig;
    },
];
