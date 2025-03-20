<?php

namespace App\Http\Controllers;

use App\Http\Requests\SkipRequest;
use App\Models\Skip;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use League\Csv\Writer;
use SplTempFileObject;

class SkipController extends Controller
{
    public function index(Request $request) {
        if (!auth()->user()->hasRole(['admin', 'dean', 'teacher'])) {
            return response()->json(['message' => 'Access is forbidden.'], 403);
        }

        $query = Skip::with('user');

        if ($request->has('student_name')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->student_name . '%');
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date')) {
            $date = Carbon::createFromFormat('d.m.Y', $request->date)->format('Y-m-d');
            $query->where('start_date', '<=', $date)
                ->where(function ($q) use ($date) {
                    $q->where('end_date', '>=', $date)
                        ->orWhereNull('end_date'); // Бессрочные пропуски
                });
        }

        if ($request->has('is_indefinite')) {
            $query->whereNull('end_date');
        }

        $perPage = $request->input('per_page', 10);
        $skips = $query->paginate($perPage);

        return response()->json([
            'data' => $skips->items(),
            'pagination' => [
                'total' => $skips->total(),
                'per_page' => $skips->perPage(),
                'current_page' => $skips->currentPage(),
                'last_page' => $skips->lastPage(),
            ],
        ]);
    }

    public function delete(Request $request) {
        $request->validate([
            'skip_id' => 'required'
        ]);
        Skip::where('id', $request->input('skip_id'))->delete();

        return response()->json(['data' => 'Skip was deleted'], 200);
    }

    public function store(SkipRequest $request)
    {
        try {
            $documentPaths = [];
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $documentPaths[] = $file->store('documents', 'public');
                }
            }

            $reason = null;
            if ($request->has('reason')) {
                $reason = $request->input('reason');
            }

            $skip = Skip::create([
                'user_id' => Auth::user()->id,
                'start_date' => Carbon::createFromFormat('d.m.Y', $request->start_date)->format('Y-m-d'),
                'end_date' => Carbon::createFromFormat('d.m.Y', $request->end_date)->format('Y-m-d'),
                'document_paths' => json_encode($documentPaths),
                'reason' => $reason,
            ]);

            return response()->json(['message' => 'Skip was created!', 'data' => $skip], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error:', 'error' => $e->getMessage()], 500);
        }
    }

    public function extend(Request $request, Skip $skip) {
        if ($skip->status !== 'approved') {
            return response()->json(['message' => 'Skip must be approved for extending'], 400);
        }

        $request->validate([
            'new_end_date' => 'nullable|date|date_format:d.m.Y|after:today',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimetypes:text/plain,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document|max:2048',
            'reason' => 'nullable|string',
        ]);

        $documentPaths = $skip->document_paths ?? [];
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $documentPaths[] = $file->store('documents', 'public');
            }
        }

        $reason  = $request->has('reason') ?? null;

        $skip->update([
            'end_date' => $request->new_end_date ? Carbon::createFromFormat('d.m.Y', $request->new_end_date)->format('Y-m-d') : null,
            'status' => 'pending',
            'is_extended' => true,
            'document_paths' => $documentPaths,
            'reason' => $reason,
        ]);

        return response()->json([
            'message' => 'Request foe extending was send',
            'data' => $skip
        ], 200);
    }

    public function updateStatus(Request $request, Skip $skip){
        if (!auth()->user()->hasRole(['admin', 'dean'])) {
            return response()->json(['message' => 'Access is forbidden.'], 403);
        }

        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $skip->update(['status' => $request->status]);

        return response()->json(['message' => 'Status was updated!', 'data' => $skip], 201);
    }

    public function exportSkipsToCsv() {
        $skips = Skip::with('user')->get();

        $csv = Writer::createFromFileObject(new SplTempFileObject());
        $csv->insertOne(['ID', 'User ID', 'User name', 'From', 'To', 'Document Paths', 'Reason']);

        foreach ($skips as $skip) {
            $csv->insertOne([
                $skip->id,
                $skip->user->id,
                $skip->user->name,
                $skip->start_date,
                $skip->end_date,
                $skip->document_paths,
                $skip->reason ?? 'Without reason',
            ]);
        }

        $csv->output('skips.csv');
        exit;
    }

    public function getByIdSkip($skipId) {
        $user = auth()->user();

        $skip = Skip::with('user')->find($skipId);
        if (!$skip) {
            return response()->json(['message' => 'Skip is not found.'], 404);
        }
        if ($user->hasRole(['admin', 'dean', 'teacher'])) {
            return response()->json(['data' => $skip], 200);
        } elseif ($user->hasRole('student')) {
            if ($skip->user_id === $user->id) {
                return response()->json(['data' => $skip], 200);
            } else {
                return response()->json(['message' => 'Access is forbidden.'], 403);
            }
        } else {
            return response()->json(['message' => 'Access is forbidden.'], 403);
        }
    }

    public function getMySkips() {
        $user = Auth::user();
        return response()->json(['data' => $user->skips()->get()], 200);
    }
}
