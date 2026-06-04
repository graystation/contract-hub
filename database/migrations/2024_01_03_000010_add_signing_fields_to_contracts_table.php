<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Change signed_at from date to timestamp so we can store exact signing time.
        // MySQL requires MODIFY; SQLite is dynamically typed so no ALTER needed.
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE contracts MODIFY signed_at TIMESTAMP NULL DEFAULT NULL');
        }

        Schema::table('contracts', function (Blueprint $table) {
            $table->string('sign_token', 64)->nullable()->unique()->after('notes');
            $table->timestamp('sign_token_expires_at')->nullable()->after('sign_token');
            $table->string('signer_name')->nullable()->after('sign_token_expires_at');
            $table->string('signer_email')->nullable()->after('signer_name');
            $table->string('signer_ip_address', 45)->nullable()->after('signer_email');
            $table->text('signer_user_agent')->nullable()->after('signer_ip_address');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn([
                'sign_token',
                'sign_token_expires_at',
                'signer_name',
                'signer_email',
                'signer_ip_address',
                'signer_user_agent',
            ]);
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE contracts MODIFY signed_at DATE NULL DEFAULT NULL');
        }
    }
};
