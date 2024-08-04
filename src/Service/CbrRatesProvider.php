<?php

namespace App\Service;

use App\Dto\DateDto;
use DateTimeImmutable;
use RuntimeException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

final class CbrRatesProvider implements RatesProviderInterface
{
    private const RATES = 'http://www.cbr.ru/scripts/XML_daily.asp';

    private DateDto $borderDate;

    public function __construct(
        readonly private HttpClientInterface $client,
        readonly private CacheInterface      $ratesCache,
        ParameterBagInterface       $params
    ) {
        $this->borderDate = DateDto::create(
            new DateTimeImmutable(sprintf('-%d day', (int)$params->get('days_date_range')))
        );
    }

    public function getRates(?DateDto $date = null): ?string
    {
        if (is_null($date)) {
            $date = DateDto::create();
        }

        if (strcmp($date, $this->borderDate) < 0) {
            return null;
        }

        try {
            $xml = $this->ratesCache->get(
                (string)$date,
                function (ItemInterface $item) use ($date) {
                    $response = $this->client->request(
                        'GET',
                        sprintf('%s?date_req=%s', self::RATES, $date->format('d/m/Y'))
                    );
                    return $response->getContent();
                }
            );
        } catch (Throwable $exception) {
            throw new RuntimeException(
                'Could not connect to cbr service',
                Response::HTTP_SERVICE_UNAVAILABLE,
                $exception
            );
        }

        return $xml;
    }
}
