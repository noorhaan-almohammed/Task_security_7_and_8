<?php
namespace App\Jobs;

use Exception;
use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Notifications\DailyReportNotification;

class dailyReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    // public function handle()
    // {
    //     // dd($this->userId);
    //     Log::info('Job bbbbbbbbbb ');
    //     try{
    //         $tasks = Task::dailyReport( $this->userId)->get();
    //         Cache::put('daily_report_' . $this->userId, $tasks, 10);
    //         Log::info('Job executed for user: ' . $this->userId);
    //     }catch(Exception $e){
    //         log::error($e->getMessage());
    //     }
    //    }

    // public function handle()
    // {
    //     $tasks = Task::dailyReport()->get();
    //     if ($tasks) {
    //         $user = User::find($this->userId);
    //         $user->notify(new DailyReportNotification($tasks));
    //     }
    // }
    public function handle()
    {
        Log::info('Job executed for user: ' . $this->userId);
        $user = User::find($this->userId);
        $tasks = Task::dailyReport($this->userId)->get();

        if ($user && $tasks) {
            Log::info("Sending daily report notification to user {$user->id} with tasks count: " . $tasks->count());
            $user->notify(new DailyReportNotification($tasks));
        }
    }
}
