<?php

namespace App\Http\Controllers;

use App\Services\LeaveService;
use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Auth;

class LeaveController extends Controller
{
    protected $leaveService;
    public function __construct(LeaveService $leaveService)
    {
        $this->leaveService = $leaveService;
    }

    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            $data = LeaveRequest::with('user')->orderBy('created_at', 'desc')->get();
        } else {
            $data = LeaveRequest::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return response()->json(['data' => $data]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'reason'     => 'required|string',
            'attachment' => 'required|file|mimes:jpg,png,pdf|max:2048'
        ]);

        try {
            $leave = $this->leaveService->createRequest($request->user(), $request->all());
            
            return response()->json([
                'message' => 'Cuti berhasil diajukan.', 
                'data' => $leave
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'rejection_reason' => 'required_if:status,rejected'
        ]);

        try {
            $leave = $this->leaveService->processApproval(
                $id, 
                $request->status, 
                $request->rejection_reason
            );

            return response()->json([
                'message' => 'Status pengajuan berhasil diperbarui.',
                'data' => $leave
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}