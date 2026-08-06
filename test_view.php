<?php
require 'vendor/autoload.php';
\ = require_once 'bootstrap/app.php';
\->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
file_put_contents('test_view_output.html', view('admin.estudiantes.index')->render());
echo 'View rendered to test_view_output.html';
