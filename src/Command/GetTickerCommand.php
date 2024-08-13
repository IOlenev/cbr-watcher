<?php
namespace App\Command;

use App\Domain\Storage\Service\TickerServiceInterface;
use App\Domain\Ticker\Dto\TickerDto;
use App\Dto\DateDto;
use App\Dto\InputDto;
use DateTime;
use DateTimeImmutable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:get-ticker',
    description: 'Get ticker rate. Usage: php bin/console app:get-ticker <ticker> <date>(optional, default: now) <baseCurrency> (optional, default: RUR)',
    hidden: false
)]
final class GetTickerCommand extends Command
{
    public function __construct(
        private readonly TickerServiceInterface $tickerService,
        private readonly ValidatorInterface $validator
    ) {
        parent::__construct();
    }
    protected function configure(): void
    {
        $this
            ->addArgument('ticker', InputArgument::REQUIRED, 'The currency ticker')
            ->addArgument('date', InputArgument::OPTIONAL, 'The date of the rate')
            ->addArgument('baseCurrency', InputArgument::OPTIONAL, 'The rate base currency ticker', TickerDto::BASE_CURRENCY)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputParams = InputDto::create(
            $input->getArgument('ticker'),
            $input->getArgument('date'),
            $input->getArgument('baseCurrency')
        );

        $errors = $this->validator->validate($inputParams);
        if (count($errors) > 0) {
            $output->writeln((string) $errors);
            return Command::INVALID;
        }

        $date = DateTimeImmutable::createFromMutable(new DateTime($inputParams->getDate()));
        $this->tickerService->withDate(DateDto::create($date));
        $ticker = $this->tickerService->getTicker($inputParams->getTicker(), $inputParams->getBaseCurrency());
        if (is_null($ticker)) {
            $output->writeln('Processing..');
            $output->writeln('Try again later');
            return Command::FAILURE;
        }

        $output->writeln((string) $ticker);
        return Command::SUCCESS;
    }
}
