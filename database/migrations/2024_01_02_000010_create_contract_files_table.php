<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type', 20)->default('pdf');
            $table->string('file_hash', 64)->nullable(); // SHA256 hex is 64 chars
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_files');
    }
};
