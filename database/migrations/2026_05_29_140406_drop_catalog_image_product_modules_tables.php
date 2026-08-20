<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('images');
        Schema::dropIfExists('esb_products');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
