<?php
// Prompt the user for input
$password = readline("Enter password: ");

// Output the result
echo hash('sha256', $password) . "\n";


?>
