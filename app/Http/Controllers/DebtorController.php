<?php

namespace App\Http\Controllers;

use App\Http\Requests\DebtorAmountRequest;
use App\Http\Requests\StoreDebtorRequest;
use App\Http\Requests\UpdateDebtorRequest;
use App\Models\Debtor;
use App\Services\DebtorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DebtorController extends Controller
{
	public function __construct(
		private DebtorService $debtorService
	) {}

	public function index(Request $request): JsonResponse
	{
		$status = $request->query('status', 'all');
		$debtors = $this->debtorService->getDebtorsByStatus(
			$request->user()->id,
			$status
		);

		return response()->json($debtors);
	}

	public function transactions(Debtor $debtor): JsonResponse
	{
		$this->authorize('view', $debtor);

		$transactions = $this->debtorService->getTransactions($debtor);

		return response()->json($transactions);
	}

	public function store(StoreDebtorRequest $request): JsonResponse
	{
		$debtor = $this->debtorService->createDebtor(
			$request->user()->id,
			$request->validated()
		);

		return response()->json($debtor, 201);
	}

	public function show(Debtor $debtor): JsonResponse
	{
		$this->authorize('view', $debtor);

		return response()->json($debtor);
	}

	public function update(UpdateDebtorRequest $request, Debtor $debtor): JsonResponse
	{
		$debtor = $this->debtorService->updateDebtor(
			$debtor,
			$request->validated()
		);

		return response()->json($debtor);
	}

	public function addAmount(DebtorAmountRequest $request, Debtor $debtor): JsonResponse
	{
		$debtor = $this->debtorService->addAmount(
			$debtor,
			$request->validated('amount')
		);

		return response()->json($debtor);
	}

	public function payAmount(DebtorAmountRequest $request, Debtor $debtor): JsonResponse
	{
		$debtor = $this->debtorService->payAmount(
			$debtor,
			$request->validated('amount')
		);

		return response()->json($debtor);
	}

	public function destroy(Debtor $debtor): JsonResponse
	{
		$this->authorize('delete', $debtor);

		$this->debtorService->deleteDebtor($debtor);

		return response()->json(['message' => 'Debtor deleted']);
	}
}
