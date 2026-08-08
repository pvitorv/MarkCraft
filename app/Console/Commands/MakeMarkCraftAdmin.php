<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeMarkCraftAdmin extends Command
{
    protected $signature = 'markcraft:make-admin {email : E-mail do usuário}';

    protected $description = 'Marca um usuário existente como admin do CMS MarkCraft';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));
        $user = User::query()->where('email', $email)->first();
        if (! $user) {
            $this->error("Usuário não encontrado: {$email}");

            return self::FAILURE;
        }

        $user->forceFill(['is_admin' => true])->save();
        $this->info("Admin OK: {$user->email} (id {$user->id})");

        return self::SUCCESS;
    }
}
