<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Hash Driver
    |--------------------------------------------------------------------------
    |
    | MarkCraft usa Argon2id (recomendado OWASP) para senhas de usuários.
    | Drivers suportados: "bcrypt", "argon", "argon2id"
    |
    */

    'driver' => env('HASH_DRIVER', 'argon2id'),

    /*
    |--------------------------------------------------------------------------
    | Bcrypt Options
    |--------------------------------------------------------------------------
    */

    'bcrypt' => [
        'rounds' => env('BCRYPT_ROUNDS', 12),
        // false: permite verificar hashes de outro algoritmo durante migração
        'verify' => env('HASH_VERIFY', false),
        'limit' => env('BCRYPT_LIMIT', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Argon Options (argon / argon2id)
    |--------------------------------------------------------------------------
    |
    | memory: KiB (65536 = 64 MiB)
    | threads: paralelismo
    | time: iterações de custo
    |
    | verify=false é obrigatório enquanto existirem senhas bcrypt no banco:
    | com true, o login explode com "does not use the Argon2id algorithm".
    | Com rehash_on_login, o hash é atualizado para Argon2id no próximo login ok.
    |
    */

    'argon' => [
        'memory' => env('ARGON_MEMORY', 65536),
        'threads' => env('ARGON_THREADS', 1),
        'time' => env('ARGON_TIME', 4),
        'verify' => env('HASH_VERIFY', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rehash On Login
    |--------------------------------------------------------------------------
    |
    | Se true, hashes antigos (ex.: bcrypt) são atualizados para o driver
    | atual no próximo login bem-sucedido.
    |
    */

    'rehash_on_login' => env('HASH_REHASH_ON_LOGIN', true),

];
