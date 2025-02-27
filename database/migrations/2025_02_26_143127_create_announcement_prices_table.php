<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('announcement_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained('announcements')->onDelete('cascade');
            $table->decimal('price', 10, 2);
            $table->string('currency', 10);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('announcement_prices');
    }
};
