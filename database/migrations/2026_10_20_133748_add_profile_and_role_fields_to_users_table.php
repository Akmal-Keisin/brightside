<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('Member')->after('password')->index();
            $table->string('avatar_path')->nullable()->after('role');
            $table->string('headline', 120)->nullable()->after('avatar_path');
            $table->text('bio')->nullable()->after('headline');
            $table->timestamp('terms_accepted_at')->nullable()->after('bio');
            $table->string('terms_version', 20)->nullable()->after('terms_accepted_at');
            $table->timestamp('last_login_at')->nullable()->after('terms_version');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'avatar_path', 'headline', 'bio', 'terms_accepted_at', 'terms_version', 'last_login_at']);
        });
    }
};
