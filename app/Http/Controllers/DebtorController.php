<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Debtor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

		$debtor->increment('current_balance', $data['amount']);

		$debtor->update(['status' => 'outstanding']);

		return response()->json($debtor->fresh());
	}

	public function payAmount(Request $request, Debtor $debtor)
	{
		$this->authorizeDebtor($debtor);

		$data = $request->validate([
			'amount' => ['required', 'numeric', 'min:1'],
		]);

		$debtor->decrement('current_balance', $data['amount']);

		if ($debtor->current_balance <= 0) {
			$debtor->update([
				'current_balance' => 0,
				'status' => 'cleared',
			]);
		}

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
