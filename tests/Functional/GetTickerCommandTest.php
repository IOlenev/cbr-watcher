<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class GetTickerCommandTest extends KernelTestCase
{
    public function testExecute(): void
    {
        self::bootKernel();
        $app = new Application(self::$kernel);
        $command = $app->find('app:get-ticker');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['ticker' => 'U SD']);

        self::assertEquals(Command::INVALID, $commandTester->getStatusCode());
    }
}
