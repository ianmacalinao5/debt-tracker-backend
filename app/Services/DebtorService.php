<?php

namespace App\Services;

use App\Models\Debtor;
use Illuminate\Support\Facades\DB;

class DebtorService
{
	public function createDebtor(int $userId, array $data): Debtor
	{
		return Debtor::create([
			'user_id' => $userId,
			'name' => $data['name'],
			'current_balance' => $data['current_balance'],
			'status' => $data['current_balance'] > 0 ? 'outstanding' : 'cleared',
		]);
	}

	public function updateDebtor(Debtor $debtor, array $data): Debtor
	{
		$debtor->update($data);
		return $debtor->fresh();
	}

	public function addAmount(Debtor $debtor, float $amount): Debtor
	{
		DB::transaction(function () use ($debtor, $amount) {
			$balanceBefore = $debtor->current_balance;

			$debtor->increment('current_balance', $amount);
			$debtor->update(['status' => 'outstanding']);

			$debtor->transactions()->create([
				'type' => 'add',
				'amount' => $amount,
				'balance_before' => $balanceBefore,
				'balance_after' => $balanceBefore + $amount,
			]);
		});

		return $debtor->fresh();
	}

	public function payAmount(Debtor $debtor, float $amount): Debtor
	{
		DB::transaction(function () use ($debtor, $amount) {
			$balanceBefore = $debtor->current_balance;
			$newBalance = max(0, $balanceBefore - $amount);

			$debtor->update([
				'current_balance' => $newBalance,
				'status' => $newBalance === 0.0 ? 'cleared' : 'outstanding',
			]);

			$debtor->transactions()->create([
				'type' => 'payment',
				'amount' => $amount,
				'balance_before' => $balanceBefore,
				'balance_after' => $newBalance,
			]);
		});

		return $debtor->fresh();
	}

	public function deleteDebtor(Debtor $debtor): void
	{
		$debtor->delete();
	}

	public function getDebtorsByStatus(int $userId, string $status)
	{
		return Debtor::where('user_id', $userId)
			->when($status !== 'all', fn($q) => $q->where('status', $status))
			->orderByDesc('updated_at')
			->get();
	}

	public function getTransactions(Debtor $debtor)
	{
		return $debtor->transactions()
			->latest()
			->get();
	}
}
