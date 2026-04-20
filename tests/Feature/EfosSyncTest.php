<?php

namespace Tests\Feature;

use App\Jobs\SyncEfosJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EfosSyncTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'superadmin', 'guard_name' => 'web']);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('superadmin');
    }

    public function test_sync_dispatches_job_and_returns_job_id(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->admin)
            ->postJson('/sat-efos-69b/sync');

        $response->assertOk()
                 ->assertJsonStructure(['job_id']);

        Queue::assertPushed(SyncEfosJob::class);
    }

    public function test_sync_returns_409_when_job_already_running(): void
    {
        Queue::fake();

        $jobId = uniqid('efos_', true);
        Cache::put('efos_sync_current', $jobId, now()->addHours(2));
        Cache::put("efos_sync_{$jobId}", ['status' => 'running'], now()->addHours(2));

        $response = $this->actingAs($this->admin)
            ->postJson('/sat-efos-69b/sync');

        $response->assertStatus(409)
                 ->assertJson(['message' => 'Ya hay una sincronización en curso']);

        Queue::assertNothingPushed();
    }

    public function test_sync_status_returns_state_from_cache(): void
    {
        $jobId = uniqid('efos_', true);
        Cache::put("efos_sync_{$jobId}", [
            'status'    => 'running',
            'processed' => 5000,
            'total'     => 80000,
            'message'   => 'Procesando...',
        ], now()->addHours(2));

        $response = $this->actingAs($this->admin)
            ->getJson("/sat-efos-69b/sync/{$jobId}");

        $response->assertOk()
                 ->assertJson([
                     'status'    => 'running',
                     'processed' => 5000,
                     'total'     => 80000,
                 ]);
    }

    public function test_sync_status_returns_404_for_unknown_job(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/sat-efos-69b/sync/nonexistent_job_id');

        $response->assertNotFound();
    }

    public function test_sync_sets_completed_status_on_success(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->admin)
            ->postJson('/sat-efos-69b/sync');

        $jobId = $response->json('job_id');

        Cache::put("efos_sync_{$jobId}", [
            'status'      => 'completed',
            'processed'   => 87000,
            'total'       => 87000,
            'message'     => 'Sincronización completada',
            'finished_at' => now()->toDateTimeString(),
        ], now()->addHours(2));

        $status = $this->actingAs($this->admin)
            ->getJson("/sat-efos-69b/sync/{$jobId}");

        $status->assertOk()
               ->assertJson(['status' => 'completed', 'processed' => 87000]);
    }
}
