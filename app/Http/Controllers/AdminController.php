<?php
namespace App\Http\Controllers;
use App\Models\{ActivityLog,Attendance,Location,Setting,User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
class AdminController extends Controller {
    public function dashboard() { $today=Attendance::where('attendance_date',now()->toDateString()); return view('admin.dashboard',['users'=>User::where('active',true)->count(),'today'=>(clone $today)->count(),'valid'=>(clone $today)->where('status','valid')->count(),'late'=>(clone $today)->where('status','terlambat')->count(),'outside'=>(clone $today)->where('status','di_luar_area')->count(),'recent'=>Attendance::with('user')->latest('checked_at')->limit(10)->get()]); }
    public function users() { return view('admin.users',['users'=>User::orderBy('name')->paginate(15)]); }
    public function saveUser(Request $request, ?User $user=null) {
        $rules=['name'=>['required','string','max:120'],'email'=>['required','email','max:255',Rule::unique('users','email')->ignore($user?->id)],'role'=>['required',Rule::in(['admin','user'])],'password'=>[$user?'nullable':'required','string','min:10']];
        $data=$request->validate($rules); if (empty($data['password'])) unset($data['password']);
        if ($user) { if ($user->id===$request->user()->id && $data['role']!=='admin') return back()->withErrors(['role'=>'Tidak dapat menurunkan peran admin sendiri.']); $user->update($data); $action='user_updated'; }
        else { $user=User::create($data); $action='user_created'; }
        ActivityLog::create(['user_id'=>$request->user()->id,'action'=>$action]); return redirect()->route('admin.users')->with('success','Pengguna tersimpan.');
    }
    public function toggle(Request $request, User $user) { abort_if($user->id===$request->user()->id,422,'Tidak dapat menonaktifkan akun sendiri.'); $user->update(['active'=>!$user->active]); ActivityLog::create(['user_id'=>$request->user()->id,'action'=>'user_updated']); return back(); }
    private function filtered(Request $request) { $q=Attendance::with(['user','location']); if ($request->filled('date')) $q->whereDate('attendance_date',$request->date); if ($request->filled('status')) $q->where('status',$request->status); if ($request->filled('search')) $q->whereHas('user',fn($u)=>$u->where('name','ilike','%'.addcslashes($request->search,'%_\\').'%')); return $q->latest('checked_at'); }
    public function attendance(Request $request) { $request->validate(['date'=>'nullable|date','status'=>['nullable',Rule::in(['valid','terlambat','di_luar_area'])],'search'=>'nullable|string|max:100']); return view('admin.attendance',['rows'=>$this->filtered($request)->paginate(20)->withQueryString()]); }
    public function report(Request $request) { $request->validate(['date'=>'nullable|date','status'=>['nullable',Rule::in(['valid','terlambat','di_luar_area'])],'search'=>'nullable|string|max:100']); return response()->streamDownload(function () use ($request) { $f=fopen('php://output','w'); fputcsv($f,['Nama','Email','Tanggal','Waktu WIB','Status','Jarak (m)','Latitude','Longitude']); $this->filtered($request)->chunk(200,function ($items) use ($f) { foreach ($items as $a) fputcsv($f,[$a->user->name,$a->user->email,$a->attendance_date->format('Y-m-d'),$a->checked_at->timezone('Asia/Jakarta')->format('H:i:s'),$a->status,$a->distance_meters,$a->latitude,$a->longitude]); }); fclose($f); },'laporan-hadirin.csv',['Content-Type'=>'text/csv; charset=UTF-8']); }
    public function locations() { return view('admin.locations',['locations'=>Location::orderBy('id')->get(),'reject'=>Setting::find('reject_outside')?->value==='1','lateAfter'=>Setting::find('late_after')?->value??'08:00']); }
    public function saveLocation(Request $request) { $d=$request->validate(['name'=>'required|string|max:120','latitude'=>'required|numeric|between:-90,90','longitude'=>'required|numeric|between:-180,180','radius_meters'=>'required|integer|between:1,100000','reject_outside'=>'nullable|boolean','late_after'=>'required|date_format:H:i']); \Illuminate\Support\Facades\DB::transaction(function () use ($d) { Location::query()->update(['active'=>false]); Location::create(['name'=>$d['name'],'latitude'=>$d['latitude'],'longitude'=>$d['longitude'],'radius_meters'=>$d['radius_meters'],'active'=>true]); Setting::updateOrCreate(['key'=>'reject_outside'],['value'=>(string)($d['reject_outside']??0)]); Setting::updateOrCreate(['key'=>'late_after'],['value'=>$d['late_after']]); }); return back()->with('success','Lokasi aktif diperbarui.'); }
}
