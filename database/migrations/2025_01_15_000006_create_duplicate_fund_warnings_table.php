<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('duplicate_fund_warnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fund_id')->constrained();
            $table->foreignId('duplicate_fund_id')->constrained('funds');
            $table->string('matched_name');
            $table->foreignId('fund_manager_id')->constrained();
            $table->boolean('is_resolved')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duplicate_fund_warnings');
    }
};
