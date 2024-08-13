<?php

namespace App\Domain\Rates\Service;

use App\Domain\Rates\Dto\RatesDto;
use App\Domain\Ticker\Dto\TickerDto;
use LogicException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

final class CbrRatesParser implements RatesParserInterface
{
    /**
     * @var null|array<int, array{"Nominal": int, "Value": string, "VarChar": string}>
     */
    private ?array $dayRates = null;

    public function __construct()
    {
    }

    public function getNext(bool $reset = false): ?TickerDto
    {
        if (is_null($this->dayRates)) {
            throw new LogicException('Rates source not specified');
        }
        if ($reset) {
            reset($this->dayRates);
        }

        $result = current($this->dayRates);
        if (!is_array($result)) {
            return null;
        }
        next($this->dayRates);

        return TickerDto::create($result['CharCode'], $result['Value'], $result['Nominal']);
    }

    public function withRates(RatesDto $rates): RatesParserInterface
    {
        $raw = simplexml_load_string($rates);
        $raw = json_encode($raw, JSON_THROW_ON_ERROR);
        $this->dayRates = json_decode($raw, true);
        $this->dayRates = $this->dayRates['Valute'] ?? null;
        if (is_null($this->dayRates)) {
            throw new RuntimeException(
                'Parsing error. Check rates source format',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
        reset($this->dayRates);
        return $this;
    }
}
