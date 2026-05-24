<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\InstallmentPlan;
use App\Models\InstallmentPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class InstallmentController extends Controller
{
    /**
     * Create an installment plan for a debt.
     */
    public function createPlan(Request $request, Debt $debt)
    {
        $validated = $request->validate([
            'installment_count' => 'required|integer|min:2|max:60',
            'installment_amount' => 'required|numeric|min:0.01',
            'frequency' => 'required|in:daily,weekly,monthly',
            'start_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            // Check if there's already an active plan
            if ($debt->activeInstallmentPlan) {
                return back()->withErrors(['error' => 'Sudah ada rencana cicilan aktif untuk hutang ini']);
            }

            // Calculate total amount
            $totalAmount = $validated['installment_count'] * $validated['installment_amount'];
            
            // Validate total amount doesn't exceed remaining debt
            if ($totalAmount > $debt->remaining_amount) {
                return back()->withErrors(['installment_amount' => 'Total cicilan melebihi sisa hutang']);
            }

            // Calculate end date based on frequency
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = null;
            
            switch ($validated['frequency']) {
                case 'daily':
                    $endDate = $startDate->copy()->addDays($validated['installment_count'] - 1);
                    break;
                case 'weekly':
                    $endDate = $startDate->copy()->addWeeks($validated['installment_count'] - 1);
                    break;
                case 'monthly':
                    $endDate = $startDate->copy()->addMonths($validated['installment_count'] - 1);
                    break;
            }

            // Create installment plan
            $plan = InstallmentPlan::create([
                'debt_id' => $debt->id,
                'total_amount' => $totalAmount,
                'installment_count' => $validated['installment_count'],
                'installment_amount' => $validated['installment_amount'],
                'frequency' => $validated['frequency'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'active',
                'notes' => $validated['notes'] ?? null,
                'user_id' => Auth::id(),
            ]);

            // Create installment payment schedules
            $currentDate = $startDate->copy();
            for ($i = 1; $i <= $validated['installment_count']; $i++) {
                InstallmentPayment::create([
                    'installment_plan_id' => $plan->id,
                    'debt_id' => $debt->id,
                    'installment_number' => $i,
                    'amount' => $validated['installment_amount'],
                    'due_date' => $currentDate->copy(),
                    'status' => 'pending',
                ]);

                // Move to next due date
                switch ($validated['frequency']) {
                    case 'daily':
                        $currentDate->addDay();
                        break;
                    case 'weekly':
                        $currentDate->addWeek();
                        break;
                    case 'monthly':
                        $currentDate->addMonth();
                        break;
                }
            }

            DB::commit();

            return redirect()->route('debts.show', $debt)
                ->with('success', 'Rencana cicilan berhasil dibuat');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal membuat rencana cicilan: ' . $e->getMessage()]);
        }
    }

    /**
     * Pay an installment.
     */
    public function payInstallment(Request $request, InstallmentPayment $installmentPayment)
    {
        $validated = $request->validate([
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,transfer,card',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            if ($installmentPayment->status === 'paid') {
                return back()->withErrors(['error' => 'Cicilan ini sudah dibayar']);
            }

            $debt = $installmentPayment->debt;
            $plan = $installmentPayment->installmentPlan;

            // Update installment payment
            $installmentPayment->update([
                'payment_date' => $validated['payment_date'],
                'status' => 'paid',
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
                'user_id' => Auth::id(),
            ]);

            // Update installment plan
            $plan->increment('paid_count');
            $plan->increment('paid_amount', $installmentPayment->amount);

            // Check if plan is completed
            if ($plan->paid_count >= $plan->installment_count) {
                $plan->update(['status' => 'completed']);
            }

            // Update debt
            $newPaidAmount = $debt->paid_amount + $installmentPayment->amount;
            $newRemainingAmount = $debt->remaining_amount - $installmentPayment->amount;

            $debt->update([
                'paid_amount' => $newPaidAmount,
                'remaining_amount' => max(0, $newRemainingAmount),
                'status' => $newRemainingAmount <= 0 ? 'paid' : ($newPaidAmount > 0 ? 'partial' : 'unpaid'),
            ]);

            // Update sale debt status
            if ($debt->sale) {
                $debt->sale->update([
                    'debt_amount' => max(0, $newRemainingAmount),
                    'debt_status' => $newRemainingAmount <= 0 ? 'paid' : ($newPaidAmount > 0 ? 'partial' : 'unpaid'),
                ]);
            }

            DB::commit();

            return redirect()->route('debts.show', $debt)
                ->with('success', 'Cicilan berhasil dibayar');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal membayar cicilan: ' . $e->getMessage()]);
        }
    }

    /**
     * Cancel installment plan.
     */
    public function cancelPlan(InstallmentPlan $plan)
    {
        DB::beginTransaction();

        try {
            if ($plan->status !== 'active') {
                return back()->withErrors(['error' => 'Rencana cicilan tidak aktif']);
            }

            $plan->update(['status' => 'cancelled']);

            DB::commit();

            return redirect()->route('debts.show', $plan->debt)
                ->with('success', 'Rencana cicilan berhasil dibatalkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal membatalkan rencana cicilan: ' . $e->getMessage()]);
        }
    }
}
