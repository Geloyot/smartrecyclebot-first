<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Notification;
use Livewire\Livewire;
use Carbon\Carbon;

/**
 * Integration Test: ITG-08
 *
 * Module Names: Automatic Full Bin Alert and Notification Reset / Acknowledgment
 * Test Case ID: ITG-08
 * Date Tested: 11/12/25
 * Pre-condition: Verify if Notification status updates are acknowledged.
 */
class Integration8Test extends TestCase
{
    use RefreshDatabase;

    public function test_mark_all_read_acknowledges_notifications_and_updates_unread_count()
    {
        // Arrange: create and authenticate a user
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create some notifications: one read (older), one global unread, one user-specific unread
        Notification::forceCreate([
            'type'       => 'system',
            'title'      => 'Already read',
            'message'    => 'This was already read',
            'level'      => 'info',
            'is_read'    => true,
            'user_id'    => null,
            'created_at' => Carbon::now()->subMinutes(20),
            'updated_at' => Carbon::now()->subMinutes(20),
        ]);

        Notification::forceCreate([
            'type'       => 'system',
            'title'      => 'Global unread',
            'message'    => 'Global unread message',
            'level'      => 'info',
            'is_read'    => false,
            'user_id'    => null,
            'created_at' => Carbon::now()->subMinutes(2),
            'updated_at' => Carbon::now()->subMinutes(2),
        ]);

        // Changed level from 'critical' to 'info' to match DB allowed values
        Notification::forceCreate([
            'type'       => 'user',
            'title'      => 'User unread',
            'message'    => 'User-specific unread message',
            'level'      => 'info',
            'is_read'    => false,
            'user_id'    => $user->id,
            'created_at' => Carbon::now()->subMinute(),
            'updated_at' => Carbon::now()->subMinute(),
        ]);

        // Pre-assert: unread-count should report 2
        $before = $this->getJson(route('notifications.unreadCount'));
        $before->assertStatus(200);
        $this->assertEquals(2, $before->json('unread_count'), 'Precondition failed: expected 2 unread notifications.');

        // Act: call mark-all-read endpoint
        $markAll = $this->postJson(route('notifications.markAllRead'));
        $markAll->assertStatus(200)->assertJson(['success' => true]);

        // Assert: unread-count endpoint should now be 0
        $after = $this->getJson(route('notifications.unreadCount'));
        $after->assertStatus(200);
        $this->assertEquals(0, $after->json('unread_count'), 'After markAllRead, unread_count should be 0.');

        // Assert database: no notifications (global or user-specific) remain unread for this user
        $this->assertEquals(0, Notification::where(function ($q) use ($user) {
            $q->whereNull('user_id')->orWhere('user_id', $user->id);
        })->where('is_read', false)->count(), 'There should be no unread notifications after markAllRead.');
    }

    public function test_mark_individual_notification_acknowledged_via_livewire()
    {
        // Arrange: create and authenticate a user
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create an unread notification (user-specific)
        $notif = Notification::forceCreate([
            'type'       => 'user',
            'title'      => 'Individual unread',
            'message'    => 'Mark me read via Livewire',
            'level'      => 'info',
            'is_read'    => false,
            'user_id'    => $user->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Pre-assert: ensure it's unread
        $this->assertFalse(Notification::find($notif->id)->is_read, 'Notification should start as unread.');

        // Act: call Livewire component method markRead($id)
        Livewire::test(\App\Livewire\Notify::class)
            ->call('markRead', $notif->id);

        // Assert: the notification is now marked as read
        $this->assertTrue(Notification::find($notif->id)->is_read, 'Notification should be marked as read after markRead call.');

        // And unread-count reflects change (optional)
        $countResp = $this->getJson(route('notifications.unreadCount'));
        $countResp->assertStatus(200);
        $this->assertEquals(0, $countResp->json('unread_count'), 'Unread count should be 0 after marking the single notification read.');
    }
}
