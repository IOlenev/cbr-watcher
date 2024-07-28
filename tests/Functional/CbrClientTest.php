<?php

namespace App\tests\Functional;

use App\Service\CbrClient;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CbrClientTest extends KernelTestCase
{
    private CbrClient $cbrClient;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $container = static::getContainer();
        $info = socket_addrinfo_lookup('cbrw_redis');
//        $redis = new \Redis();
//        $redis->connect('172.17.0.1', 6400);

        $this->cbrClient = $container->get(CbrClient::class);
    }

    public function testCbrClient(): void
    {
        $xml = $this->cbrClient->getRates(new DateTimeImmutable());
        self::assertNotEmpty($xml);
    }
}