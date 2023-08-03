<?php

declare(strict_types = 1);

use App\Auth;
use App\Config;
use App\Contracts\AuthInterface;
use App\Contracts\EntityManagerServiceInterface;
use App\Contracts\RequestValidatorFactoryInterface;
use App\Contracts\SessionInterface;
use App\Contracts\UserProviderServiceInterface;
use App\Csrf;
use App\DataObjects\SessionConfig;
use App\Enum\AppEnvironment;
use App\Enum\SameSite;
use App\Enum\StorageDriver;
use App\Filters\UserFilter;
use App\RequestValidators\RequestValidatorFactory;
use App\RouteEntityBindingStrategy;
use App\Services\EntityManagerService;
use App\Services\UserProviderService;
use App\Session;
use Clockwork\DataSource\DoctrineDataSource;
use Clockwork\Storage\FileStorage;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use DoctrineExtensions\Query\Mysql\DateFormat;
use DoctrineExtensions\Query\Mysql\Month;
use DoctrineExtensions\Query\Mysql\Year;
use League\Flysystem\Filesystem;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\SimpleCache\CacheInterface;
use Slim\App;
use Slim\Csrf\Guard;
use Slim\Factory\AppFactory;
use Slim\Interfaces\RouteParserInterface;
use Slim\Views\Twig;
use Symfony\Bridge\Twig\Extension\AssetExtension;
use Symfony\Bridge\Twig\Mime\BodyRenderer;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\BodyRendererInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\CacheStorage;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;
use Symfony\WebpackEncoreBundle\Twig\EntryFilesTwigExtension;
use Twig\Extra\Intl\IntlExtension;
use Clockwork\Clockwork;

use function DI\create;

