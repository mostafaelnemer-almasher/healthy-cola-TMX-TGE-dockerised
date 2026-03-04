<?php

namespace App\Console\Commands;

use App\Models\IcoStage;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ActiveStageCronJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:active-stage-cron-job';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
//        Log::info('Cron job is working correctly');
        $timezone = get_setting('site_timezone', 'UTC');
        $current_date = now()->timezone($timezone);
        $stage=IcoStage::where('status','active')->whereRaw("? BETWEEN start_date AND end_date",[$current_date])->first();
        $activeStage=active_stage();
        if($activeStage->id!=$stage->id){
            Setting::updateValue('actived_stage', $stage->id);
        }
        return Command::SUCCESS;
    }
}
