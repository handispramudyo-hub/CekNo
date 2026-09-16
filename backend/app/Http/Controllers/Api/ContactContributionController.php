<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Consent;
use App\Models\ContactContribution;
use App\Models\PhoneNumber;
use App\Services\PhoneNormalizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class ContactContributionController extends Controller
{
    public const CONSENT_VERSION = '1.0';
    public const MAX_CONTACTS_PER_SYNC = 50;
    public const MAX_LABEL_LENGTH = 64;

    public function __construct(protected PhoneNormalizer $normalizer) {}

    /**
     * Ambil daftar kontribusi kontak milik user.
     */
    public function index(Request $request): JsonResponse
    {
        $contributions = $request->user()
            ->contactContributions()
            ->with('phoneNumber:id,normalized_number')
            ->latest()
            ->paginate(20);

        return response()->json([
            'consent' => $request->user()
                ->consents()
                ->where('scope', 'contact_contribution')
                ->latest('consent_version')
                ->first(),
            'consent_version' => self::CONSENT_VERSION,
            'contributions' => $contributions,
        ]);
    }

    /**
     * Sinkronkan label kontak dari buku kontak user (bulk, autosync pasca-consent).
     */
    public function sync(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'consent_version' => ['required', 'string', 'max:32'],
            'contacts' => ['required', 'array', 'max:'.self::MAX_CONTACTS_PER_SYNC],
            'contacts.*.phone' => ['required', 'string', 'max:30'],
            'contacts.*.label' => ['required', 'string', 'max:'.self::MAX_LABEL_LENGTH],
        ]);

        if ($data['consent_version'] !== self::CONSENT_VERSION) {
            throw ValidationException::withMessages([
                'consent_version' => 'Versi persetujuan tidak dikenali.',
            ]);
        }

        // Simpan jejak persetujuan (consent log) bila belum ada.
        Consent::firstOrCreate([
            'user_id' => $user->id,
            'consent_version' => $data['consent_version'],
            'scope' => 'contact_contribution',
        ]);

        $errors = [];
        $created = 0;
        $duplicates = 0;

        foreach ($data['contacts'] as $contact) {
            try {
                $normalized = $this->normalizer->normalize($contact['phone']);
            } catch (Throwable) {
                $errors[] = ['phone' => $contact['phone'], 'message' => 'Nomor tidak valid.'];
                continue;
            }

            $phone = PhoneNumber::firstOrCreate(
                ['normalized_number' => $normalized],
                ['phone_number' => $normalized, 'country_code' => '+62'],
            );

            $label = trim($contact['label']);
            if ($label === '') {
                $errors[] = ['phone' => $contact['phone'], 'message' => 'Label kosong.'];
                continue;
            }
            $labelNormalized = mb_strtolower($label);

            try {
                $contribution = ContactContribution::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'phone_number_id' => $phone->id,
                        'label_normalized' => $labelNormalized,
                    ],
                    [
                        'label' => $label,
                        'category' => 'general',
                        'consent_version' => $data['consent_version'],
                        'status' => ContactContribution::STATUS_PENDING,
                    ],
                );

                $contribution->wasRecentlyCreated ? $created++ : $duplicates++;
            } catch (Throwable) {
                $errors[] = ['phone' => $contact['phone'], 'message' => 'Gagal menyimpan kontribusi.'];
            }
        }

        return response()->json([
            'created' => $created,
            'duplicates' => $duplicates,
            'errors' => $errors,
        ], 200);
    }

    /**
     * Tarik kembali (withdraw) kontribusi yang dibuat user.
     */
    public function destroy(Request $request, ContactContribution $contribution): JsonResponse
    {
        $user = $request->user();

        if ($contribution->user_id !== $user->id) {
            return response()->json(['message' => 'Aksi tidak diizinkan.'], 403);
        }

        if ($contribution->status === ContactContribution::STATUS_WITHDRAWN) {
            return response()->json(['message' => 'Kontribusi sudah ditarik sebelumnya.'], 422);
        }

        $contribution->forceFill(['status' => ContactContribution::STATUS_WITHDRAWN])->save();

        return response()->json(['message' => 'Kontribusi telah ditarik.']);
    }
}