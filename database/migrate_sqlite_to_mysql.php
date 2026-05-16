<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

$sqlitePath = base_path('database/webpc.sqlite');
if (!is_file($sqlitePath)) {
    echo "SQLite database not found.\n";
    exit(0);
}

$source = new PDO('sqlite:' . $sqlitePath);
$source->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$source->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$target = db();
$tables = [
    'categories',
    'users',
    'products',
    'services',
    'payment_methods',
    'orders',
    'order_items',
    'service_requests',
    'payments',
    'newsletter_subscribers',
];

foreach ($tables as $table) {
    $exists = $source
        ->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name = " . $source->quote($table))
        ->fetchColumn();

    if (!$exists || !table_exists($table)) {
        continue;
    }

    $rows = $source->query('SELECT * FROM ' . $table)->fetchAll();
    foreach ($rows as $row) {
        $columns = array_keys($row);
        $columnSql = '`' . implode('`,`', $columns) . '`';
        $placeholders = ':' . implode(',:', $columns);
        $updates = implode(
            ', ',
            array_map(static fn (string $column): string => '`' . $column . '` = VALUES(`' . $column . '`)', $columns)
        );

        $stmt = $target->prepare(
            'INSERT INTO ' . $table . ' (' . $columnSql . ') VALUES (' . $placeholders . ')
             ON DUPLICATE KEY UPDATE ' . $updates
        );

        foreach ($row as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        $stmt->execute();
    }

    echo $table . ': ' . count($rows) . "\n";
}
