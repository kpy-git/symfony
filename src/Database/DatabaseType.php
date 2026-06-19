<?php

namespace App\Database;

enum DatabaseType: string
{
    case MySQL = 'MySQL';
    case PostgresSQL = 'PostgresSQL';
    case Oracle  = 'Oracle';
    case SQLite = 'SQLite';
    case SQLServer = 'SQLServer';
}
