<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\User;
use Database\Seeders\SecurityRbacSeeder;
use Spatie\Permission\Models\Role;

test('admin with products.view permission can access product management screen', function (): void {
    $this->seed(SecurityRbacSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get(route('products.index'));

    $response->assertOk();
});

test('non privileged user cannot access product management screen', function (): void {
    $this->seed(SecurityRbacSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('user');

    $response = $this->actingAs($user)->get(route('products.index'));

    $response->assertForbidden();
});

test('admin can create update and delete product', function (): void {
    $this->seed(SecurityRbacSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'web'));

    $createResponse = $this->actingAs($admin)->post(route('products.store'), [
        'name' => 'Songket Classic',
        'slug' => 'songket-classic',
        'category' => 'Women',
        'price_in_sen' => 19900,
        'original_price_in_sen' => 24900,
        'badge' => 'New',
        'gradient' => 'from-rose-100 via-orange-50 to-amber-100',
        'is_active' => true,
    ]);

    $createResponse->assertCreated();
    $this->assertDatabaseHas('products', ['slug' => 'songket-classic']);

    $product = Product::query()->where('slug', 'songket-classic')->firstOrFail();

    $updateResponse = $this->actingAs($admin)->put(route('products.update', $product), [
        'name' => 'Songket Classic Updated',
        'slug' => 'songket-classic-updated',
        'category' => 'Women',
        'price_in_sen' => 20900,
        'original_price_in_sen' => 25900,
        'badge' => 'Best Seller',
        'gradient' => 'from-red-100 via-orange-50 to-yellow-100',
        'is_active' => true,
    ]);

    $updateResponse->assertOk();
    $this->assertDatabaseHas('products', ['slug' => 'songket-classic-updated']);

    $deleteResponse = $this->actingAs($admin)->delete(route('products.destroy', $product->fresh()));

    $deleteResponse->assertOk();
    $this->assertDatabaseMissing('products', ['slug' => 'songket-classic-updated']);
});
