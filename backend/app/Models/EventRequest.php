<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'date',
        'time',
        'location',
        'justification',
        'expected_attendees',
        'budget',
        'resources',
        'requested_by',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
        'dean_approved_by',
        'dean_approved_at',
        'chair_approved_by',
        'chair_approved_at',
        'required_approvers',
        'all_approvals_received'
    ];

    protected $casts = [
        'date' => 'date',
        'time' => 'datetime:H:i',
        'reviewed_at' => 'datetime',
        'dean_approved_at' => 'datetime',
        'chair_approved_at' => 'datetime',
        'required_approvers' => 'array',
        'all_approvals_received' => 'boolean'
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function deanApprover()
    {
        return $this->belongsTo(User::class, 'dean_approved_by');
    }

    public function chairApprover()
    {
        return $this->belongsTo(User::class, 'chair_approved_by');
    }

    public function event()
    {
        return $this->hasOne(Event::class, 'approved_request_id');
    }

    /**
     * Check if all required approvals have been received
     */
    public function checkAllApprovalsReceived()
    {
        if (!$this->required_approvers || empty($this->required_approvers)) {
            return false;
        }

        $approvedBy = array_filter([
            $this->dean_approved_by,
            $this->chair_approved_by
        ]);

        // Check if all required approvers have approved
        $allApproved = true;
        foreach ($this->required_approvers as $requiredApproverId) {
            if (!in_array($requiredApproverId, $approvedBy)) {
                $allApproved = false;
                break;
            }
        }

        return $allApproved;
    }
}