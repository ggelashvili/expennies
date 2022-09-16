<?php

declare(strict_types = 1);

namespace Tests\Unit;

use App\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /** @test */
    public function it_is_able_to_get_nested_settings(): void
    {
        $config = [
            'doctrine' => [
                'connection' => [
                    'user' => 'root'
                ]
            ]
        ];

        $config = new Config($config);

        $this->assertEquals('root', $config->get('doctrine.connection.user'));
        $this->assertEquals(['user' => 'root'], $config->get('doctrine.connection'));
    }

    /** @test */
    public function it_gets_the_default_value_when_setting_is_not_found(): void
    {
        $config = [
            'doctrine' => [
                'connection' => [
                    'user' => 'root'
                ]
            ]
        ];

        $config = new Config($config);

        $this->assertEquals('pdo_mysql', $config->get('doctrine.connection.driver', 'pdo_mysql'));
        $this->assertEquals('bar', $config->get('foo', 'bar'));
        $this->assertEquals('baz', $config->get('foo.bar', 'baz'));
    }

    /** @test */
    public function it_returns_null_by_default_when_setting_is_not_found(): void
    {
        $config = [
            'doctrine' => [
                'connection' => [
                    'user' => 'root'
                ]
            ]
        ];

        $config = new Config($config);

        $this->assertNull($config->get('doctrine.connection.driver'));
        $this->assertNull($config->get('foo.bar'));
    }
}
