<?php
namespace App\Command;

use App\Domain\Rates\Dto\RatesDto;
use App\Domain\Rates\Feature\RatesPreloadFeature;
use App\Domain\Rates\Message\RatesPreloadMessage;
use App\Domain\Rates\Message\WarmupDateMessage;
use App\Domain\Rates\Service\RatesParserInterface;
use App\Domain\Rates\Service\RatesProviderInterface;
use App\Domain\Ticker\Dto\TickerDto;
use App\Domain\Ticker\Dto\TickerPayloadDto;
use App\Dto\DateDto;
use DateTime;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:warmup',
    description: 'Warmup currency rates. Usage: php bin/console app:warmup',
    hidden: false
)]
final class WarmupCommand extends Command
{
    public function __construct(
        private readonly ParameterBagInterface $params,
        private readonly MessageBusInterface $bus,
        private readonly RatesParserInterface $parser,
        private readonly RatesProviderInterface $provider,
        private readonly RatesPreloadFeature $preloadFeature
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $date = new DateTime();
        $days = (int)$this->params->get('days_date_range');
        $borderDate = new DateTime(sprintf('-%d day', $days));

        $payload = TickerPayloadDto::create(TickerDto::DEFAULT_CURRENCY, DateDto::create($date));
        ($this->preloadFeature)(new RatesPreloadMessage($payload));
        $this->parser->withRates(RatesDto::create(
            $this->provider->getRates($payload->getBaseDate())
        ));
        $tickers = [];
        while ($ticker = $this->parser->getNext()) {
            $tickers[] = $ticker->getCharCode();
        }

        $section = $output->section();
        $section->setMaxHeight(1);
        $total = $days;
        $i = 1;
        while ($date > $borderDate) {
            $this->bus->dispatch(new WarmupDateMessage(DateDto::create($date), $tickers));
// works in right way inside container only
//            $section->overwrite(
//                sprintf('Processed %d of %d (%d %%)', $i, $total, $i++ / $total * 100)
//            );
            $date->modify('-1 day');
        }
        $output->writeln('Done. Warmed up to ' . $borderDate->modify('1 day')->format('Y-m-d'));
        $output->writeln('Please wait for queue jobs complete');
        return Command::SUCCESS;
    }
}
