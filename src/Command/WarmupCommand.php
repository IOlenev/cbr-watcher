<?php
namespace App\Command;

use App\Domain\Storage\Service\TickerServiceInterface;
use App\Domain\Ticker\Dto\TickerDto;
use App\Dto\DateDto;
use App\Dto\InputDto;
use DateTime;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;

#[AsCommand(
    name: 'app:warmup',
    description: 'Warmup currency rates. Usage: php bin/console app:warmup <ticker> <baseCurrency> (optional, default: RUR)',
    hidden: false
)]
final class WarmupCommand extends Command
{
    public function __construct(
        private readonly TickerServiceInterface $tickerService,
        private readonly ValidatorInterface $validator,
        private ParameterBagInterface $params
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'ticker',
                InputArgument::REQUIRED,
                'The currency ticker'
            )
            ->addArgument(
                'baseCurrency',
                InputArgument::OPTIONAL,
                'The rate base currency ticker',
                TickerDto::DEFAULT_CURRENCY
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputParams = InputDto::create(
            $input->getArgument('ticker'),
            DateDto::create()->format('Ymd'),
            $input->getArgument('baseCurrency')
        );

        $errors = $this->validator->validate($inputParams);
        if (count($errors) > 0) {
            $output->writeln(
                sprintf('%s - %s', $errors->get(0)->getPropertyPath(), $errors->get(0)->getMessage())
            );
            return Command::INVALID;
        }

        $date = new DateTime();
        $borderDate = new DateTime(sprintf('-%d day', (int)$this->params->get('days_date_range')));

        while ($date > $borderDate) {
            $this->tickerService->withDate(DateDto::create($date));
            try {
                $this->tickerService->getTicker($inputParams->getTicker(), $inputParams->getBaseCurrency());
            } catch (Throwable $exception) {
                $output->writeln('Error: ' . $exception->getMessage());
                return Command::FAILURE;
            }
            $date->modify('-1 day');
        }
        $output->writeln('Done. Warmed up to ' . $borderDate->modify('1 day')->format('Y-m-d'));
        return Command::SUCCESS;
    }
}
