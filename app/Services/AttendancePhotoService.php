<?php
namespace App\Services;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;
class AttendancePhotoService {
    private function base(): string { return rtrim(config('services.supabase.url') ?? '', '/'); }
    private function bucket(): string { return config('services.supabase.bucket'); }
    private function headers(): array { return ['apikey'=>config('services.supabase.key'),'Authorization'=>'Bearer '.config('services.supabase.key')]; }
    public function upload(UploadedFile $photo, int $userId): string {
        if (!$this->base() || !config('services.supabase.key')) throw new RuntimeException('Penyimpanan foto belum dikonfigurasi.');
        $path=$userId.'/'.(string) \Illuminate\Support\Str::uuid().'.jpg';
        $bytes=file_get_contents($photo->getRealPath());
        $response=Http::withHeaders($this->headers()+['Content-Type'=>'image/jpeg','x-upsert'=>'false'])->withBody($bytes,'image/jpeg')->post($this->base().'/storage/v1/object/'.$this->bucket().'/'.$path);
        if (!$response->successful()) throw new RuntimeException('Foto tidak berhasil disimpan.');
        return $path;
    }
    public function delete(string $path): void {
        Http::withHeaders($this->headers())->delete($this->base().'/storage/v1/object/'.$this->bucket().'/'.$path);
    }
    public function bytes(string $path): string {
        $response=Http::withHeaders($this->headers())->get($this->base().'/storage/v1/object/authenticated/'.$this->bucket().'/'.$path);
        if (!$response->successful()) throw new RuntimeException('Foto tidak tersedia.');
        return $response->body();
    }
}
