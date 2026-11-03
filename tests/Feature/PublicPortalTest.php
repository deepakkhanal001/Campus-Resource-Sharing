<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_browse_the_public_catalog(): void
    {
        $owner = $this->makeUser();
        [$category, $resource] = $this->makeResource($owner);

        $this->get('/')
            ->assertOk()
            ->assertSee('Browse resources')
            ->assertSee($resource->title);

        $this->get(route('public.resources.index'))
            ->assertOk()
            ->assertSee($resource->title)
            ->assertSee($category->name);

        $this->get(route('public.categories.index'))
            ->assertOk()
            ->assertSee($category->name);

        $this->get(route('public.resources.show', $resource->id))
            ->assertOk()
            ->assertSee($resource->title)
            ->assertSee($owner->name);
    }

    public function test_active_student_can_request_an_available_physical_resource(): void
    {
        $owner = $this->makeUser();
        $student = $this->makeUser();
        [, $resource] = $this->makeResource($owner);

        $this->actingAs($student)
            ->get(route('public.borrowings.request', $resource->id))
            ->assertOk()
            ->assertSee($resource->title);

        $response = $this->actingAs($student)->post(route('public.borrowings.store', $resource->id), [
            'message' => 'I can collect this after class.',
            'pickup_date' => now()->addDay()->toDateString(),
            'return_date' => now()->addDays(4)->toDateString(),
        ]);

        $response->assertRedirect(route('public.borrowings.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('borrowing_records', [
            'resource_id' => $resource->id,
            'requester_id' => $student->id,
            'owner_id' => $owner->id,
            'status' => 'pending',
            'request_message' => 'I can collect this after class.',
        ]);

        $this->assertDatabaseHas('resources', [
            'id' => $resource->id,
            'availability_status' => 'available',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $owner->id,
            'type' => 'borrow_request',
        ]);

        $this->actingAs($student)
            ->get(route('public.borrowings.index'))
            ->assertOk()
            ->assertSee($resource->title)
            ->assertSee('Requested');

        $this->actingAs($student)
            ->get(route('public.resources.show', $resource->id))
            ->assertOk()
            ->assertSee('You have an open request');
    }

    public function test_duplicate_active_requests_are_blocked(): void
    {
        $owner = $this->makeUser();
        $student = $this->makeUser();
        [, $resource] = $this->makeResource($owner);

        $this->createBorrowing($resource, $student, $owner, 'pending');

        $response = $this->actingAs($student)->post(route('public.borrowings.store', $resource->id), [
            'message' => 'A second request.',
        ]);

        $response->assertSessionHasErrors('request');
        $this->assertSame(1, DB::table('borrowing_records')->count());
    }

    public function test_inactive_or_admin_accounts_cannot_request_resources(): void
    {
        $owner = $this->makeUser();
        $inactiveStudent = $this->makeUser('student', 'inactive');
        $admin = $this->makeUser('admin');
        [, $resource] = $this->makeResource($owner);

        $this->actingAs($inactiveStudent)
            ->get(route('public.borrowings.request', $resource->id))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('public.borrowings.store', $resource->id), ['message' => 'Admin request'])
            ->assertForbidden();

        $this->assertSame(0, DB::table('borrowing_records')->count());
    }

    public function test_owner_can_progress_a_request_from_approval_to_pickup_to_return(): void
    {
        $owner = $this->makeUser();
        $student = $this->makeUser();
        [, $resource] = $this->makeResource($owner);
        $borrowingId = $this->createBorrowing($resource, $student, $owner, 'pending');

        $this->actingAs($owner)
            ->get(route('public.owner-requests.index'))
            ->assertOk()
            ->assertSee($resource->title)
            ->assertSee($student->name);

        $this->actingAs($owner)
            ->get(route('public.resources.show', $resource->id))
            ->assertOk()
            ->assertSee('You own this resource');

        $this->actingAs($owner)
            ->patch(route('public.owner-requests.update', $borrowingId), ['action' => 'approve'])
            ->assertRedirect(route('public.owner-requests.index'));

        $this->assertDatabaseHas('borrowing_records', ['id' => $borrowingId, 'status' => 'approved']);
        $this->assertDatabaseHas('resources', ['id' => $resource->id, 'availability_status' => 'borrowed']);

        $this->actingAs($owner)
            ->patch(route('public.owner-requests.update', $borrowingId), ['action' => 'picked_up'])
            ->assertRedirect(route('public.owner-requests.index'));

        $this->assertDatabaseHas('borrowing_records', ['id' => $borrowingId, 'status' => 'picked_up']);
        $this->assertDatabaseHas('resources', ['id' => $resource->id, 'availability_status' => 'borrowed']);

        $this->actingAs($owner)
            ->patch(route('public.owner-requests.update', $borrowingId), ['action' => 'returned'])
            ->assertRedirect(route('public.owner-requests.index'));

        $this->assertDatabaseHas('borrowing_records', ['id' => $borrowingId, 'status' => 'returned']);
        $this->assertDatabaseHas('resources', ['id' => $resource->id, 'availability_status' => 'available']);

        $this->assertDatabaseHas('notifications', ['user_id' => $student->id, 'type' => 'borrow_approved']);
        $this->assertDatabaseHas('notifications', ['user_id' => $student->id, 'type' => 'borrow_picked_up']);
        $this->assertDatabaseHas('notifications', ['user_id' => $student->id, 'type' => 'borrow_returned']);
    }

    public function test_non_owner_cannot_manage_a_borrowing_request(): void
    {
        $owner = $this->makeUser();
        $student = $this->makeUser();
        $otherStudent = $this->makeUser();
        [, $resource] = $this->makeResource($owner);
        $borrowingId = $this->createBorrowing($resource, $student, $owner, 'pending');

        $this->actingAs($otherStudent)
            ->patch(route('public.owner-requests.update', $borrowingId), ['action' => 'approve'])
            ->assertForbidden();

        $this->assertDatabaseHas('borrowing_records', ['id' => $borrowingId, 'status' => 'pending']);
        $this->assertDatabaseHas('resources', ['id' => $resource->id, 'availability_status' => 'available']);
    }

    public function test_owner_can_reject_a_request_with_remarks(): void
    {
        $owner = $this->makeUser();
        $student = $this->makeUser();
        [, $resource] = $this->makeResource($owner);
        $borrowingId = $this->createBorrowing($resource, $student, $owner, 'pending');

        $response = $this->actingAs($owner)
            ->patch(route('public.owner-requests.update', $borrowingId), [
                'action' => 'reject',
                'remarks' => 'This item is already promised to someone else.',
            ]);
        $response->assertRedirect(route('public.owner-requests.index'));

        $this->assertDatabaseHas('borrowing_records', [
            'id' => $borrowingId,
            'status' => 'rejected',
            'rejection_reason' => 'This item is already promised to someone else.',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $student->id,
            'type' => 'borrow_rejected',
        ]);

        $notification = DB::table('notifications')
            ->where('user_id', $student->id)
            ->where('type', 'borrow_rejected')
            ->first();

        $this->assertStringContainsString('already promised', $notification->message);

        $this->actingAs($student)
            ->get(route('public.borrowings.index'))
            ->assertOk()
            ->assertSee('Rejection remarks')
            ->assertSee('This item is already promised to someone else.');
    }

    public function test_requester_can_cancel_a_pending_request(): void
    {
        $owner = $this->makeUser();
        $student = $this->makeUser();
        [, $resource] = $this->makeResource($owner);
        $borrowingId = $this->createBorrowing($resource, $student, $owner, 'pending');

        $response = $this->actingAs($student)
            ->delete(route('public.borrowings.cancel', $borrowingId));
        $response->assertRedirect(route('public.borrowings.index'));

        $this->assertDatabaseHas('borrowing_records', ['id' => $borrowingId, 'status' => 'cancelled']);
        $this->assertDatabaseHas('notifications', ['user_id' => $owner->id, 'type' => 'borrow_cancelled']);
        $this->assertDatabaseHas('resources', ['id' => $resource->id, 'availability_status' => 'available']);
    }

    public function test_digital_downloads_require_an_active_authorized_user(): void
    {
        Storage::fake('local');

        $owner = $this->makeUser();
        $student = $this->makeUser();
        [, $resource] = $this->makeResource($owner, 'digital', 'available');

        Storage::disk('local')->put('digital-files/notes.txt', 'Campus notes');

        $fileId = DB::table('digital_files')->insertGetId([
            'resource_id' => $resource->id,
            'file_path' => 'digital-files/notes.txt',
            'file_name' => 'notes.txt',
            'file_type' => 'txt',
            'size' => 12,
            'created_at' => now(),
        ]);

        $downloadUrl = route('public.resources.download', [
            'resource' => $resource->id,
            'digitalFile' => $fileId,
        ]);

        $this->get($downloadUrl)->assertRedirect(route('login'));

        $this->actingAs($student)
            ->get(route('public.resources.show', $resource->id))
            ->assertOk()
            ->assertSee('notes.txt');

        $this->actingAs($student)->get($downloadUrl)->assertOk();

        $this->assertSame(
            1,
            (int) DB::table('digital_files')->where('id', $fileId)->value('download_count')
        );

        DB::table('resources')->where('id', $resource->id)->update([
            'availability_status' => 'borrowed',
            'updated_at' => now(),
        ]);

        $this->actingAs($student)->get($downloadUrl)->assertForbidden();

        $this->assertSame(
            1,
            (int) DB::table('digital_files')->where('id', $fileId)->value('download_count')
        );

        $this->actingAs($owner)->get($downloadUrl)->assertOk();

        $this->assertSame(
            2,
            (int) DB::table('digital_files')->where('id', $fileId)->value('download_count')
        );
    }

    public function test_authenticated_users_can_manage_their_notifications(): void
    {
        $user = $this->makeUser();
        $otherUser = $this->makeUser();

        $notificationId = DB::table('notifications')->insertGetId([
            'user_id' => $user->id,
            'message' => 'Your request was approved.',
            'type' => 'borrow_approved',
            'is_read' => false,
            'created_at' => now(),
        ]);

        $otherNotificationId = DB::table('notifications')->insertGetId([
            'user_id' => $otherUser->id,
            'message' => 'A private notification.',
            'type' => 'borrow_request',
            'is_read' => false,
            'created_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('public.notifications.index'))
            ->assertOk()
            ->assertSee('Your request was approved.');

        $this->actingAs($user)
            ->patch(route('public.notifications.read', $notificationId))
            ->assertRedirect();

        $this->assertDatabaseHas('notifications', ['id' => $notificationId, 'is_read' => true]);

        $this->actingAs($user)
            ->patch(route('public.notifications.read', $otherNotificationId))
            ->assertRedirect();

        $this->assertDatabaseHas('notifications', ['id' => $otherNotificationId, 'is_read' => false]);

        $this->actingAs($user)
            ->patch(route('public.notifications.read-all'))
            ->assertRedirect();

        $this->assertDatabaseHas('notifications', ['id' => $notificationId, 'is_read' => true]);
        $this->assertDatabaseHas('notifications', ['id' => $otherNotificationId, 'is_read' => false]);
    }

    public function test_public_account_pages_require_authentication(): void
    {
        $this->get(route('public.borrowings.index'))->assertRedirect(route('login'));
        $this->get(route('public.owner-requests.index'))->assertRedirect(route('login'));
        $this->get(route('public.notifications.index'))->assertRedirect(route('login'));
    }

    private function makeUser(string $role = 'student', string $status = 'active'): User
    {
        $user = User::factory()->create();

        DB::table('users')->where('id', $user->id)->update([
            'role' => $role,
            'status' => $status,
        ]);

        return $user->fresh();
    }

    private function makeResource(
        User $owner,
        string $type = 'physical',
        string $availability = 'available'
    ): array {
        $categoryId = DB::table('categories')->insertGetId([
            'name' => 'Category '.fake()->unique()->numberBetween(1, 999999),
            'description' => 'A shared category.',
            'created_at' => now(),
        ]);

        $resourceId = DB::table('resources')->insertGetId([
            'title' => 'Resource '.fake()->unique()->word(),
            'description' => 'A useful resource for testing.',
            'type' => $type,
            'owner_id' => $owner->id,
            'category_id' => $categoryId,
            'availability_status' => $availability,
            'created_at' => now(),
        ]);

        return [
            DB::table('categories')->where('id', $categoryId)->first(),
            DB::table('resources')->where('id', $resourceId)->first(),
        ];
    }

    private function createBorrowing(
        object $resource,
        User $requester,
        User $owner,
        string $status
    ): int {
        return DB::table('borrowing_records')->insertGetId([
            'resource_id' => $resource->id,
            'requester_id' => $requester->id,
            'owner_id' => $owner->id,
            'status' => $status,
            'request_message' => 'A test request.',
            'created_at' => now(),
        ]);
    }
}
