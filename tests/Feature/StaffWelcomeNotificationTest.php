<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\StaffWelcomeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class StaffWelcomeNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_is_sent_via_mail_only(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'test@petrotal.com.mx']);
        $user->notify(new StaffWelcomeNotification('MiPassword123'));

        Notification::assertSentTo($user, StaffWelcomeNotification::class, function ($notification) {
            return in_array('mail', $notification->via($notification));
        });
    }

    public function test_notification_is_not_sent_via_database(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'test@petrotal.com.mx']);
        $user->notify(new StaffWelcomeNotification('MiPassword123'));

        Notification::assertSentTo($user, StaffWelcomeNotification::class, function ($notification) {
            return !in_array('database', $notification->via($notification));
        });
    }
}
