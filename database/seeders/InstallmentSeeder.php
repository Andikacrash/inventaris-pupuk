<?php

namespace Database\Seeders;

use App\Models\Debt;
use App\Models\InstallmentPayment;
use App\Models\InstallmentPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class InstallmentSeeder extends Seeder
{
    /**
     * Rencana cicilan + jadwal angsuran (installment_payments) untuk piutang terbuka.
     */
    public function run(): void
    {
        $user = User::where('role', 'kasir')->orderBy('id')->first()
            ?? User::where('role', 'admin')->first()
            ?? User::first();

        if (! $user) {
            $this->command?->warn('InstallmentSeeder: tidak ada user.');

            return;
        }

        InstallmentPayment::query()->delete();
        InstallmentPlan::query()->delete();

        $openDebts = Debt::query()
            ->whereIn('status', ['unpaid', 'partial'])
            ->where('remaining_amount', '>', 0)
            ->orderByDesc('remaining_amount')
            ->limit(5)
            ->get();

        if ($openDebts->isEmpty()) {
            $this->command?->warn('InstallmentSeeder: tidak ada piutang terbuka. Jalankan DebtSeeder dulu.');

            return;
        }

        $planConfigs = [
            ['count' => 4, 'frequency' => 'monthly', 'paid_installments' => 1],
            ['count' => 5, 'frequency' => 'monthly', 'paid_installments' => 0],
            ['count' => 4, 'frequency' => 'monthly', 'paid_installments' => 2],
            ['count' => 2, 'frequency' => 'weekly', 'paid_installments' => 1],
            ['count' => 6, 'frequency' => 'monthly', 'paid_installments' => 0],
        ];

        $createdPlans = 0;
        $createdPayments = 0;

        foreach ($openDebts as $index => $debt) {
            $scenario = $planConfigs[$index] ?? ['count' => 3, 'frequency' => 'monthly', 'paid_installments' => 0];

            $remaining = (float) $debt->remaining_amount;
            $count = (int) $scenario['count'];
            $installmentAmount = round($remaining / $count, 2);
            $totalPlan = round($installmentAmount * $count, 2);

            if ($totalPlan > $remaining + 0.01) {
                $installmentAmount = floor(($remaining / $count) * 100) / 100;
                $totalPlan = round($installmentAmount * $count, 2);
            }

            $startDate = Carbon::today()->subMonths(1);
            $endDate = match ($scenario['frequency']) {
                'weekly' => $startDate->copy()->addWeeks($count - 1),
                'daily' => $startDate->copy()->addDays($count - 1),
                default => $startDate->copy()->addMonths($count - 1),
            };

            $plan = InstallmentPlan::create([
                'debt_id' => $debt->id,
                'total_amount' => $totalPlan,
                'installment_count' => $count,
                'installment_amount' => $installmentAmount,
                'frequency' => $scenario['frequency'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'paid_amount' => 0,
                'paid_count' => 0,
                'status' => 'active',
                'notes' => 'Rencana cicilan demo — '.$count.'x Rp '.number_format($installmentAmount, 0, ',', '.'),
                'user_id' => $user->id,
            ]);
            $createdPlans++;

            $currentDate = $startDate->copy();
            $paidTarget = min($count, (int) $scenario['paid_installments']);

            for ($i = 1; $i <= $count; $i++) {
                $isPaid = $i <= $paidTarget;
                $paymentDate = $isPaid ? $currentDate->copy() : null;

                $payment = InstallmentPayment::create([
                    'installment_plan_id' => $plan->id,
                    'debt_id' => $debt->id,
                    'installment_number' => $i,
                    'amount' => $installmentAmount,
                    'due_date' => $currentDate->copy(),
                    'payment_date' => $paymentDate,
                    'status' => $isPaid ? 'paid' : ($currentDate->isPast() ? 'overdue' : 'pending'),
                    'payment_method' => $isPaid ? 'transfer' : null,
                    'notes' => $isPaid ? 'Angsuran ke-'.$i.' lunas' : null,
                    'user_id' => $isPaid ? $user->id : null,
                ]);
                $createdPayments++;

                if ($isPaid) {
                    $plan->increment('paid_count');
                    $plan->increment('paid_amount', $installmentAmount);

                    $debt->paid_amount = (float) $debt->paid_amount + $installmentAmount;
                    $debt->remaining_amount = max(0, (float) $debt->remaining_amount - $installmentAmount);
                    $debt->status = $debt->remaining_amount <= 0 ? 'paid' : 'partial';
                    $debt->save();

                    if ($debt->sale) {
                        $debt->sale->update([
                            'debt_amount' => $debt->remaining_amount,
                            'debt_status' => $debt->status,
                            'status' => $debt->status === 'paid' ? 'completed' : 'pending',
                        ]);
                    }
                }

                match ($scenario['frequency']) {
                    'weekly' => $currentDate->addWeek(),
                    'daily' => $currentDate->addDay(),
                    default => $currentDate->addMonth(),
                };
            }

            if ($plan->paid_count >= $plan->installment_count) {
                $plan->update(['status' => 'completed']);
            }

            $debt->refresh();
        }

        $this->command?->info("InstallmentSeeder: {$createdPlans} rencana cicilan, {$createdPayments} jadwal angsuran.");
    }
}
