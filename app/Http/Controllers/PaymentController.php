<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Display a listing of the payments.
     */
    public function index(Request $request)
    {
        $query = Payment::with(['sale', 'user']);

        // Filter by sale
        if ($request->filled('sale_id')) {
            $query->where('sale_id', $request->sale_id);
        }

        // Filter by method
        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        // Filter by date range
        if ($request->filled('date_range')) {
            $dates = explode(' - ', $request->date_range);
            if (count($dates) == 2) {
                $startDate = Carbon::createFromFormat('m/d/Y', $dates[0])->startOfDay();
                $endDate = Carbon::createFromFormat('m/d/Y', $dates[1])->endOfDay();
                $query->whereBetween('date', [$startDate, $endDate]);
            }
        }

        $payments = $query->latest()->paginate(15);
        return view('payments.index', compact('payments'));
    }

    /**
     * Show the form for creating a new payment.
     */
    public function create(Request $request)
    {
        $sale = null;
        if ($request->has('sale_id')) {
            $sale = Sale::with('customer')->findOrFail($request->sale_id);
        }

        return view('payments.create', compact('sale'));
    }

    /**
     * Store a newly created payment in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|string',
            'reference' => 'nullable|string',
            'date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $sale = Sale::findOrFail($request->sale_id);
        
        // Validate that the amount doesn't exceed due
        if ($request->amount > $sale->due_amount) {
            return back()->withInput()->withErrors([
                'amount' => 'Payment amount cannot exceed the due amount (' . currency_format($sale->due_amount) . ')'
            ]);
        }

        try {
            DB::beginTransaction();

            // Create payment
            $payment = Payment::create([
                'sale_id' => $request->sale_id,
                'amount' => $request->amount,
                'method' => $request->method,
                'reference' => $request->reference,
                'date' => $request->date,
                'user_id' => auth()->id(),
                'notes' => $request->notes,
            ]);

            // Process the payment
            $payment->process();
            
            DB::commit();

            return redirect()->route('sales.show', $sale)
                ->with('success', 'Payment added successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment creation failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Error adding payment. ' . $e->getMessage());
        }
    }

    /**
     * Display the specified payment.
     */
    public function show(Payment $payment)
    {
        $payment->load(['sale.customer', 'user']);
        return view('payments.show', compact('payment'));
    }

    /**
     * Show the form for editing the specified payment.
     */
    public function edit(Payment $payment)
    {
        $payment->load('sale.customer');
        return view('payments.edit', compact('payment'));
    }

    /**
     * Update the specified payment in storage.
     */
    public function update(Request $request, Payment $payment)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|string',
            'reference' => 'nullable|string',
            'date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $sale = $payment->sale;
        $oldAmount = $payment->amount;
        $newAmount = $request->amount;
        
        // Calculate the new due amount if we update
        $potentialDue = $sale->due_amount + $oldAmount - $newAmount;
        
        // Validate that the amount doesn't make the sale overpaid
        if ($potentialDue < 0) {
            return back()->withInput()->withErrors([
                'amount' => 'Payment amount would exceed the sale total. Max allowed: ' . 
                    currency_format($sale->due_amount + $oldAmount)
            ]);
        }

        try {
            DB::beginTransaction();

            // Revert the old payment
            $sale->paid_amount -= $oldAmount;
            $sale->save();
            
            // Update payment
            $payment->update([
                'amount' => $newAmount,
                'method' => $request->method,
                'reference' => $request->reference,
                'date' => $request->date,
                'notes' => $request->notes,
            ]);

            // Apply the new payment
            $sale->paid_amount += $newAmount;
            $sale->save();
            
            // Update payment status
            $sale->updatePaymentStatus();
            
            DB::commit();

            return redirect()->route('sales.show', $sale)
                ->with('success', 'Payment updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment update failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Error updating payment. ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified payment from storage.
     */
    public function destroy(Payment $payment)
    {
        $sale = $payment->sale;

        try {
            DB::beginTransaction();

            // Update sale paid amount
            $sale->paid_amount -= $payment->amount;
            $sale->save();
            
            // Update payment status
            $sale->updatePaymentStatus();
            
            // Delete payment
            $payment->delete();

            DB::commit();

            return redirect()->route('sales.show', $sale)
                ->with('success', 'Payment deleted successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment deletion failed: ' . $e->getMessage());
            return back()->with('error', 'Error deleting payment. ' . $e->getMessage());
        }
    }
} 