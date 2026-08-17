<?php

use Illuminate\Support\Carbon;

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$d = Carbon::parse('2026-08-31');
echo "addMonthsNoOverflow(3)=".$d->copy()->addMonthsNoOverflow(3)->toDateString()."\n";
echo "2026-10-01 +12mo=".Carbon::parse('2026-10-01')->addMonthsNoOverflow(12)->toDateString()."\n";
echo "2026-08-31 +12=".Carbon::parse('2026-08-31')->addMonthsNoOverflow(12)->toDateString()."\n";
