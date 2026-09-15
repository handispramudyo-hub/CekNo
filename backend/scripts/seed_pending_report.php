<?php

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$p = App\Models\PhoneNumber::query()->first();
$u = App\Models\User::query()->where('email', 'user@cekno.id')->first();

$rep = App\Models\Report::create([
    'phone_number_id' => $p->id,
    'user_id' => $u->id,
    'category' => 'fraud',
    'description' => 'Menghubungi tengah malam mengaku bank meminta kode OTP transfer.',
    'evidence' => null,
    'evidence_type' => 'text',
    'description_hash' => 'e2e-seed-'.uniqid(),
    'status' => 'pending',
]);

echo 'pending reports: '.App\Models\Report::query()->where('status', 'pending')->count().PHP_EOL;
echo 'created report id='.$rep->id.PHP_EOL;