<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;

class LeaveService
{
    public function createRequest(User $user, array $data)
    {
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);
        
        $daysRequested = $startDate->diffInDays($endDate) + 1;

        if ($endDate->lessThan($startDate)) {
            throw new Exception("Tanggal selesai tidak boleh sebelum tanggal mulai.");
        }

        if ($user->leave_quota < $daysRequested) {
            throw new Exception("Sisa kuota cuti tidak mencukupi. Sisa: {$user->leave_quota}, Diminta: {$daysRequested}");
        }

        return DB::transaction(function () use ($user, $data, $daysRequested) {
            $attachmentPath = null;
            if (isset($data['attachment'])) {
                $attachmentPath = $data['attachment']->store('attachments', 'public');
            }

            $leaveRequest = LeaveRequest::create([
                'user_id' => $user->id,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'reason' => $data['reason'],
                'attachment' => $attachmentPath,
                'status' => 'pending'
            ]);

            $user->decrement('leave_quota', $daysRequested);

            return $leaveRequest;
        });
    }

    public function processApproval($requestId, $status, $rejectionReason = null)
    {
        $leaveRequest = LeaveRequest::findOrFail($requestId);

        if ($leaveRequest->status !== 'pending') {
            throw new Exception("Pengajuan ini sudah diproses sebelumnya.");
        }

        return DB::transaction(function () use ($leaveRequest, $status, $rejectionReason) {
            
            if ($status === 'rejected') {
                $startDate = Carbon::parse($leaveRequest->start_date);
                $endDate = Carbon::parse($leaveRequest->end_date);
                $days = $startDate->diffInDays($endDate) + 1;

                $leaveRequest->user->increment('leave_quota', $days);
                $leaveRequest->rejection_reason = $rejectionReason;
            }

            $leaveRequest->status = $status;
            $leaveRequest->save();

            return $leaveRequest;
        });
    }
}