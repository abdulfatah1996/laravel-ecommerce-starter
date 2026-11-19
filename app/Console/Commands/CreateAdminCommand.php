<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdminCommand extends Command
{
    protected $signature = 'store:create-admin {--name= : اسم المدير} {--email= : البريد الإلكتروني}';

    protected $description = 'ينشئ حساب مدير نظام (بديل عن صفحة /install، مفيد عبر SSH). كلمة المرور تُدخل بشكل مخفي.';

    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('الاسم');
        $email = $this->option('email') ?: $this->ask('البريد الإلكتروني');
        $password = $this->secret('كلمة المرور (10 أحرف على الأقل، حروف وأرقام)');
        $confirmation = $this->secret('تأكيد كلمة المرور');

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password, 'password_confirmation' => $confirmation],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'confirmed', Password::min(10)->letters()->numbers()],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => UserRole::Admin,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->info("تم إنشاء المدير {$email}.");

        return self::SUCCESS;
    }
}
