<?php

namespace App\Policies;

use App\Models\Debtor;
use App\Models\User;

class DebtorPolicy
{
	public function viewAny(User $user): bool
	{
		return true;
	}

	public function view(User $user, Debtor $debtor): bool
	{
		return $user->id === $debtor->user_id;
	}

	public function create(User $user): bool
	{
		return true;
	}

	public function update(User $user, Debtor $debtor): bool
	{
		return $user->id === $debtor->user_id;
	}

	public function delete(User $user, Debtor $debtor): bool
	{
		return $user->id === $debtor->user_id;
	}
}
