<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Song;
use App\Models\Share;
use App\Notifications\UserFollowedNotification;
use App\Notifications\CommentOnPostNotification;
use App\Notifications\UserMentionedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_receive_notification_when_followed(): void
    {
        $userA = User::factory()->create(['name' => 'UserA']);
        $userB = User::factory()->create(['name' => 'UserB']);

        $response = $this
            ->actingAs($userA)
            ->post(route('users.follow', $userB->id));

        $response->assertOk();
        $response->assertJson([
            'followed' => true,
        ]);

        $this->assertTrue($userA->isFollowing($userB));

        // Verify userB received UserFollowedNotification
        $this->assertEquals(1, $userB->unreadNotifications()->count());
        $notification = $userB->unreadNotifications()->first();
        $this->assertEquals(UserFollowedNotification::class, $notification->type);
        $this->assertEquals('UserA started following you.', $notification->data['message']);
        $this->assertEquals($userA->id, $notification->data['follower_id']);
        $this->assertEquals($userA->name, $notification->data['follower_name']);
    }

    public function test_user_receives_notification_when_commented_on_their_share(): void
    {
        $userA = User::factory()->create(['name' => 'UserA']);
        $userB = User::factory()->create(['name' => 'UserB']);

        $song = Song::create([
            'track_name' => 'Test Track',
            'artist_name' => 'Test Artist',
            'spotify_track_id' => '12345abcdef',
        ]);

        $share = Share::create([
            'user_id' => $userB->id,
            'song_id' => $song->id,
            'caption' => 'Check this song',
        ]);

        $response = $this
            ->actingAs($userA)
            ->post(route('shares.comments.store', $share->id), [
                'body' => 'Nice track!',
            ]);

        $response->assertStatus(200);

        // Verify userB received CommentOnPostNotification
        $this->assertEquals(1, $userB->unreadNotifications()->count());
        $notification = $userB->unreadNotifications()->first();
        $this->assertEquals(CommentOnPostNotification::class, $notification->type);
        $this->assertEquals('UserA commented on your post.', $notification->data['message']);
        $this->assertEquals($share->id, $notification->data['share_id']);
    }

    public function test_user_receives_only_mention_notification_if_commenter_also_mentions_them(): void
    {
        $userA = User::factory()->create(['name' => 'UserA']);
        $userB = User::factory()->create(['name' => 'UserB']);

        $song = Song::create([
            'track_name' => 'Test Track',
            'artist_name' => 'Test Artist',
            'spotify_track_id' => '12345abcdef',
        ]);

        $share = Share::create([
            'user_id' => $userB->id,
            'song_id' => $song->id,
            'caption' => 'Check this song',
        ]);

        // UserA comments and mentions @UserB
        $response = $this
            ->actingAs($userA)
            ->post(route('shares.comments.store', $share->id), [
                'body' => 'Nice track @UserB!',
            ]);

        $response->assertStatus(200);

        // Verify userB received only 1 notification, and it's a mention notification
        $this->assertEquals(1, $userB->unreadNotifications()->count());
        $notification = $userB->unreadNotifications()->first();
        $this->assertEquals(UserMentionedNotification::class, $notification->type);
        $this->assertEquals('UserA mentioned you in a comment.', $notification->data['message']);
        $this->assertEquals($share->id, $notification->data['share_id']);
    }

    public function test_user_receives_notification_when_their_post_is_liked(): void
    {
        $userA = User::factory()->create(['name' => 'UserA']);
        $userB = User::factory()->create(['name' => 'UserB']);

        $song = Song::create([
            'track_name' => 'Test Track',
            'artist_name' => 'Test Artist',
            'spotify_track_id' => '12345abcdef',
        ]);

        $share = Share::create([
            'user_id' => $userB->id,
            'song_id' => $song->id,
            'caption' => 'Check this song',
        ]);

        // UserA likes UserB's share
        $response = $this
            ->actingAs($userA)
            ->post(route('shares.like', $share->id));

        $response->assertOk();
        $response->assertJson([
            'liked' => true,
        ]);

        // Verify userB received PostLikedNotification
        $this->assertEquals(1, $userB->unreadNotifications()->count());
        $notification = $userB->unreadNotifications()->first();
        $this->assertEquals(\App\Notifications\PostLikedNotification::class, $notification->type);
        $this->assertEquals('UserA liked your post.', $notification->data['message']);
        $this->assertEquals($share->id, $notification->data['share_id']);
        $this->assertEquals($userA->id, $notification->data['liker_id']);
    }
}
