<?php
declare(strict_types=1);

namespace Shopware\Production\Command;

use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use Shopware\Production\Service\DatabaseService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DatabaseImportCommand extends Command
{
    protected static $defaultName = 'database:import';
    private string $projectDir;
    private DatabaseService $databaseService;

    public function __construct(string $projectDir, DatabaseService $databaseService)
    {
        parent::__construct();
        $this->projectDir = $projectDir;
        $this->databaseService = $databaseService;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new ShopwareStyle($input, $output);
        $path = $this->projectDir . '/var/';

        $io->writeln('Copy your dump to ' . $path);
        $filename = $io->ask('Please enter the filename');

        try {
            $this->databaseService->import(file_get_contents($path . $filename));
        } catch (\Exception $e) {
            $io->error($e->getMessage());
        }
    }
}
