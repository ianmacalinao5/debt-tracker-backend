<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('transactions', function (Blueprint $table) {
			$table->id();

			$table->foreignId('debtor_id')
				->constrained()
				->cascadeOnDelete();

			$table->enum('type', ['add', 'payment']);

			$table->decimal('amount', 12, 2);

			$table->decimal('balance_before', 12, 2)->nullable();
			$table->decimal('balance_after', 12, 2)->nullable();

			$table->timestamps();

			$table->index(['debtor_id', 'type']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('transactions');
	}
};
