<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('name');
            $table->string('password');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('admin', function (Blueprint $table) {
            $table->integer('id_admin')->autoIncrement()->primary();
            $table->string('username')->unique();
            $table->foreign('username')
                ->references('username')
                ->on('users');
            $table->string('no_hp', 20);
            $table->enum('shift', ['Pagi', 'Siang', 'Malam']);
        });

        Schema::create('anggota', function (Blueprint $table) {
            $table->integer('id_anggota')->autoIncrement()->primary();
            $table->string('username')->unique();
            $table->foreign('username')
                ->references('username')
                ->on('users');
            $table->string('no_hp', 20);
            $table->string('alamat');
            $table->timestamp('tanggal_join')->nullable();
        });

        Schema::create('buku', function (Blueprint $table) {
            $table->integer('id_buku')->autoIncrement()->primary();
            $table->string('judul');
            $table->string('genre');
            $table->string('penulis');
            $table->string('penerbit');
            $table->year('tahun_terbit');
            $table->integer('stok');
        });

        Schema::create('riwayat',function (Blueprint $table) {
            $table->integer('id_riwayat')->autoIncrement()->primary();
            $table->integer('id_buku');
            $table->foreign('id_buku')
                ->references('id_buku')
                ->on('buku');
            $table->integer('id_anggota_pinjam');
            $table->foreign('id_anggota_pinjam')
                ->references('id_anggota')
                ->on('anggota');
            $table->timestamp('tanggal_pinjam')->nullable();
            $table->timestamp('tanggal_kembali')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
e('tanggal_lahir');
            $table->string('alamat');