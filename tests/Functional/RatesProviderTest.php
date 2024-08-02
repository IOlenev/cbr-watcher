<?php

namespace App\tests\Functional;

use App\Service\RatesProviderInterface;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RatesProviderTest extends KernelTestCase
{
    private RatesProviderInterface $provider;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $container = static::getContainer();
        $this->provider = $container->get(RatesProviderInterface::class);
    }

    public function testCbrProvider(): void
    {
        $xml = $this->provider->getRates(new DateTimeImmutable());
        self::assertNotEmpty($xml);
    }
}
