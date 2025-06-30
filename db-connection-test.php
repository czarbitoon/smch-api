<?php
// Simple script to test database connection

echo "Testing database connection...\n";

$host = getenv('DB_HOST') ?: 'interchange.proxy.rlwy.net';
$port = getenv('DB_PORT') ?: '51691';
$database = getenv('DB_DATABASE') ?: 'railway';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: 'SMCUxfcsmhpwhJFcarxCUQLeHsdGiVMH';

echo "Connection details:\n";
echo "- Host: $host\n";
echo "- Port: $port\n";
echo "- Database: $database\n";
echo "- Username: $username\n";
echo "- Password: " . (empty($password) ? '[NOT SET]' : '[SET]') . "\n";

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$database";
    echo "Attempting connection with DSN: $dsn\n";

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 5, // 5 second timeout
    ];

    $pdo = new PDO($dsn, $username, $password, $options);

    echo "Connection successful!\n";

    // Get version
    $stmt = $pdo->query('SELECT VERSION() as version');
    $version = $stmt->fetch();
    echo "MySQL Version: " . $version['version'] . "\n";

    // List tables
    $stmt = $pdo->query('SHOW TABLES');
    $tables = $stmt->fetchAll();

    echo "Tables:\n";
    if (count($tables) > 0) {
        foreach ($tables as $table) {
            $tableName = reset($table);
            echo "- $tableName\n";
        }
    } else {
        echo "No tables found in database.\n";
    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";

    if (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "\nThis looks like a credentials issue. Please check:\n";
        echo "1. That the username and password are correct\n";
        echo "2. That the user has access to the database\n";
        echo "3. That the user is allowed to connect from this host\n";
    }

    if (strpos($e->getMessage(), 'php_network_getaddresses') !== false) {
        echo "\nThis looks like a network or host resolution issue. Please check:\n";
        echo "1. That the host name is correct\n";
        echo "2. That your network allows connections to this host and port\n";
        echo "3. That the database server is running and accepting connections\n";
    }

    exit(1);
}
