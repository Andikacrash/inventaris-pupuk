<?php

namespace Database\Seeders;

use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\InstallmentPayment;
use App\Models\InstallmentPlan;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NormalizeUsersSeeder extends Seeder
{
    /**
     * Ganti petugas Test User / Manager → hanya Admin & Kasir.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $kasirUsers = User::where('role', 'kasir')->orderBy('id')->get();

        if (! $admin && $kasirUsers->isEmpty()) {
            $this->command?->warn('NormalizeUsersSeeder: tidak ada Admin/Kasir.');

            return;
        }

        $allowedIds = collect([$admin?->id])
            ->merge($kasirUsers->pluck('id'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $legacyIds = User::query()
            ->whereNotIn('role', ['admin', 'kasir'])
            ->orWhere('email', 'test@example.com')
            ->orWhere('name', 'Test User')
            ->pluck('id')
            ->all();

        if (empty($legacyIds)) {
            $legacyIds = User::query()
                ->whereNotIn('id', $allowedIds)
                ->pluck('id')
                ->all();
        }

        if (empty($legacyIds)) {
            $this->command?->info('NormalizeUsersSeeder: tidak ada user legacy.');

            return;
        }

        $pickKasir = function (int $i) use ($kasirUsers, $admin): int {
            if ($kasirUsers->isNotEmpty()) {
                return $kasirUsers[$i % $kasirUsers->count()]->id;
            }

            return $admin->id;
        };

        $updated = 0;

        DB::transaction(function () use ($legacyIds, $pickKasir, $admin, &$updated) {
            $tables = [
                [StockMovement::class, 'stock_movements'],
                [Sale::class, 'sales'],
                [Debt::class, 'debts'],
                [DebtPayment::class, 'debt_payments'],
                [InstallmentPlan::class, 'installment_plans'],
                [InstallmentPayment::class, 'installment_payments'],
            ];

            foreach ($tables as [$model, $label]) {
                $rows = $model::query()->whereIn('user_id', $legacyIds)->orderBy('id')->get();
                $i = 0;
                foreach ($rows as $row) {
                    $row->user_id = $pickKasir($i++);
                    $row->saveQuietly();
                    $updated++;
                }
                if ($rows->isNotEmpty()) {
                    $this->command?->line("  {$label}: {$rows->count()} baris");
                }
            }

            User::query()->whereIn('id', $legacyIds)->delete();
        });

        $this->command?->info("NormalizeUsersSeeder: {$updated} baris diperbarui ke Admin/Kasir. User legacy dihapus.");
    }
}
