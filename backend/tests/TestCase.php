<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Ganti identitas token antar-request dalam satu test.
     *
     * RequestGuard Laravel men-cache user per instance guard untuk satu
     * aplikasi (berlaku sepanjang proses test). Saat test berpindah token,
     * cache harus di-flush agar request berikutnya memakai identitas baru.
     */
    protected function withUserToken(User $user): static
    {
        $this->app['auth']->forgetGuards();

        return $this->withToken($user->createToken('test')->plainTextToken);
    }
}