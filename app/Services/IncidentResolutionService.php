<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Incident;
use App\Models\DisciplineHistory;

class IncidentResolutionService
{
    public function resolve(int $incidentId, array $actionData): bool
    {
        DB::transaction(function () use ($incidentId, $actionData) {
            $incident = Incident::findOrFail($incidentId);
            $incident->status = 'Resolved';
            $incident->disciplinary_action = $actionData['action'];
            $incident->save();

            DisciplineHistory::create([
                'student_id' => $incident->student_id,
                'incident_id' => $incident->id,
                'action_taken' => $actionData['action'],
                'date_executed' => now(),
            ]);
        });

        return true;
    }
}
