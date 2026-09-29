<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Attendance extends Model {
    protected $fillable=['user_id','location_id','attendance_date','checked_at','latitude','longitude','distance_meters','photo_path','status'];
    protected function casts(): array { return ['attendance_date'=>'date','checked_at'=>'datetime']; }
    public function user() { return $this->belongsTo(User::class); }
    public function location() { return $this->belongsTo(Location::class); }
}
