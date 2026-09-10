<?php
// 01_hello.php

echo "Hello, World!\n";
print "Welcome to PHP 8.3\n";

echo PHP_VERSION . "\n";
echo PHP_OS . "\n";

$name = "Ifti";
echo "Developer: " . $name . "\n";

$intro = <<<EOT
Project: Modern E-Commerce Platform
Stack: Laravel + React + Vite
Purpose: Learning PHP/Laravel by building a real product
EOT;
echo $intro . "\n";

echo "\n--- Script 01 Complete ---\n";
