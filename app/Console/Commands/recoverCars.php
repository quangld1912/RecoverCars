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
        $address = base_path('assets/chassisNo.xlsx');
        \Excel::load($address, function ($reader) {
            foreach($reader->toArray() as $row)
            {
            print_r($row);
            }
        });
    }
}
