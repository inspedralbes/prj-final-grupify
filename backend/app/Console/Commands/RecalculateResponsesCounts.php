<?php

namespace App\Console\Commands;

use App\Models\FormAssignment;
use Illuminate\Console\Command;

class RecalculateResponsesCounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'forms:recalculate-responses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate the responses_count field in form_assignments table based on answered=1 in form_user table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting recalculation of response counts...');
        
        try {
            FormAssignment::recalculateResponsesCounts();
            $this->info('Successfully recalculated response counts for all form assignments!');
        } catch (\Exception $e) {
            $this->error('Error recalculating response counts: ' . $e->getMessage());
        }
        
        return Command::SUCCESS;
    }
}
