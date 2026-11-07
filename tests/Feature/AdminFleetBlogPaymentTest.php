<?php

namespace Tests\Feature;

use App\Models\PaymentMethod;
use App\Models\Post;
use App\Models\Shipment;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFleetBlogPaymentTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_non_admin_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin')->assertForbidden();
        $this->actingAs($user)->get('/admin/shipments')->assertForbidden();
        $this->actingAs($user)->get('/admin/users')->assertForbidden();
        $this->actingAs($user)->get('/admin/vehicles')->assertForbidden();
    }

    public function test_admin_can_view_dashboard_and_stats(): void
    {
        $admin = $this->admin();
        Shipment::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('Admin dashboard');
    }

    public function test_admin_can_list_and_update_any_shipment(): void
    {
        $admin = $this->admin();
        $shipment = Shipment::factory()->create(['status' => 'pending']);

        $this->actingAs($admin)->get('/admin/shipments')->assertStatus(200);

        $this->actingAs($admin)->patch("/admin/shipments/{$shipment->id}/status", [
            'status' => 'delivered',
        ])->assertRedirect();

        $this->assertSame('delivered', $shipment->fresh()->status);
    }

    public function test_admin_status_update_assigns_vehicle_and_notifies_customer(): void
    {
        \Illuminate\Support\Facades\Notification::fake();

        $admin = $this->admin();
        $vehicle = Vehicle::create(['name' => 'Van 7', 'type' => 'van', 'status' => 'available']);
        $shipment = Shipment::factory()->create(['status' => 'pending']);

        $this->actingAs($admin)->patch("/admin/shipments/{$shipment->id}/status", [
            'status' => 'in_transit',
            'vehicle_id' => $vehicle->id,
        ])->assertRedirect();

        $shipment->refresh();
        $this->assertSame('in_transit', $shipment->status);
        $this->assertSame($vehicle->id, $shipment->vehicle_id);

        \Illuminate\Support\Facades\Notification::assertSentTo(
            $shipment->user,
            \App\Notifications\ShipmentStatusUpdated::class,
        );
    }

    public function test_admin_can_list_users_and_toggle_admin(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();

        $this->actingAs($admin)->get('/admin/users')->assertStatus(200);

        $this->actingAs($admin)->patch("/admin/users/{$user->id}/toggle-admin")->assertRedirect();
        $this->assertTrue($user->fresh()->is_admin);
    }

    public function test_admin_cannot_toggle_own_admin_status(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->patch("/admin/users/{$admin->id}/toggle-admin")->assertForbidden();
    }

    public function test_admin_can_crud_vehicles(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/vehicles', [
            'name' => 'Truck 01',
            'type' => 'truck',
            'plate_number' => 'LAG-123-XX',
            'status' => 'available',
        ])->assertRedirect(route('admin.vehicles.index'));

        $vehicle = Vehicle::first();
        $this->assertSame('Truck 01', $vehicle->name);

        $this->actingAs($admin)->get('/admin/vehicles')->assertStatus(200);
        $this->actingAs($admin)->put("/admin/vehicles/{$vehicle->id}", [
            'name' => 'Truck 01',
            'type' => 'truck',
            'status' => 'on_trip',
        ])->assertRedirect();
        $this->assertSame('on_trip', $vehicle->fresh()->status);

        $this->actingAs($admin)->delete("/admin/vehicles/{$vehicle->id}")->assertRedirect();
        $this->assertDatabaseMissing('vehicles', ['id' => $vehicle->id]);
    }

    public function test_public_can_view_published_posts_only(): void
    {
        Post::create([
            'user_id' => User::factory()->create()->id,
            'title' => 'Published Post',
            'slug' => 'published-post',
            'body' => 'Hello world',
            'is_published' => true,
            'published_at' => now(),
        ]);
        Post::create([
            'user_id' => User::factory()->create()->id,
            'title' => 'Draft Post',
            'slug' => 'draft-post',
            'body' => 'Secret',
            'is_published' => false,
        ]);

        $this->get('/blog')->assertStatus(200)->assertSee('Published Post')->assertDontSee('Draft Post');
        $this->get('/blog/published-post')->assertStatus(200)->assertSee('Hello world');
        $this->get('/blog/draft-post')->assertNotFound();
    }

    public function test_admin_can_create_and_publish_post(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/posts', [
            'title' => 'New Route Launch',
            'excerpt' => 'We launched a new lane',
            'body' => 'Full body text',
            'is_published' => '1',
        ])->assertRedirect(route('admin.posts.index'));

        $post = Post::first();
        $this->assertTrue($post->is_published);
        $this->assertSame('new-route-launch', $post->slug);

        $this->get('/blog/new-route-launch')->assertStatus(200);
    }

    public function test_user_can_pay_for_shipment_with_saved_method(): void
    {
        $user = User::factory()->create();
        $method = PaymentMethod::create([
            'user_id' => $user->id,
            'type' => 'card',
            'brand' => 'visa',
            'last4' => '4242',
            'expiry_month' => 12,
            'expiry_year' => (int) date('Y') + 2,
            'cardholder_name' => 'Test',
        ]);
        $shipment = Shipment::factory()->create(['user_id' => $user->id, 'amount' => 50000]);

        $response = $this->actingAs($user)->post("/shipments/{$shipment->id}/pay", [
            'payment_method_id' => $method->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'user_id' => $user->id,
            'shipment_id' => $shipment->id,
            'amount' => 50000,
            'status' => 'paid',
        ]);
    }

    public function test_user_cannot_pay_with_another_users_method(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $method = PaymentMethod::create([
            'user_id' => $other->id,
            'type' => 'card',
            'brand' => 'visa',
            'last4' => '4242',
            'expiry_month' => 12,
            'expiry_year' => (int) date('Y') + 2,
            'cardholder_name' => 'Other',
        ]);
        $shipment = Shipment::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->post("/shipments/{$shipment->id}/pay", [
            'payment_method_id' => $method->id,
        ])->assertNotFound();
    }
}
