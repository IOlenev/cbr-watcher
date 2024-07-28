<?php

namespace App\Service;

use DateTimeImmutable;
use DateTimeInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class CbrClient
{
    private const RATES = 'http://www.cbr.ru/scripts/XML_daily.asp';

    private DateTimeImmutable $borderDate;

    public function __construct(
        private HttpClientInterface $client,
        private CacheInterface      $ratesCache,
        ParameterBagInterface       $params
    ) {
        $this->borderDate = new DateTimeImmutable(sprintf('-%d day', (int)$params->get('days_date_range')));
    }

    public function getRates(DateTimeInterface $date): ?string
    {
        if ($date < $this->borderDate) {
            return null;
        }

        $xml = $this->ratesCache->get(
            self::getKeyByDate($date),
            function (ItemInterface $item) use ($date) {
                $item->expiresAfter(3600);
                $response = $this->client->request(
                    'GET',
                    sprintf('%s?date_req=%s', self::RATES, $date->format('d/m/Y'))
                );
                return $response->getContent();
            }
        );

        return $xml;
    }

    private static function getKeyByDate(DateTimeInterface $date): string
    {
        return $date->format('Ymd');
    }
}
