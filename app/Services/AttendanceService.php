<?php
namespace App\Services;
use App\Models\{Attendance,Location,Setting,ActivityLog};
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class AttendanceService {
    public function __construct(private LocationVerificationService $geo, private AttendancePhotoService $photos) {}
    public function create(int $userId, UploadedFile $photo, float $lat, float $lon): Attendance {
        $location=Location::where('active',true)->first();
        if (!$location) throw ValidationException::withMessages(['location'=>'Lokasi resmi belum diatur admin.']);
        $distance=$this->geo->meters((float)$location->latitude,(float)$location->longitude,$lat,$lon);
        $outside=$distance > $location->radius_meters;
        $reject=Setting::find('reject_outside')?->value === '1';
        if ($outside && $reject) { ActivityLog::create(['user_id'=>$userId,'action'=>'attendance_rejected']); throw ValidationException::withMessages(['location'=>'Anda berada di luar area absensi.']); }
        $date=now()->toDateString();
        if (Attendance::where('user_id',$userId)->where('attendance_date',$date)->exists()) throw ValidationException::withMessages(['attendance'=>'Anda sudah melakukan absensi hari ini.']);
        $lateAfter=Setting::find('late_after')?->value ?? '08:00';
        $status=$outside?'di_luar_area':(now()->format('H:i')>$lateAfter?'terlambat':'valid');
        $path=$this->photos->upload($photo,$userId);
        try {
            return DB::transaction(function () use ($userId,$location,$date,$lat,$lon,$distance,$status,$path) {
                $attendance=Attendance::create(['user_id'=>$userId,'location_id'=>$location->id,'attendance_date'=>$date,'checked_at'=>now(),'latitude'=>$lat,'longitude'=>$lon,'distance_meters'=>$distance,'photo_path'=>$path,'status'=>$status]);
                ActivityLog::create(['user_id'=>$userId,'action'=>'attendance_created']); return $attendance;
            });
        } catch (\Throwable $e) { $this->photos->delete($path); if ($e instanceof \Illuminate\Database\UniqueConstraintViolationException) throw ValidationException::withMessages(['attendance'=>'Anda sudah melakukan absensi hari ini.']); throw $e; }
    }
}
