<?php
declare(strict_types=1);

namespace Shopware\Production\Command;

use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use Shopware\Production\Service\DatabaseService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DatabaseDumpCommand extends Command
{
    protected static $defaultName = 'database:dump';
    private DatabaseService $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        parent::__construct();
        $this->databaseService = $databaseService;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new ShopwareStyle($input, $output);

        $filename = $io->ask('Please enter a filename');

        $path = $this->databaseService->dump($filename);

        $io->writeln('Dump created at ' . $path);

        return self::SUCCESS;
    }
}
