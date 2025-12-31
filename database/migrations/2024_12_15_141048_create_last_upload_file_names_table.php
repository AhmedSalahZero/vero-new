<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLastUploadFileNamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('last_upload_file_names', function (Blueprint $table) {
            $table->id();
			$table->string('name');
			$table->string('model_name');
			$table->string('status')->nullable()->comment('هل الملف دا اترفع فعلا وبالتالي هظهره ولا هو لسه بيترفع حاليا');
			$table->unsignedBigInteger('company_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('last_upload_file_names');
    }
}
