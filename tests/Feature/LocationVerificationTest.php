<?php
namespace Tests\Feature;
use App\Services\LocationVerificationService;
use PHPUnit\Framework\TestCase;
class LocationVerificationTest extends TestCase {
    public function test_same_point_has_zero_distance(): void { $this->assertSame(0,(new LocationVerificationService)->meters(-7.33,110.5,-7.33,110.5)); }
    public function test_distant_point_exceeds_radius(): void { $this->assertGreaterThan(100,(new LocationVerificationService)->meters(-7.33,110.5,-7.34,110.5)); }
}
