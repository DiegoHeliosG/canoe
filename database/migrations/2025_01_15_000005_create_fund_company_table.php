<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_fund', function (Blueprint $table) {
            $table->foreignId('fund_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['fund_id', 'company_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_fund');
    }
};
