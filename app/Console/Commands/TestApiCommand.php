<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestApiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test fieldstaff dashboard API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = \App\Models\User::where('role', 'fieldstaff')->first();
        if (!$user) {
            $user = \App\Models\User::create(['name' => 'FS', 'email' => 'fs@gmail.com', 'password' => \Illuminate\Support\Facades\Hash::make('12345'), 'role' => 'fieldstaff', 'status' => 'active']); 
            $user->assignRole('fieldstaff'); 
            \App\Models\FieldStaff::create(['user_id' => $user->id, 'sales_manager_id' => 1, 'address' => 'X', 'pincode' => '123']);
            $this->info("Created new fieldstaff user.");
        }

        auth('api')->login($user);
        $token = auth('api')->tokenById($user->id);

        $request = \Illuminate\Http\Request::create('/api/field-staff/dashboard', 'GET');
        $request->headers->set('Authorization', 'Bearer ' . $token);
        $request->headers->set('X-Device-ID', 'TEST-DEVICE-ID-123');

        try {
            $response = app()->handle($request);
            $this->info("STATUS: " . $response->getStatusCode());
            $this->line("CONTENT:");
            $this->line($response->getContent());
        } catch (\Exception $e) {
            $this->error("EXCEPTION: " . $e->getMessage());
            $this->line($e->getTraceAsString());
        } catch (\Error $e) {
            $this->error("FATAL ERROR: " . $e->getMessage());
            $this->line($e->getTraceAsString());
        }
    }
}