return [
    App::class                              => function (ContainerInterface $container) {
        AppFactory::setContainer($container);

        $addMiddlewares = require CONFIG_PATH . '/middleware.php';
        $router         = require CONFIG_PATH . '/routes/web.php';

        $app = AppFactory::create();

        $app->getRouteCollector()->setDefaultInvocationStrategy(
            new RouteEntityBindingStrategy(
                $container->get(EntityManagerServiceInterface::class),
                $app->getResponseFactory()
            )
        );

        $router($app);

        $addMiddlewares($app);

        return $app;
    },
    Config::class                           => create(Config::class)->constructor(
        require CONFIG_PATH . '/app.php'
    ),
    EntityManagerInterface::class           => function (Config $config) {
        $ormConfig = ORMSetup::createAttributeMetadataConfiguration(
            $config->get('doctrine.entity_dir'),
            $config->get('doctrine.dev_mode')
        );

        $ormConfig->addFilter('user', UserFilter::class);

        if (class_exists('DoctrineExtensions\Query\Mysql\Year')) {
            $ormConfig->addCustomDatetimeFunction('YEAR', Year::class);
        }

        if (class_exists('DoctrineExtensions\Query\Mysql\Month')) {
            $ormConfig->addCustomDatetimeFunction('MONTH', Month::class);
        }

        if (class_exists('DoctrineExtensions\Query\Mysql\DateFormat')) {
            $ormConfig->addCustomStringFunction('DATE_FORMAT', DateFormat::class);
        }

        return new EntityManager(
            DriverManager::getConnection($config->get('doctrine.connection'), $ormConfig),
            $ormConfig
        );
    },
    Twig::class                             => function (Config $config, ContainerInterface $container) {
        $twig = Twig::create(VIEW_PATH, [
            'cache'       => STORAGE_PATH . '/cache/templates',
            'auto_reload' => AppEnvironment::isDevelopment($config->get('app_environment')),
        ]);

        $twig->addExtension(new IntlExtension());
        $twig->addExtension(new EntryFilesTwigExtension($container));
        $twig->addExtension(new AssetExtension($container->get('webpack_encore.packages')));

        return $twig;
    },
    /**
     * The following two bindings are needed for EntryFilesTwigExtension & AssetExtension to work for Twig
     */
    'webpack_encore.packages'               => fn() => new Packages(
        new Package(new JsonManifestVersionStrategy(BUILD_PATH . '/manifest.json'))
    ),
    'webpack_encore.tag_renderer'           => fn(ContainerInterface $container) => new TagRenderer(
        new EntrypointLookup(BUILD_PATH . '/entrypoints.json'),
        $container->get('webpack_encore.packages')
    ),
    ResponseFactoryInterface::class         => fn(App $app) => $app->getResponseFactory(),
    AuthInterface::class                    => fn(ContainerInterface $container) => $container->get(
        Auth::class
    ),
    UserProviderServiceInterface::class     => fn(ContainerInterface $container) => $container->get(
        UserProviderService::class
    ),
    SessionInterface::class                 => fn(Config $config) => new Session(
        new SessionConfig(
            $config->get('session.name', ''),
            $config->get('session.flash_name', 'flash'),
            $config->get('session.secure', true),
            $config->get('session.httponly', true),
            SameSite::from($config->get('session.samesite', 'lax'))
        )
    ),
    RequestValidatorFactoryInterface::class => fn(ContainerInterface $container) => $container->get(
        RequestValidatorFactory::class
    ),
    'csrf'                                  => fn(ResponseFactoryInterface $responseFactory, Csrf $csrf) => new Guard(
        $responseFactory, failureHandler: $csrf->failureHandler(), persistentTokenMode: true
    ),
    Filesystem::class                       => function (Config $config) {
        $digitalOcean = function (array $options) {
            $client = new Aws\S3\S3Client(
                [
                    'credentials' => [
                        'key'    => $options['key'],
                        'secret' => $options['secret'],
                    ],
                    'region'      => $options['region'],
                    'version'     => $options['version'],
                    'endpoint'    => $options['endpoint'],
                ]
            );

            return new League\Flysystem\AwsS3V3\AwsS3V3Adapter(
                $client,
                $options['bucket']
            );
        };

        $adapter = match ($config->get('storage.driver')) {
            StorageDriver::Local => new League\Flysystem\Local\LocalFilesystemAdapter(STORAGE_PATH),
            StorageDriver::Remote_DO => $digitalOcean($config->get('storage.s3'))
        };

        return new League\Flysystem\Filesystem($adapter);
    },
    Clockwork::class                        => function (EntityManagerInterface $entityManager) {
        $clockwork = new Clockwork();

        $clockwork->storage(new FileStorage(STORAGE_PATH . '/clockwork'));
        $clockwork->addDataSource(new DoctrineDataSource($entityManager));

        return $clockwork;
    },
    EntityManagerServiceInterface::class    => fn(EntityManagerInterface $entityManager) => new EntityManagerService(
        $entityManager
    ),
    MailerInterface::class                  => function (Config $config) {
        if ($config->get('mailer.driver') === 'log') {
            return new \App\Mailer();
        }

        $transport = Transport::fromDsn($config->get('mailer.dsn'));

        return new Mailer($transport);
    },
    BodyRendererInterface::class            => fn(Twig $twig) => new BodyRenderer($twig->getEnvironment()),
    RouteParserInterface::class             => fn(App $app) => $app->getRouteCollector()->getRouteParser(),
    CacheInterface::class                   => fn(RedisAdapter $redisAdapter) => new Psr16Cache($redisAdapter),
    RedisAdapter::class                     => function (Config $config) {
        $redis  = new \Redis();
        $config = $config->get('redis');

        $redis->connect($config['host'], (int) $config['port']);

        if ($config['password']) {
            $redis->auth($config['password']);
        }

        return new RedisAdapter($redis);
    },
    RateLimiterFactory::class               => fn(RedisAdapter $redisAdapter, Config $config) => new RateLimiterFactory(
        $config->get('limiter'), new CacheStorage($redisAdapter)
    ),
];
