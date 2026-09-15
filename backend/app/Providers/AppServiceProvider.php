<?php

namespace App\Providers;

use App\Models\PhoneNumber;
use App\Services\PhoneNormalizer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Named rate limiters — values read from config/services.php (env-overridable)
        $this->configureRateLimiting();

        // Route parametrik {phone} untuk rute publik: selalu dimuat lewat
        // digit nomor (bukan primary key) agar aman dipakai dari URL /numbers/0812…
        Route::bind('phone', function (string $value) {
            try {
                $normalized = app(PhoneNormalizer::class)->normalize($value);
            } catch (Throwable $e) {
                throw new \Illuminate\Http\Exceptions\HttpResponseException(
                    response()->json(['message' => $e->getMessage()], 422)
                );
            }

            return PhoneNumber::firstOrCreate(
                ['normalized_number' => $normalized],
                ['phone_number' => $normalized, 'country_code' => '+62'],
            );
        });
    }

    private function configureRateLimiting(): void
    {
        $keys = ['register', 'login', 'report', 'review', 'tag', 'search'];

        foreach ($keys as $key) {
            RateLimiter::for("auth.{$key}", function (Request $request) use ($key) {
                $max = (int) config("services.throttle.{$key}", 30);

                return Limit::perMinute($max)->by($request->ip());
            });
        }

        RateLimiter::for('global', function (Request $request) {
            return Limit::perMinute(120)->by($request->ip());
        });
    }
}
