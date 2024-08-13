<?php

namespace App\Tests\Functional;

use App\Dto\InputDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InputValidationTest extends KernelTestCase
{
    private const DATE = '20240802';
    private const CODE = 'BLA';
    private const BASE = 'ALB';

    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $container = static::getContainer();
        $this->validator = $container->get(ValidatorInterface::class);
    }

    public function testIsValid(): void
    {
        $errors = $this->validator->validate(
            InputDto::create(self::CODE, self::DATE, self::BASE)
        );
        self::assertCount(0, $errors);

        $errors = $this->validator->validate(
            InputDto::create(self::CODE, self::DATE)
        );
        self::assertCount(0, $errors);

        $errors = $this->validator->validate(
            InputDto::create(self::CODE)
        );
        self::assertCount(0, $errors);
    }

    public function testIsNotValid(): void
    {
        $errors = $this->validator->validate(
            InputDto::create('b l a')
        );
        self::assertGreaterThan(0, count($errors));

        $errors = $this->validator->validate(
            InputDto::create(self::CODE, '01.012')
        );
        self::assertGreaterThan(0, count($errors));
    }
}
