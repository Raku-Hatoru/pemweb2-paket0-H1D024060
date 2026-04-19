<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            throw new RuntimeException('The sync migration expects an imported database that already contains the users table.');
        }

        $this->createPasswordResetTokensTable();
        $this->createSessionsTable();
        $this->createCacheTables();
        $this->createJobTables();
        $this->recordImportedMigrations();
    }

    /**
     * This sync migration should not remove framework tables from imported databases.
     */
    public function down(): void {}

    private function createPasswordResetTokensTable(): void
    {
        if (Schema::hasTable('password_reset_tokens')) {
            return;
        }

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    private function createSessionsTable(): void
    {
        if (Schema::hasTable('sessions')) {
            return;
        }

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    private function createCacheTables(): void
    {
        if (! Schema::hasTable('cache')) {
            Schema::create('cache', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->mediumText('value');
                $table->bigInteger('expiration')->index();
            });
        }

        if (! Schema::hasTable('cache_locks')) {
            Schema::create('cache_locks', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->string('owner');
                $table->bigInteger('expiration')->index();
            });
        }
    }

    private function createJobTables(): void
    {
        if (! Schema::hasTable('jobs')) {
            Schema::create('jobs', function (Blueprint $table) {
                $table->id();
                $table->string('queue')->index();
                $table->longText('payload');
                $table->unsignedTinyInteger('attempts');
                $table->unsignedInteger('reserved_at')->nullable();
                $table->unsignedInteger('available_at');
                $table->unsignedInteger('created_at');
            });
        }

        if (! Schema::hasTable('job_batches')) {
            Schema::create('job_batches', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->string('name');
                $table->integer('total_jobs');
                $table->integer('pending_jobs');
                $table->integer('failed_jobs');
                $table->longText('failed_job_ids');
                $table->mediumText('options')->nullable();
                $table->integer('cancelled_at')->nullable();
                $table->integer('created_at');
                $table->integer('finished_at')->nullable();
            });
        }

        if (! Schema::hasTable('failed_jobs')) {
            Schema::create('failed_jobs', function (Blueprint $table) {
                $table->id();
                $table->string('uuid')->unique();
                $table->text('connection');
                $table->text('queue');
                $table->longText('payload');
                $table->longText('exception');
                $table->timestamp('failed_at')->useCurrent();
            });
        }
    }

    private function recordImportedMigrations(): void
    {
        $completedMigrations = [];

        if (
            Schema::hasTable('users')
            && Schema::hasTable('password_reset_tokens')
            && Schema::hasTable('sessions')
        ) {
            $completedMigrations[] = '0001_01_01_000000_create_users_table';
        }

        if (Schema::hasTable('cache') && Schema::hasTable('cache_locks')) {
            $completedMigrations[] = '0001_01_01_000001_create_cache_table';
        }

        if (
            Schema::hasTable('jobs')
            && Schema::hasTable('job_batches')
            && Schema::hasTable('failed_jobs')
        ) {
            $completedMigrations[] = '0001_01_01_000002_create_jobs_table';
        }

        if (Schema::hasColumn('users', 'role')) {
            $completedMigrations[] = '2026_04_19_120323_add_role_to_users_table';
        }

        foreach ($completedMigrations as $migration) {
            $alreadyRecorded = DB::table('migrations')
                ->where('migration', $migration)
                ->exists();

            if (! $alreadyRecorded) {
                DB::table('migrations')->insert([
                    'migration' => $migration,
                    'batch' => 1,
                ]);
            }
        }
    }
};
