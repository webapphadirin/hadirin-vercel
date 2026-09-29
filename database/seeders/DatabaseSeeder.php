<?php
namespace Database\Seeders;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder {
    public function run(): void {
        if (!env('ADMIN_EMAIL') || !env('ADMIN_PASSWORD')) return;
        User::firstOrCreate(['email'=>env('ADMIN_EMAIL')], ['name'=>env('ADMIN_NAME','Administrator'),'password'=>Hash::make(env('ADMIN_PASSWORD')),'role'=>'admin','active'=>true]);
    }
}
