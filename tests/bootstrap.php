<?php

require_once __DIR__ . '/../config.php';

function verifyMysqlIsRunning() {
    mysqli_report(MYSQLI_REPORT_OFF);

    $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_errno) {
        fwrite(STDERR, "\n");
        fwrite(STDERR, "=========================================\n");
        fwrite(STDERR, " ERROR: MySQL server is not reachable\n");
        fwrite(STDERR, "-----------------------------------------\n");
        fwrite(STDERR, " Host: " . DB_HOST . "\n");
        fwrite(STDERR, " Error: " . $conn->connect_error . "\n");
        fwrite(STDERR, "\n");
        fwrite(STDERR, " Did you forget to start MySQL?\n");
        fwrite(STDERR, " Example:\n");
        fwrite(STDERR, "   brew services start mysql@8.4\n");
        fwrite(STDERR, "=========================================\n");
        exit(1);
    }

    $conn->close();
}

verifyMysqlIsRunning();
