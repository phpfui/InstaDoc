@echo off
rem php c:\bin\php-cs-fixer-v2.phar fix --allow-risky=yes
php c:\bin\whitesmith.php src\PHPFUI\*.php
php c:\bin\whitesmith.php tests\*.php

