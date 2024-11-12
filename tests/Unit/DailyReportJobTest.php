<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Jobs\DailyReport;
use Illuminate\Support\Facades\Log;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use App\Notifications\DailyReportNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DailyReportJobTest extends TestCase
{
    use RefreshDatabase;


        public function test_daily_report_job_sends_notification()
        {
            Artisan::call('db:seed');

            Notification::fake();

            $user = User::findOrFail(1);
            $tasks = Task::findOrFail($user->id);
            log::info($tasks);
            $this->assertNotEmpty($user, "User with ID 1 not found.");
            $this->assertNotEmpty($tasks, "No tasks found for user with ID 1.");

            $job = new DailyReport($user->id);
            $job->handle();

            Notification::assertSentTo(
                [$user],
                DailyReportNotification::class,
                function ($notification, $channels) use ($tasks) {
                    Log::info('Daily report notification sent successfully');
                    return $notification->tasks->count() === $tasks->count();
                }
            );
        }
    }

