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
        Schema::create('labeling_items', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('company_id');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('update_at')->nullable();
            $table->string('building_name')->nullable();
            $table->string('c1')->nullable();
            $table->string('sub_2')->nullable();
            $table->string('c2')->nullable();
            $table->string('location')->nullable();
            $table->string('c3')->nullable();
            $table->string('sub_3')->nullable();
            $table->string('c4')->nullable();
            $table->string('classification')->nullable();
            $table->string('c5')->nullable();
            $table->string('sub_22')->nullable();
            $table->string('c6')->nullable();
            $table->string('sub_32')->nullable();
            $table->string('c7')->nullable();
            $table->string('qty')->nullable();
            $table->string('code')->nullable();
            $table->string('item')->nullable();
            $table->string('ahmed_salah')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('floor_number')->nullable();
            $table->string('furniture')->nullable();
            $table->string('muneera_experience_center')->nullable();
            $table->string('qr_code')->nullable();
            $table->string('depratment')->nullable();
            $table->string('abc')->nullable();
            $table->string('sn')->nullable();
            $table->string('cod_location')->nullable();
            $table->string('sub-location')->nullable();
            $table->string('codsub-location')->nullable();
            $table->string('items')->nullable();
            $table->string('items_summary')->nullable();
            $table->string('items_code')->nullable();
            $table->string('qty_cod')->nullable();
            $table->string('supplier')->nullable();
            $table->string('supplier_cod')->nullable();
            $table->string('dimension')->nullable();
            $table->string('condition-stored')->nullable();
            $table->string('stored')->nullable();
            $table->string('condition_stored_cod')->nullable();
            $table->string('name_code')->nullable();
            $table->string('part_number')->nullable();
            $table->string('location_cod_')->nullable();
            $table->string('sub-location_cod')->nullable();
            $table->string('item_summary')->nullable();
            $table->string('condition-furnished')->nullable();
            $table->string('furnished')->nullable();
            $table->string('name')->nullable();
            $table->string('dimension_')->nullable();
            $table->string('details')->nullable();
            $table->string('code_and_part_number')->nullable();
            $table->string('name_and_part_number')->nullable();
            $table->string('word_')->nullable();
            $table->string('word')->nullable();
            $table->string('number')->nullable();
            $table->string('print')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('labeling_items');
    }
};
