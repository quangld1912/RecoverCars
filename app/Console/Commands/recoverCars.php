<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class recoverCars extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recoverCars';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recovery Deleted Cars';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        ///export?format=xlsx&gid
        $dailyCheck = base_path('assets/dailyCheck.xlsx');
        $chassisNo = base_path('assets/chassisNo.xlsx');
        $exportFile = fopen(base_path('assets/recover_deleted_cars_'.date('Ymd').'.sql'), 'w+');
        $data = \Excel::load($dailyCheck, function ($reader) {
        }, 'UTF-8')->get();

        $chassis = \Excel::load($chassisNo, function ($reader) {
        }, 'UTF-8')->get();

        if(empty($chassis) || !$chassis->count()){
            echo "Error: ChassisNo file is empty";
            return;
        }

        if(empty($data) || !$data->count()){
            echo "Error: DailyCheck File is empty";
            return;
        }

        $total = 0;
        foreach ($chassis as $key => $value1) {
            foreach ($data as $key => $value2) {
                if((string)$value1->車台番号 === (string)$value2->車台番号) {
                    $total++;
                    fwrite($exportFile, "UPDATE shop_cars SET delete_flg=0 WHERE id='$value2->id';\n");
                    var_dump($value2->id);
                };
            }
        }

        fwrite($exportFile, "-- Total: $total");
        fclose($exportFile);
    }
}
