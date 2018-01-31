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
        try {
            $data = \Excel::load($dailyCheck, function ($reader) {
            }, 'UTF-8')->get();

            $chassis = \Excel::load($chassisNo, function ($reader) {
            }, 'UTF-8')->get();
        } catch(Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }

        if(empty($chassis) || !$chassis->count()){
            echo "Error: ChassisNo file is empty";
            return;
        }

        if(empty($data) || !$data->count()){
            echo "Error: DailyCheck File is empty";
            return;
        }

        $total = 0;
        $nonRestore = [];
        foreach ($data as $key1 => $value1) {
            foreach ($chassis as $key2 => $value2) {
                if((string)$value2->車台番号 === (string)$value1->車台番号) {
                    $total++;
                    fwrite($exportFile, "UPDATE shop_cars SET delete_flg=0 WHERE id=$value1->id;\n");
                    var_dump($value1->id);
                    break;
                } else {
                    if(($key2 === count($chassis) - 1) && !array_key_exists((integer)$value1->id, $nonRestore)) {
                        $nonRestore[$value1->id] = $value1->車台番号;
                    }
                }
            }
        }

        fwrite($exportFile, "-- Total cars need restoring: $total\n");
        fwrite($exportFile, "------------------------------------\n");
        fwrite($exportFile, "-- Cars don't need restoring --\n");
        foreach($nonRestore as $key => $value) {
            fwrite($exportFile, "    -- id = $key, bbno = $value\n");
        }
        fwrite($exportFile, "-- Total cars don't need restoring: " . count($nonRestore) . "\n");
        fclose($exportFile);
    }
}
