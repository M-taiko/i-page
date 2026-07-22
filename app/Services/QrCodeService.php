<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\QrCode;
use App\Models\QrScanLog;
use Illuminate\Database\Eloquent\Model;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrCodeGenerator;
use Illuminate\Support\Str;

class QrCodeService
{
    public function generate(
        Model $ownable,
        Organization $organization,
        string $label = null,
        string $url = null,
        int $expiryDays = null,
        array $branding = null
    ): QrCode {
        $code = Str::random(32);
        $url = $url ?? route('qr.redirect', ['code' => $code]);

        $qrCode = QrCode::create([
            'organization_id' => $organization->id,
            'ownable_type' => $ownable::class,
            'ownable_id' => $ownable->id,
            'code' => $code,
            'label' => $label,
            'url' => $url,
            'expires_at' => $expiryDays ? now()->addDays($expiryDays) : null,
            'is_branded' => $branding !== null,
            'metadata' => $branding,
        ]);

        ActivityLogService::logCreated($qrCode, "QR code generated for " . $ownable::class);

        return $qrCode;
    }

    /**
     * SVG rather than PNG: the installed simple-qrcode version only rasterizes
     * PNG via the Imagick backend, which isn't available on this environment
     * (only GD, which the package doesn't support). SVG needs no extension.
     */
    public function generateImage(QrCode $qrCode, int $size = 300): string
    {
        $qrImage = QrCodeGenerator::size($size)
            ->format('svg')
            ->generate($qrCode->url);

        return base64_encode($qrImage);
    }

    public function generateImageFile(QrCode $qrCode, string $path, int $size = 300): string
    {
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        QrCodeGenerator::size($size)
            ->format('svg')
            ->generate($qrCode->url, $path);

        return $path;
    }

    public function logScan(QrCode $qrCode, int $organizationId, ?int $userId = null): QrScanLog
    {
        if (!$qrCode->canBeScanned()) {
            throw new \Exception('QR code cannot be scanned (inactive or expired)');
        }

        $qrCode->increment('scan_count');

        $deviceType = $this->detectDeviceType(request()->userAgent());

        return QrScanLog::create([
            'qr_code_id' => $qrCode->id,
            'user_id' => $userId ?? auth()->id(),
            'organization_id' => $organizationId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'device_type' => $deviceType,
            'referer' => request()->header('referer'),
            'location' => request()->header('cf-ipcountry'),
        ]);
    }

    public function findByCode(string $code): ?QrCode
    {
        return QrCode::where('code', $code)
            ->notExpired()
            ->active()
            ->first();
    }

    public function getScanStats(QrCode $qrCode): array
    {
        $scans = $qrCode->scanLogs()->get();

        return [
            'total_scans' => $qrCode->scan_count,
            'unique_users' => $scans->pluck('user_id')->unique()->count(),
            'device_breakdown' => $scans->groupBy('device_type')->map->count(),
            'scans_by_day' => $scans->groupBy(function ($scan) {
                return $scan->created_at->format('Y-m-d');
            })->map->count(),
        ];
    }

    public function getOrganizationQrStats(Organization $organization): array
    {
        $qrCodes = $organization->qrCodes ?? collect();
        $allScans = QrScanLog::where('organization_id', $organization->id)->get();

        return [
            'total_qr_codes' => $qrCodes->count(),
            'active_qr_codes' => $qrCodes->where('is_active', true)->count(),
            'total_scans' => $allScans->count(),
            'unique_scanners' => $allScans->pluck('user_id')->unique()->count(),
            'scans_today' => $allScans->where('created_at', '>=', now()->startOfDay())->count(),
        ];
    }

    public function deactivate(QrCode $qrCode): QrCode
    {
        $qrCode->update(['is_active' => false]);
        ActivityLogService::logUpdated($qrCode, ['is_active' => ['old' => true, 'new' => false]]);
        return $qrCode;
    }

    public function reactivate(QrCode $qrCode): QrCode
    {
        $qrCode->update(['is_active' => true]);
        ActivityLogService::logUpdated($qrCode, ['is_active' => ['old' => false, 'new' => true]]);
        return $qrCode;
    }

    public function delete(QrCode $qrCode): bool
    {
        ActivityLogService::logDeleted($qrCode);
        return $qrCode->delete();
    }

    protected function detectDeviceType(?string $userAgent): string
    {
        if (!$userAgent) {
            return 'unknown';
        }

        if (preg_match('/mobile|android|iphone|ipod|windows phone/i', $userAgent)) {
            return 'mobile';
        }

        if (preg_match('/tablet|ipad|kindle/i', $userAgent)) {
            return 'tablet';
        }

        return 'desktop';
    }
}
