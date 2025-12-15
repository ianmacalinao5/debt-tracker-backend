<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
	protected $fillable = [
		'debtor_id',
		'type',
		'amount',
		'balance_before',
		'balance_after',
	];

	public function debtor()
	{
		return $this->belongsTo(Debtor::class);
	}
}
