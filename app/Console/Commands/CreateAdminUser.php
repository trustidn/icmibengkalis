<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

use function Laravel\Prompts\password as promptPassword;
use function Laravel\Prompts\text;

/**
 * Membuat (atau mempromosikan) akun admin dari terminal — dipakai setelah instalasi
 * awal di server, saat belum ada akun apa pun untuk masuk dan mengonfigurasi situs.
 * Idempoten: email yang sudah ada tidak diduplikasi, hanya ditambahi peran.
 */
class CreateAdminUser extends Command
{
    protected $signature = 'icmi:admin
        {--name= : Nama lengkap}
        {--email= : Email untuk login}
        {--password= : Kata sandi (hindari di server bersama — kosongkan agar ditanya tersembunyi)}
        {--role=super-admin : Peran yang diberikan}';

    protected $description = 'Buat akun admin baru atau berikan peran admin ke akun yang sudah ada';

    public function handle(): int
    {
        $roles = Role::pluck('name')->all();

        if ($roles === []) {
            $this->error('Belum ada peran di database. Jalankan dulu: php artisan db:seed --class=RolePermissionSeeder --force');

            return self::FAILURE;
        }

        $role = $this->option('role');

        if (! in_array($role, $roles, true)) {
            $this->error("Peran '{$role}' tidak dikenal. Pilihan: ".implode(', ', $roles));

            return self::FAILURE;
        }

        // Prompt hanya bila terminal benar-benar interaktif; jika tidak, minta opsi
        // eksplisit agar tidak melempar exception mentah (mis. dipanggil dari skrip).
        $interactive = stream_isatty(STDIN);

        $email = $this->option('email') ?: ($interactive ? text('Email login', required: true) : null);

        if (! $email) {
            $this->error('Sesi non-interaktif: sebutkan --email (dan --name, --password) atau jalankan dari terminal.');

            return self::FAILURE;
        }

        $existing = User::where('email', $email)->first();

        if ($existing) {
            $existing->assignRole($role);
            $this->info("Akun {$email} sudah ada — peran '{$role}' ditambahkan.");
            $this->line('Peran saat ini: '.$existing->fresh()->getRoleNames()->implode(', '));

            return self::SUCCESS;
        }

        $name = $this->option('name') ?: ($interactive ? text('Nama lengkap', required: true) : null);
        $password = $this->option('password') ?: ($interactive ? promptPassword('Kata sandi (min. 8 karakter)', required: true) : null);

        if (! $name || ! $password) {
            $this->error('Sesi non-interaktif: --name dan --password wajib disebutkan untuk membuat akun baru.');

            return self::FAILURE;
        }

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8'],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'email_verified_at' => now(),
        ]);

        $user->assignRole($role);

        $this->info("Akun admin dibuat: {$email} (peran: {$role})");
        $this->line('Masuk lewat /login, lalu buka Konfigurasi Web untuk mengatur identitas situs.');

        return self::SUCCESS;
    }
}
