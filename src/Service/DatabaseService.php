<?php
declare(strict_types=1);

namespace Shopware\Production\Service;

use Doctrine\DBAL\Connection;
use Ifsnop\Mysqldump\Mysqldump;

class DatabaseService
{
    private string $projectDir;
    private Connection $connection;

    public function __construct(string $projectDir, Connection $connection)
    {
        $this->projectDir = $projectDir;
        $this->connection = $connection;
    }

    public function dump(string $filename): string
    {
        $databaseParams = $this->connection->getParams();
        $dump = new Mysqldump(sprintf('mysql:host=%s;dbname=%s', $databaseParams['host'], $this->connection->getDatabase()), $databaseParams['user'], $databaseParams['password']);
        $path = $this->projectDir . '/var/' . $filename;
        $dump->start($path);

        return $path;
    }

    public function import(string $sql): void
    {
        $stmt = $this->connection->prepare($sql);
        $rs = $stmt->execute();

        $stmt->free();
    }
}
