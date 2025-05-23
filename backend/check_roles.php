<?php
require __DIR__.'/vendor/autoload.php';

use Illuminate\Foundation\Application;

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Obtener los roles
$roles = DB::table('roles')->get();
echo "Roles disponibles:\n";
foreach ($roles as $role) {
    echo "- ID: {$role->id}, Nombre: {$role->name}\n";
}

// Obtener los usuarios por rol
$users = DB::table('users')
    ->join('roles', 'users.role_id', '=', 'roles.id')
    ->select('users.id', 'users.name', 'users.email', 'roles.name as role_name')
    ->get();

echo "\nUsuarios por rol:\n";
$roleUsers = [];
foreach ($users as $user) {
    if (!isset($roleUsers[$user->role_name])) {
        $roleUsers[$user->role_name] = [];
    }
    $roleUsers[$user->role_name][] = [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email
    ];
}

foreach ($roleUsers as $roleName => $usersInRole) {
    echo "Rol: {$roleName} - " . count($usersInRole) . " usuarios\n";
    foreach ($usersInRole as $user) {
        echo "  - ID: {$user['id']}, Nombre: {$user['name']}, Email: {$user['email']}\n";
    }
}
