<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('property_management')->create('foreign_exchange_rates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('from_currency');
            $table->string('to_currency');
            $table->date('date');
            $table->decimal('exchange_rate', 10, 4)->default(1);
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('property_management')->dropIfExists('foreign_exchange_rates');
    }
};
