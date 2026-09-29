<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('users', function (Blueprint $t) { $t->id(); $t->string('name'); $t->string('email')->unique(); $t->string('password'); $t->string('role')->default('user'); $t->boolean('active')->default(true); $t->rememberToken(); $t->timestamps(); });
        Schema::create('password_reset_tokens', function (Blueprint $t) { $t->string('email')->primary(); $t->string('token'); $t->timestamp('created_at')->nullable(); });
        Schema::create('sessions', function (Blueprint $t) { $t->string('id')->primary(); $t->foreignId('user_id')->nullable()->index(); $t->string('ip_address',45)->nullable(); $t->text('user_agent')->nullable(); $t->longText('payload'); $t->integer('last_activity')->index(); });
        Schema::create('cache', function (Blueprint $t) { $t->string('key')->primary(); $t->mediumText('value'); $t->integer('expiration'); });
        Schema::create('cache_locks', function (Blueprint $t) { $t->string('key')->primary(); $t->string('owner'); $t->integer('expiration'); });
        Schema::create('locations', function (Blueprint $t) { $t->id(); $t->string('name'); $t->decimal('latitude',10,7); $t->decimal('longitude',10,7); $t->unsignedInteger('radius_meters')->default(100); $t->boolean('active')->default(true); $t->timestamps(); });
        Schema::create('settings', function (Blueprint $t) { $t->string('key')->primary(); $t->text('value'); $t->timestamps(); });
        Schema::create('attendances', function (Blueprint $t) { $t->id(); $t->foreignId('user_id')->constrained()->restrictOnDelete(); $t->foreignId('location_id')->constrained()->restrictOnDelete(); $t->date('attendance_date'); $t->timestampTz('checked_at'); $t->decimal('latitude',10,7); $t->decimal('longitude',10,7); $t->unsignedInteger('distance_meters'); $t->string('photo_path'); $t->string('status'); $t->timestamps(); $t->unique(['user_id','attendance_date']); $t->index(['attendance_date','status']); });
        Schema::create('activity_logs', function (Blueprint $t) { $t->id(); $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); $t->string('action'); $t->timestamps(); });
    }
    public function down(): void { foreach (['activity_logs','attendances','settings','locations','cache_locks','cache','sessions','password_reset_tokens','users'] as $table) Schema::dropIfExists($table); }
};
