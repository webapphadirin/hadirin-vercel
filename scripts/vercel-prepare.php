<?php
// The community runtime invokes this Composer hook during its build.
// Only production builds initialize the database; previews never run migrations.
if (getenv('VERCEL_ENV') !== 'production') {
    fwrite(STDOUT,"Preview build: skipping database initialization.\n");
    exit(0);
}
foreach (['APP_KEY','DB_HOST','DB_USERNAME','DB_PASSWORD','SUPABASE_URL','SUPABASE_SERVICE_ROLE_KEY','ADMIN_EMAIL','ADMIN_PASSWORD'] as $key) {
    if (!getenv($key)) { fwrite(STDERR,"Missing production environment variable: ".$key."\n"); exit(1); }
}
define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$kernel=$app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
foreach ([['migrate',['--force'=>true]],['db:seed',['--force'=>true]]] as [$command,$arguments]) {
    $status=$kernel->call($command,$arguments);
    fwrite(STDOUT,$kernel->output());
    if ($status!==0) exit($status);
}
