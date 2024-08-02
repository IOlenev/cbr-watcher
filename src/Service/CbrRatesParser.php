<?php

namespace App\Service;

use App\Dto\TickerDto;
use DateTimeInterface;
use LogicException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

final class CbrRatesParser implements RatesParserInterface
{
    /**
     * @var null|array<int, array{"Nominal": int, "Value": string, "VarChar": string}>
     */
    private ?array $dayRates = null;

    public function __construct(readonly private RatesProviderInterface $provider)
    {
    }

    public function withDate(DateTimeInterface $date): self
    {
        $raw = $this->provider->getRates($date);
        if (is_null($raw)) {
            throw new RuntimeException('Could not get day rates', Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $raw = simplexml_load_string($raw);
        $raw = json_encode($raw, JSON_THROW_ON_ERROR);
        $this->dayRates = json_decode($raw, true);
        $this->dayRates = $this->dayRates['Valute'] ?? null;
        if (is_null($this->dayRates)) {
            throw new LogicException('Parsing error. Check cbr data format', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        reset($this->dayRates);
        return $this;
    }

    public function getNext(bool $reset = false): ?TickerDto
    {
        if ($reset) {
            reset($this->dayRates);
        }
        $result = current($this->dayRates);
        next($this->dayRates);

        return TickerDto::create($result['CharCode'], $result['Value'], $result['Nominal']);
    }
}
