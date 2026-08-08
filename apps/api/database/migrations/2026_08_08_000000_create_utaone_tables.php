<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Existing FastAPI installations already have this compatible schema.
        // Returning here lets Laravel adopt that database without data loss.
        if (Schema::hasTable('songs')) {
            return;
        }
        DB::statement('PRAGMA journal_mode=WAL');
        DB::statement('PRAGMA foreign_keys=ON');
        Schema::create('songs', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('artist');
            $t->string('status')->default('draft');
            $t->unsignedTinyInteger('difficulty')->default(1);
            $t->timestamps();
        });
        Schema::create('song_assets', function (Blueprint $t) {
            $t->id();
            $t->foreignId('song_id')->constrained()->cascadeOnDelete();
            $t->string('kind');
            $t->string('original_name');
            $t->text('storage_path');
            $t->string('mime_type');
            $t->string('sha256', 64);
            $t->text('metadata_json')->default('{}');
            $t->timestamp('created_at')->useCurrent();
            $t->unique(['song_id', 'kind', 'sha256']);
        });
        Schema::create('processing_jobs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('song_id')->constrained()->cascadeOnDelete();
            $t->unsignedBigInteger('recording_id')->nullable();
            $t->string('job_type')->default('karaoke_build');
            $t->string('status')->default('queued');
            $t->unsignedTinyInteger('progress')->default(0);
            $t->text('error_message')->nullable();
            $t->text('result_json')->default('{}');
            $t->timestamp('created_at')->useCurrent();
            $t->timestamp('started_at')->nullable();
            $t->timestamp('finished_at')->nullable();
            $t->timestamp('updated_at')->useCurrent();
            $t->index(['status', 'id']);
        });
        Schema::create('lyric_segments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('song_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('version')->default(1);
            $t->unsignedInteger('position');
            $t->text('text');
            $t->text('reading')->nullable();
            $t->unsignedBigInteger('start_ms');
            $t->unsignedBigInteger('end_ms');
            $t->double('confidence')->default(0);
            $t->unique(['song_id', 'version', 'position']);
        });
        Schema::create('karaoke_releases', function (Blueprint $t) {
            $t->id();
            $t->foreignId('song_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('version');
            $t->unsignedBigInteger('stream_asset_id')->nullable();
            $t->text('timeline_json');
            $t->timestamp('published_at')->nullable();
            $t->timestamp('created_at')->useCurrent();
            $t->unique(['song_id', 'version']);
        });
        Schema::create('recordings', function (Blueprint $t) {
            $t->id();
            $t->string('app_user_id');
            $t->foreignId('song_id')->constrained();
            $t->text('storage_path');
            $t->string('status')->default('uploaded');
            $t->double('score')->nullable();
            $t->text('score_detail_json')->default('{}');
            $t->timestamp('created_at')->useCurrent();
            $t->index(['app_user_id', 'created_at']);
        });
        Schema::create('subscriptions', function (Blueprint $t) {
            $t->string('app_user_id')->primary();
            $t->string('entitlement_id');
            $t->boolean('is_active')->default(false);
            $t->string('product_id')->nullable();
            $t->string('expires_at')->nullable();
            $t->string('environment')->nullable();
            $t->string('last_event_id')->nullable();
            $t->timestamp('updated_at')->useCurrent();
        });
        Schema::create('webhook_events', function (Blueprint $t) {
            $t->string('id')->primary();
            $t->string('event_type');
            $t->text('payload_json');
            $t->timestamp('received_at')->useCurrent();
        });
    }

    public function down(): void
    {
        foreach (['webhook_events', 'subscriptions', 'recordings', 'karaoke_releases', 'lyric_segments', 'processing_jobs', 'song_assets', 'songs'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
