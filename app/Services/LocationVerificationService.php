<?php
namespace App\Services;
class LocationVerificationService {
    public function meters(float $a, float $b, float $c, float $d): int {
        $r=6371000; $dLat=deg2rad($c-$a); $dLon=deg2rad($d-$b);
        $h=sin($dLat/2)**2+cos(deg2rad($a))*cos(deg2rad($c))*sin($dLon/2)**2;
        return (int) round(2*$r*asin(min(1,sqrt($h))));
    }
}
