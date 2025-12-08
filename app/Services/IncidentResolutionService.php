<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Incident; 
use App\Models\DisciplineHistory; // Ensure this model exists

class IncidentResolutionService
{
    /**
     * Resolves an incident, ensuring atomicity via database transaction.
     * Both the incident update and the history log insertion must succeed.
     *
     * @param int $incidentId
     * @param array $actionData Contains 'action' string (e.g., '3-Day Suspension')
     * @return bool
     * @throws \Exception
     */
    public function resolve(int $incidentId, array $actionData): bool
    {
        // CHECKLIST: Transaction Implementation
        // If an exception occurs inside this block, all changes are automatically rolled back.
        DB::transaction(function () use ($incidentId, $actionData) {
            
            // --- Step 1: Update the Incident Status (DML Operation 1) ---
            // This update fires the Audit Log Trigger.
            $incident = Incident::findOrFail($incidentId);
            $incident->status = 'Resolved';
            $incident->disciplinary_action = $actionData['action'];
            $incident->save();

            // --- Step 2: Log the Disciplinary Action to History (DML Operation 2) ---
            DisciplineHistory::create([
                'student_id' => $incident->student_id, 
                'incident_id' => $incident->id,
                'action_taken' => $actionData['action'],
                'date_executed' => now(),
            ]);
            
            // If the code reaches this point, the transaction is automatically committed.

        }); // End of DB::transaction()

        return true;
    }
}