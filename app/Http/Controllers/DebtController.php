<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\DebtPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class DebtController extends Controller
{
    /**
     * Display a listing of debts.
     */
    public function index(Request $request)
    {
        $view = $request->get('view', 'transactions');

        // Calculate summary statistics
        $allDebts = Debt::selectRaw('
            SUM(remaining_amount) as total_debt,
            COUNT(CASE WHEN status = "unpaid" THEN 1 END) as unpaid_count,
            COUNT(CASE WHEN status = "partial" THEN 1 END) as partial_count,
            COUNT(CASE WHEN status = "paid" THEN 1 END) as paid_count
        ')->first();

        $summary = [
            'total_debt' => $allDebts->total_debt ?? 0,
            'unpaid_count' => $allDebts->unpaid_count ?? 0,
            'partial_count' => $allDebts->partial_count ?? 0,
            'paid_count' => $allDebts->paid_count ?? 0,
        ];

        if ($view === 'grouped') {
            $perPage = 10;
            $page = max(1, (int) $request->get('page', 1));

            $groupQuery = Debt::with(['sale', 'user'])
                ->whereIn('status', ['unpaid', 'partial']);

            if ($request->filled('customer')) {
                $groupQuery->where('customer_name', 'like', '%'.$request->customer.'%');
            }

            if ($request->filled('overdue')) {
                $groupQuery->where('due_date', '<', now());
            }

            $openForGrouping = $groupQuery
                ->orderByRaw('due_date IS NULL, due_date ASC')
                ->orderBy('created_at', 'asc')
                ->get();

            $customerGroups = $openForGrouping
                ->groupBy(fn (Debt $d) => $d->groupKey())
                ->map(function ($items) {
                    /** @var \Illuminate\Support\Collection<int, Debt> $items */
                    $first = $items->first();
                    $sorted = $items->sortBy([
                        fn (Debt $d) => $d->due_date ? $d->due_date->timestamp : PHP_INT_MAX,
                        fn (Debt $d) => $d->created_at->timestamp,
                    ])->values();

                    return [
                        'customer_name' => $first->customer_name,
                        'customer_phone' => $first->customer_phone,
                        'phone_normalized' => Debt::normalizePhone($first->customer_phone),
                        'debts' => $sorted,
                        'debt_ids' => $sorted->pluck('id')->all(),
                        'invoice_count' => $sorted->count(),
                        'total_remaining' => (float) $sorted->sum('remaining_amount'),
                    ];
                })
                ->sortByDesc('total_remaining')
                ->values();

            $total = $customerGroups->count();
            $pageItems = $customerGroups->forPage($page, $perPage)->values();
            $customerGroups = new LengthAwarePaginator(
                $pageItems,
                $total,
                $perPage,
                $page,
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ],
            );

            $debts = null;

            return view('debts.index', compact('debts', 'summary', 'view', 'customerGroups'));
        }

        $query = Debt::with(['sale', 'user'])->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('customer')) {
            $query->where('customer_name', 'like', '%'.$request->customer.'%');
        }

        if ($request->filled('overdue')) {
            $query->where('due_date', '<', now())
                ->where('status', '!=', 'paid');
        }

        $debts = $query->paginate(10);
        $customerGroups = null;

        return view('debts.index', compact('debts', 'summary', 'view', 'customerGroups'));
    }

    /**
     * Catat satu pembayaran dan bagi ke beberapa faktur (prioritas: jatuh tempo lebih dulu, lalu transaksi lebih lama).
     */
    public function recordBulkPayment(Request $request)
    {
        $validated = $request->validate([
            'debt_ids' => 'required|array|min:1',
            'debt_ids.*' => 'integer|exists:debts,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,transfer,card',
            'notes' => 'nullable|string|max:1000',
        ]);

        $debts = Debt::with('sale')
            ->whereIn('id', $validated['debt_ids'])
            ->get();

        if ($debts->count() !== count(array_unique($validated['debt_ids']))) {
            return back()->withErrors(['amount' => 'Daftar hutang tidak valid.']);
        }

        foreach ($debts as $debt) {
            if (! in_array($debt->status, ['unpaid', 'partial'], true)) {
                return back()->withErrors(['amount' => 'Salah satu faktur sudah lunas. Muat ulang halaman.']);
            }
        }

        $groupKeys = $debts->map->groupKey()->unique();
        if ($groupKeys->count() !== 1) {
            return back()->withErrors(['amount' => 'Pembayaran gabungan hanya untuk satu pelanggan (nama + nomor HP sama).']);
        }

        $debts = $debts->sortBy([
            fn (Debt $d) => $d->due_date ? $d->due_date->timestamp : PHP_INT_MAX,
            fn (Debt $d) => $d->created_at->timestamp,
        ])->values();

        $totalRemaining = (float) $debts->sum('remaining_amount');
        $paymentAmount = (float) $validated['amount'];

        if ($paymentAmount > $totalRemaining + 0.0001) {
            return back()->withErrors([
                'amount' => 'Jumlah melebihi total sisa hutang (Rp '.number_format($totalRemaining, 0, ',', '.').').',
            ]);
        }

        DB::beginTransaction();

        try {
            $left = $paymentAmount;
            $parts = [];

            foreach ($debts as $debt) {
                if ($left <= 0) {
                    break;
                }

                $remaining = (float) $debt->remaining_amount;
                if ($remaining <= 0) {
                    continue;
                }

                $apply = min($remaining, $left);
                $notePrefix = $validated['notes'] ? $validated['notes'].' — ' : '';
                $allocNote = $notePrefix.'Alokasi gabungan ke faktur '.($debt->sale->invoice_number ?? '#'.$debt->id);

                DebtPayment::create([
                    'debt_id' => $debt->id,
                    'amount' => $apply,
                    'payment_date' => $validated['payment_date'],
                    'payment_method' => $validated['payment_method'],
                    'notes' => $allocNote,
                    'user_id' => Auth::id(),
                ]);

                $newPaidAmount = (float) $debt->paid_amount + $apply;
                $newRemainingAmount = (float) $debt->remaining_amount - $apply;

                $debt->update([
                    'paid_amount' => $newPaidAmount,
                    'remaining_amount' => $newRemainingAmount,
                    'status' => $newRemainingAmount <= 0 ? 'paid' : ($newPaidAmount > 0 ? 'partial' : 'unpaid'),
                ]);

                if ($debt->sale) {
                    $debt->sale->update([
                        'debt_amount' => max(0, $newRemainingAmount),
                        'debt_status' => $newRemainingAmount <= 0 ? 'paid' : ($newPaidAmount > 0 ? 'partial' : 'unpaid'),
                    ]);
                }

                $parts[] = ($debt->sale->invoice_number ?? 'ID '.$debt->id).': Rp '.number_format($apply, 0, ',', '.');
                $left -= $apply;
            }

            DB::commit();

            $detail = implode('; ', $parts);
            $newGroupRemaining = max(0, $totalRemaining - $paymentAmount);

            return redirect()
                ->route('debts.index', array_filter([
                    'view' => 'grouped',
                    'customer' => $request->get('customer'),
                    'overdue' => $request->get('overdue'),
                ], fn ($v) => $v !== null && $v !== ''))
                ->with(
                    'success',
                    'Pembayaran tersimpan, sisa hutang sekarang Rp '.number_format($newGroupRemaining, 0, ',', '.').'. Dialokasikan: '.$detail,
                );
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Gagal mencatat pembayaran: '.$e->getMessage()]);
        }
    }

    /**
     * Show the form for creating a new debt.
     */
    public function create()
    {
        return view('debts.create');
    }

    /**
     * Display the specified debt.
     */
    public function show(Debt $debt)
    {
        $debt->load([
            'sale.saleItems.product',
            'payments.user',
            'user',
            'installmentPlans.installmentPayments',
            'activeInstallmentPlan.installmentPayments',
        ]);

        return view('debts.show', compact('debt'));
    }

    /**
     * Record a payment for a debt.
     */
    public function recordPayment(Request $request, Debt $debt)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,transfer,card',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            $paymentAmount = $validated['amount'];

            if ($paymentAmount > $debt->remaining_amount) {
                return back()->withErrors(['amount' => 'Jumlah pembayaran melebihi sisa hutang']);
            }

            // Create payment record
            DebtPayment::create([
                'debt_id' => $debt->id,
                'amount' => $paymentAmount,
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
                'user_id' => Auth::id(),
            ]);

            // Update debt
            $newPaidAmount = $debt->paid_amount + $paymentAmount;
            $newRemainingAmount = $debt->remaining_amount - $paymentAmount;

            $debt->update([
                'paid_amount' => $newPaidAmount,
                'remaining_amount' => $newRemainingAmount,
                'status' => $newRemainingAmount <= 0 ? 'paid' : ($newPaidAmount > 0 ? 'partial' : 'unpaid'),
            ]);

            // Update sale debt status
            if ($debt->sale) {
                $debt->sale->update([
                    'debt_amount' => $newRemainingAmount,
                    'debt_status' => $newRemainingAmount <= 0 ? 'paid' : ($newPaidAmount > 0 ? 'partial' : 'unpaid'),
                ]);
            }

            DB::commit();

            return redirect()->route('debts.show', $debt)
                ->with(
                    'success',
                    'Pembayaran tersimpan, sisa hutang sekarang Rp '.number_format(max(0, $newRemainingAmount), 0, ',', '.').'.',
                );
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Gagal mencatat pembayaran: '.$e->getMessage()]);
        }
    }

    /**
     * Update a payment and recalculate debt.
     */
    public function updatePayment(Request $request, $paymentId)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,transfer,card',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            $payment = DebtPayment::findOrFail($paymentId);
            $debt = $payment->debt;

            // Calculate difference
            $oldAmount = $payment->amount;
            $newAmount = $validated['amount'];
            $difference = $newAmount - $oldAmount;

            // Check if new amount exceeds remaining debt (if increasing)
            // When editing, we need to account for the current payment amount
            // So the max allowed is: remaining_amount + old_amount (to allow reducing payment)
            $maxAllowed = $debt->remaining_amount + $oldAmount;
            if ($newAmount > $maxAllowed) {
                return back()->withErrors(['amount' => 'Jumlah pembayaran melebihi batas maksimal (Rp '.number_format($maxAllowed, 0, ',', '.').')']);
            }

            // Update payment
            $payment->update([
                'amount' => $newAmount,
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
            ]);

            // Recalculate debt amounts
            $newPaidAmount = $debt->paid_amount + $difference;
            $newRemainingAmount = $debt->remaining_amount - $difference;

            $debt->update([
                'paid_amount' => max(0, $newPaidAmount),
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
                ->with('success', 'Pembayaran berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Gagal memperbarui pembayaran: '.$e->getMessage()]);
        }
    }

    /**
     * Delete a payment and recalculate debt.
     */
    public function deletePayment($paymentId)
    {
        DB::beginTransaction();

        try {
            $payment = DebtPayment::findOrFail($paymentId);
            $debt = $payment->debt;

            // Recalculate debt amounts
            $newPaidAmount = $debt->paid_amount - $payment->amount;
            $newRemainingAmount = $debt->remaining_amount + $payment->amount;

            // Delete payment
            $payment->delete();

            // Update debt
            $debt->update([
                'paid_amount' => max(0, $newPaidAmount),
                'remaining_amount' => $newRemainingAmount,
                'status' => $newRemainingAmount <= 0 ? 'paid' : ($newPaidAmount > 0 ? 'partial' : 'unpaid'),
            ]);

            // Update sale debt status
            if ($debt->sale) {
                $debt->sale->update([
                    'debt_amount' => $newRemainingAmount,
                    'debt_status' => $newRemainingAmount <= 0 ? 'paid' : ($newPaidAmount > 0 ? 'partial' : 'unpaid'),
                ]);
            }

            DB::commit();

            return redirect()->route('debts.show', $debt)
                ->with('success', 'Pembayaran berhasil dihapus dan hutang telah diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Gagal menghapus pembayaran: '.$e->getMessage()]);
        }
    }

    /**
     * Get debts API endpoint.
     */
    public function apiIndex(Request $request)
    {
        $query = Debt::with(['sale', 'user'])->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('customer')) {
            $query->where('customer_name', 'like', '%'.$request->customer.'%');
        }

        $debts = $query->paginate(15);

        return response()->json($debts);
    }
}
