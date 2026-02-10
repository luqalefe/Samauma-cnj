$user = \App\Models\User::firstOrCreate(
    ['email' => 'luqalefe@gmail.com'],
    [
        'name' => 'Lucas Araujo',
        'password' => bcrypt('12345678'),
        'status' => \App\Enums\UserStatus::Ativo
    ]
);
$user->assignRole('super_admin');
echo "User {$user->email} created/updated and role assigned.\n";
exit;
