<?php

namespace App\Tests\Functional;

use App\Dto\TickerDto;
use App\Service\RatesParserInterface;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Throwable;

class RatesParserTest extends KernelTestCase
{
    private RatesParserInterface $parser;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $container = static::getContainer();
        $this->parser = $container->get(RatesParserInterface::class);
    }

    public function testTickerDto(): void
    {
        $code = 'USD';
        $dirtyValue = '$100 , 0001';
        $pureValue = 100.0001;
        $nominal = 1;

        $ticker = TickerDto::create($code, $dirtyValue, $nominal);
        self::assertEquals($code, $ticker->getCharCode());
        self::assertEquals($pureValue, $ticker->getValue());
        self::assertEquals($nominal, $ticker->getNominal());
    }

    /**
     * @depends testTickerDto
     */
    public function testCbrParserException(): void
    {
        $this->expectException(Throwable::class);
        $this->parser->getNext();
    }

    /**
     * @depends testCbrParserException
     */
    public function testCbrParseTicker(): void
    {
        $ticker1 = $this->parser->withDate(new DateTimeImmutable())->getNext();
        self::assertNotNull($ticker1);

        $ticker2 = $this->parser->getNext();
        self::assertNotNull($ticker2);

        self::assertNotEquals($ticker1, $ticker2);
    }
}
