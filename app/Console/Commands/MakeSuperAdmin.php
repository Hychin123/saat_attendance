<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class MakeSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:super-admin {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a user super admin by email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email {$email} not found!");
            return 1;
        }
        
        $user->is_super_admin = true;
        $user->save();
        
        $this->info("User {$user->name} ({$user->email}) is now a super admin!");
        return 0;
    }
}
