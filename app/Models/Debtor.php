<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Debtor extends Model
{
	protected $fillable = [
		'user_id',
		'name',
		'current_balance',
		'status',
	];

	protected $casts = [
		'current_balance' => 'decimal:2',
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function transactions()
	{
		return $this->hasMany(Transaction::class);
	}
}
