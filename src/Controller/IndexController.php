<?php

namespace App\Controller;

use App\Domain\Storage\Service\TickerServiceInterface;
use App\Dto\DateDto;
use App\Dto\InputDto;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class IndexController extends AbstractController
{
    public function __construct(
        private readonly TickerServiceInterface $tickerService,
        private readonly ValidatorInterface $validator
    ) {
    }

    #[Route('/{ticker}/{date}/{baseCurrency}', name: 'app_index')]
    public function index(string $ticker, string $date = null, string $baseCurrency = null): JsonResponse
    {
        $input = InputDto::create($ticker, $date, $baseCurrency);
        $errors = $this->validator->validate($input);
        $attempts = count($errors) ? 0 : 3;

        while (--$attempts > 0) {
            $result = $this->tickerService
                ->withDate(DateDto::create(new DateTime($input->getDate())))
                ->getTicker(
                    $input->getTicker(),
                    $input->getBaseCurrency()
                );
            if (!is_null($result)) {
                break;
            }
            sleep(2);
        }

        return $this->json([
            'result' => (string) ($result ?? null),
            'errors' => (string) $errors,
        ]);
    }

    #[Route('/', name: 'app_welcome')]
    public function welcome(): Response
    {
        return $this->render('default/index.html.twig');
    }
}
