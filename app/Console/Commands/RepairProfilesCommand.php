<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\FieldStaff;
use App\Models\SalesManager;
use App\Models\Retailer;

class RepairProfilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repair:profiles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Repairs missing FieldStaff, SalesManager, and Retailer profile records for existing users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting profile repair...');
        
        $users = User::where('role', 'fieldstaff')->whereDoesntHave('fieldStaff')->get();
        $this->info("Found " . $users->count() . " missing fieldstaff profiles.");
        foreach($users as $u) {
            FieldStaff::create([
                'user_id' => $u->id,
                'contact_no' => $u->contact_no,
                'address' => $u->address ?? 'N/A',
                'pincode' => $u->pincode ?? '000000',
                'sales_manager_id' => SalesManager::first()->id ?? 1
            ]);
            $this->line("Repaired FieldStaff ID: {$u->id}");
        }

        $sm = User::where('role', 'salesmanager')->whereDoesntHave('salesManager')->get();
        $this->info("Found " . $sm->count() . " missing salesmanager profiles.");
        foreach($sm as $s) {
            SalesManager::create([
                'user_id' => $s->id,
                'contact_no' => $s->contact_no,
                'address' => $s->address ?? 'N/A',
                'pincode' => $s->pincode ?? '000000'
            ]);
            $this->line("Repaired SalesManager ID: {$s->id}");
        }
        
        $retailers = User::where('role', 'retailer')->whereDoesntHave('retailer')->get();
        $this->info("Found " . $retailers->count() . " missing retailer profiles.");
        foreach($retailers as $r) {
            Retailer::create([
                'user_id' => $r->id,
                'contact_no' => $r->contact_no ?? '0000000000',
                'address' => $r->address ?? 'N/A',
                'pincode' => $r->pincode ?? '000000'
            ]);
            $this->line("Repaired Retailer ID: {$r->id}");
        }

        $this->info('Repair complete.');
    }
}
