<?php
declare(strict_types=1);

namespace PicturaeInstaller\App;

use PDO;
use Envms\FluentPDO\Query;

final class Database
{
    private string $tablePrefix;
    private string $host;
    private string $user;
    private string $password;
    private string $database;
    public string $error;
    public PDO $dbh;
    public Query $build;

    public function __construct()
    {
        $this->tablePrefix  = getenv('DB_PREFIX');
        $this->host         = getenv('DB_HOST');
        $this->user         = getenv('DB_USER');
        $this->password     = getenv('DB_PASS');
        $this->database     = getenv('DB_NAME');

        $connection = 'mysql:host=' . $this->host . ';dbname=' . $this->database;

        $options = [
            PDO::ATTR_PERSISTENT    => true,
            PDO::ATTR_ERRMODE       => PDO::ERRMODE_EXCEPTION
        ];

        try {
            $this->dbh = new PDO($connection, $this->user, $this->password, $options);
        } catch(\PDOException $exception) {
            $this->error = $exception->getMessage();
        }

        $this->build = new Query($this->dbh);
    }
}
