<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Debtor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class DebtorController extends Controller
{
	public function index(Request $request)
	{
		$status = $request->query('status', 'all');

		$debtors = Debtor::where('user_id', $request->user()->id)
			->when(
				$status !== 'all',
				fn($q) =>
				$q->where('status', $status)
			)
			->orderByDesc('updated_at')
			->get();

		return response()->json($debtors);
	}

	public function transactions(Debtor $debtor)
	{
		$this->authorizeDebtor($debtor);

		return response()->json(
			$debtor->transactions()
				->latest()
				->get()
		);
	}


	public function store(Request $request)
	{
		$data = $request->validate([
			'name' => ['required', 'string', 'max:255'],
			'current_balance' => ['required', 'numeric', 'min:0'],
		]);

		$debtor = $request->user()->debtors()->create([
			'name' => $data['name'],
			'current_balance' => $data['current_balance'],
			'status' => $data['current_balance'] > 0 ? 'outstanding' : 'cleared',
		]);

		return response()->json($debtor, 201);
	}

	public function show(Debtor $debtor)
	{
		$this->authorizeDebtor($debtor);

		return response()->json($debtor);
	}

	public function update(Request $request, Debtor $debtor)
	{
		$this->authorizeDebtor($debtor);

		$data = $request->validate([
			'name' => ['required', 'string', 'max:255'],
			'status' => ['required', Rule::in(['outstanding', 'cleared'])],
		]);

		$debtor->update($data);

		return response()->json($debtor);
	}

	public function addAmount(Request $request, Debtor $debtor)
	{
		$this->authorizeDebtor($debtor);

		$data = $request->validate([
			'amount' => ['required', 'numeric', 'min:1'],
		]);

		DB::transaction(function () use ($debtor, $data) {
			$before = $debtor->current_balance;

			$debtor->increment('current_balance', $data['amount']);
			$debtor->update(['status' => 'outstanding']);

			$debtor->transactions()->create([
				'type' => 'add',
				'amount' => $data['amount'],
				'balance_before' => $before,
				'balance_after' => $before + $data['amount'],
			]);
		});

		return response()->json($debtor->fresh());
	}

	public function payAmount(Request $request, Debtor $debtor)
	{
		$this->authorizeDebtor($debtor);

		$data = $request->validate([
			'amount' => ['required', 'numeric', 'min:1'],
		]);

		DB::transaction(function () use ($debtor, $data) {
			$before = $debtor->current_balance;

			$newBalance = max(0, $before - $data['amount']);

			$debtor->update([
				'current_balance' => $newBalance,
				'status' => $newBalance === 0 ? 'cleared' : 'outstanding',
			]);

			$debtor->transactions()->create([
				'type' => 'payment',
				'amount' => $data['amount'],
				'balance_before' => $before,
				'balance_after' => $newBalance,
			]);
		});

		return response()->json($debtor->fresh());
	}

	public function destroy(Debtor $debtor)
	{
		$this->authorizeDebtor($debtor);

		$debtor->delete();

		return response()->json(['message' => 'Debtor deleted']);
	}

	private function authorizeDebtor(Debtor $debtor): void
	{
		abort_if($debtor->user_id !== auth()->id(), 403);
	}
}
