<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class User extends Authenticatable {
    use Notifiable;
    protected $fillable=['name','email','password','role','active'];
    protected $hidden=['password','remember_token'];
    protected function casts(): array { return ['password'=>'hashed','active'=>'boolean']; }
    public function attendances() { return $this->hasMany(Attendance::class); }
}
