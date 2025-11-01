<?php

namespace App\Console\Commands;

use App\Models\Share;
use App\Models\Comment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearSharesData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-shares-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently deletes all shares, likes, and comments from the database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->confirm('This will permanently delete ALL shares, likes, and comments. Do you wish to continue?')) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            Share::truncate();
            Comment::truncate();
            DB::table('likes')->truncate();
            DB::table('comment_threads')->truncate();

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->info('All shares, likes, and comments have been deleted.');
        } else {
            $this->info('Operation cancelled.');
        }
    }
}
