<?php
namespace App\Http\Controllers;
use App\Models\{Attendance,Location};
use App\Services\{AttendanceService,AttendancePhotoService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class AttendanceController extends Controller {
    public function dashboard() { return view('attendance.dashboard',['today'=>Attendance::where('user_id',Auth::id())->where('attendance_date',now()->toDateString())->first(),'location'=>Location::where('active',true)->first()]); }
    public function store(Request $request, AttendanceService $service) {
        $data=$request->validate(['photo'=>['required','image','mimes:jpeg,jpg','max:3072'],'latitude'=>['required','numeric','between:-90,90'],'longitude'=>['required','numeric','between:-180,180']]);
        $service->create(Auth::id(),$request->file('photo'),(float)$data['latitude'],(float)$data['longitude']);
        return redirect()->route('dashboard')->with('success','Absensi berhasil dicatat.');
    }
    public function history() { return view('attendance.history',['rows'=>Attendance::with('location')->where('user_id',Auth::id())->latest('checked_at')->paginate(15)]); }
    public function photo(Attendance $attendance, AttendancePhotoService $photos) {
        abort_unless(Auth::user()->role==='admin' || $attendance->user_id===Auth::id(),403);
        try { return response($photos->bytes($attendance->photo_path),200,['Content-Type'=>'image/jpeg','Cache-Control'=>'private, no-store']); } catch (\Throwable $e) { abort(404); }
    }
}
