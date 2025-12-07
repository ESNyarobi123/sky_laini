<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkingHoursController extends Controller
{
    /**
     * Show working hours settings.
     */
    public function index(Request $request): View|JsonResponse
    {
        $agent = $request->user()->agent;

        if (!$agent) {
            abort(403, 'Agent profile not found');
        }

        $workingHours = $agent->working_hours ?? $this->getDefaultWorkingHours();

        if ($request->expectsJson()) {
            return response()->json([
                'working_hours' => $workingHours,
                'timezone' => $agent->timezone,
                'auto_offline' => $agent->auto_offline,
            ]);
        }

        return view('agent.working-hours.index', compact('workingHours', 'agent'));
    }

    /**
     * Update working hours.
     */
    public function update(Request $request): JsonResponse
    {
        $agent = $request->user()->agent;

        if (!$agent) {
            return response()->json(['message' => 'Agent profile not found'], 403);
        }

        $validated = $request->validate([
            'working_hours' => 'required|array',
            'working_hours.*.enabled' => 'required|boolean',
            'working_hours.*.start' => 'required_if:working_hours.*.enabled,true|nullable|date_format:H:i',
            'working_hours.*.end' => 'required_if:working_hours.*.enabled,true|nullable|date_format:H:i',
            'timezone' => 'nullable|string|timezone',
            'auto_offline' => 'nullable|boolean',
        ]);

        $agent->update([
            'working_hours' => $validated['working_hours'],
            'timezone' => $validated['timezone'] ?? 'Africa/Dar_es_Salaam',
            'auto_offline' => $validated['auto_offline'] ?? true,
        ]);

        return response()->json([
            'message' => 'Working hours updated successfully',
            'working_hours' => $agent->working_hours,
        ]);
    }

    /**
     * Check if agent is within working hours.
     */
    public function checkStatus(Request $request): JsonResponse
    {
        $agent = $request->user()->agent;

        if (!$agent) {
            return response()->json(['message' => 'Agent profile not found'], 403);
        }

        $isWithinHours = $this->isWithinWorkingHours($agent);

        return response()->json([
            'within_working_hours' => $isWithinHours,
            'current_time' => Carbon::now($agent->timezone)->format('H:i'),
            'timezone' => $agent->timezone,
            'today' => strtolower(Carbon::now($agent->timezone)->format('l')),
            'message' => $isWithinHours 
                ? 'You are within your working hours' 
                : 'You are outside your working hours',
        ]);
    }

    /**
     * Check if agent is within working hours.
     */
    public function isWithinWorkingHours($agent): bool
    {
        if (!$agent->working_hours) {
            return true; // No hours set, always available
        }

        $now = Carbon::now($agent->timezone ?? 'Africa/Dar_es_Salaam');
        $dayOfWeek = strtolower($now->format('l'));

        $todayHours = $agent->working_hours[$dayOfWeek] ?? null;

        if (!$todayHours || !($todayHours['enabled'] ?? false)) {
            return false;
        }

        $start = Carbon::createFromFormat('H:i', $todayHours['start'], $agent->timezone);
        $end = Carbon::createFromFormat('H:i', $todayHours['end'], $agent->timezone);

        // Handle overnight shifts (e.g., 22:00 - 06:00)
        if ($end->lt($start)) {
            return $now->gte($start) || $now->lte($end);
        }

        return $now->between($start, $end);
    }

    /**
     * Get default working hours structure.
     */
    private function getDefaultWorkingHours(): array
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $hours = [];

        foreach ($days as $day) {
            $hours[$day] = [
                'enabled' => in_array($day, ['saturday', 'sunday']) ? false : true,
                'start' => '08:00',
                'end' => '18:00',
            ];
        }

        return $hours;
    }
}
