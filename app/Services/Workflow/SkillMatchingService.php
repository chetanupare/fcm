<?php

namespace App\Services\Workflow;

use App\Models\Technician;
use App\Models\Ticket;
use App\Models\Device;
use Illuminate\Support\Collection;

class SkillMatchingService
{
    /**
     * Calculate skill match score for a technician and ticket
     * 
     * @param Technician $technician
     * @param Ticket $ticket
     * @return float Score from 0 to 100
     */
    public function calculateMatchScore(Technician $technician, Ticket $ticket): float
    {
        $score = 0;
        $maxScore = 100;

        // Get device type from ticket
        $device = $ticket->device;
        if (!$device || !$device->device_type_id) {
            // No device type info, return base score
            return 50;
        }

        $deviceTypeId = $device->device_type_id;
        $technicianSkill = $technician->getSkillForDeviceType($deviceTypeId);

        if (!$technicianSkill) {
            // No matching skill, return low score
            return 20;
        }

        // Base score for having the skill: 40 points
        $score += 40;

        // Complexity level bonus: 30 points
        $complexityScore = $this->getComplexityScore($technicianSkill->complexity_level);
        $score += $complexityScore;

        // Experience bonus: 20 points (based on experience years and jobs completed)
        $experienceScore = $this->getExperienceScore($technicianSkill);
        $score += $experienceScore;

        // Success rate bonus: 10 points
        $successScore = $this->getSuccessRateScore($technicianSkill);
        $score += $successScore;

        return min($score, $maxScore);
    }

    /**
     * Get complexity level score
     * 
     * @param string $complexityLevel
     * @return float
     */
    protected function getComplexityScore(string $complexityLevel): float
    {
        return match($complexityLevel) {
            'basic' => 5,
            'intermediate' => 15,
            'advanced' => 25,
            'expert' => 30,
            default => 0,
        };
    }

    /**
     * Get experience score based on years and jobs completed
     * 
     * @param \App\Models\TechnicianSkill $skill
     * @return float
     */
    protected function getExperienceScore($skill): float
    {
        $score = 0;

        // Years of experience (max 10 points)
        if ($skill->experience_years >= 5) {
            $score += 10;
        } elseif ($skill->experience_years >= 3) {
            $score += 7;
        } elseif ($skill->experience_years >= 1) {
            $score += 4;
        }

        // Jobs completed (max 10 points)
        if ($skill->jobs_completed >= 100) {
            $score += 10;
        } elseif ($skill->jobs_completed >= 50) {
            $score += 7;
        } elseif ($skill->jobs_completed >= 20) {
            $score += 4;
        } elseif ($skill->jobs_completed >= 5) {
            $score += 2;
        }

        return min($score, 20);
    }

    /**
     * Get success rate score
     * 
     * @param \App\Models\TechnicianSkill $skill
     * @return float
     */
    protected function getSuccessRateScore($skill): float
    {
        if (!$skill->success_rate) {
            return 0;
        }

        if ($skill->success_rate >= 95) {
            return 10;
        } elseif ($skill->success_rate >= 90) {
            return 8;
        } elseif ($skill->success_rate >= 85) {
            return 6;
        } elseif ($skill->success_rate >= 80) {
            return 4;
        }

        return 2;
    }

    /**
     * Sort technicians by skill match score
     * 
     * @param Collection $technicians
     * @param Ticket $ticket
     * @return Collection
     */
    public function sortTechniciansBySkillMatch(Collection $technicians, Ticket $ticket): Collection
    {
        return $technicians->map(function ($technician) use ($ticket) {
            $technician->skill_match_score = $this->calculateMatchScore($technician, $ticket);
            return $technician;
        })->sortByDesc(function ($technician) {
            return $technician->skill_match_score ?? 0;
        })->values();
    }

    /**
     * Filter technicians by minimum skill match score
     * 
     * @param Collection $technicians
     * @param Ticket $ticket
     * @param float $minScore
     * @return Collection
     */
    public function filterByMinScore(Collection $technicians, Ticket $ticket, float $minScore = 40): Collection
    {
        return $technicians->filter(function ($technician) use ($ticket, $minScore) {
            $score = $this->calculateMatchScore($technician, $ticket);
            return $score >= $minScore;
        });
    }

    /**
     * Get best matching technicians (top N)
     * 
     * @param Collection $technicians
     * @param Ticket $ticket
     * @param int $limit
     * @return Collection
     */
    public function getBestMatches(Collection $technicians, Ticket $ticket, int $limit = 3): Collection
    {
        $sorted = $this->sortTechniciansBySkillMatch($technicians, $ticket);
        return $sorted->take($limit);
    }
}
