<?php

namespace App\Console\Commands;

use App\Models\Page;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakePage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chimera:make-page {title} {connection}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle()
    {
        Page::create([
            'title' => $this->argument('title'),
            'permission_name' => (string) Str::uuid(),
            'description' => 'Lorem ipsum...',
            'connection' => $this->argument('connection'),
        ]);
        return 0;
    }
}
