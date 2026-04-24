<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_winners', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 16)->index();
            $table->string('name');
            $table->foreignId('prize_id')->constrained('prizes')->cascadeOnDelete();
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('is_used')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_winners');
    }
};
