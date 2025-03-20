<?php

namespace App\Http\Controllers;

use App\Http\Requests\SkipRequest;
use App\Models\Skip;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;
use SplTempFileObject;

class SkipController extends Controller
{
    public function index(Request $request) {
        if (!auth()->user()->hasRole(['admin', 'dean', 'teacher'])) {
            return response()->json(['message' => 'Access is forbidden.'], 403);
        }

        $query = Skip::with('user');
        $query = $this->applyFilters($query, $request);

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $skips = $query->paginate($perPage, ['*'], 'page', $page);

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

            $reason = $request->has('reason') ? $request->input('reason') : null;

            $startDate = $this->parseDate($request->start_date);
            $endDate = $this->parseDate($request->end_date);

            $skip = Skip::create([
                'user_id' => Auth::user()->id,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
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
            'new_end_date' => [
                'nullable',
                'date',
                function ($attribute, $value, $fail) {
                    $formats = ['d.m.Y', 'Y-m-d'];
                    foreach ($formats as $format) {
                        if (Carbon::createFromFormat($format, $value) !== false) {
                            return;
                        }
                    }
                    $fail("Wrong format of date. Use d.m.Y or Y-m-d.");
                },
                function ($attribute, $value, $fail) {
                    $newEndDate = Carbon::createFromFormat('d.m.Y', $value);
                    $today = Carbon::today();

                    if ($newEndDate->lt($today)) {
                        $fail("The new end date must be today or later.");
                    }
                },
            ],
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimetypes:text/plain,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/jpeg,image/png|max:2048',
            'reason' => 'nullable|string',
        ]);

        $documentPaths = $skip->document_paths ? json_decode($skip->document_paths, true) : [];
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $documentPaths[] = $file->store('documents', 'public');
            }
        }

        $reason = $request->has('reason') ? $request->input('reason') : null;

        $newEndDate = null;
        if ($request->new_end_date) {
            $newEndDate = $this->parseDate($request->new_end_date)->format('Y-m-d');
        }

        $skip->update([
            'end_date' => $newEndDate,
            'status' => 'pending',
            'is_extended' => true,
            'document_paths' => json_encode($documentPaths),
            'reason' => $reason,
        ]);

        return response()->json([
            'message' => 'Request for extending was sent',
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

    public function exportSkipsToCsv(Request $request) {
        if (!auth()->user()->hasRole(['admin', 'dean', 'teacher'])) {
            return response()->json(['message' => 'Access is forbidden.'], 403);
        }
        $query = Skip::with('user');
        $query = $this->applyFilters($query, $request);

        $skips = $query->get();

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

        $fileName = 'skips_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $filePath = 'public/documents/' . $fileName;

        Storage::put($filePath, $csv->toString());

        $fileUrl = Storage::url($filePath);

        return response()->json([
            'message' => 'CSV file has been generated and saved.',
            'file_url' => $fileUrl,
        ], 200);

//        $csv->output('skips.csv');
//        exit;
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

    public function getMyFilteredSkips(Request $request) {
        $user = Auth::user();
        $query = Skip::with('user')->where('user_id', $user->id);
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);


        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('start_date') || $request->has('end_date')) {
            if ($request->has('start_date') && $request->has('end_date')) {
                $startDate = Carbon::createFromFormat('d.m.Y', $request->start_date)->startOfDay();
                $endDate = Carbon::createFromFormat('d.m.Y', $request->end_date)->endOfDay();

                $query->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function ($q) use ($startDate, $endDate) {
                            $q->where('start_date', '<=', $startDate)
                                ->where('end_date', '>=', $endDate);
                        });
                });
            } elseif ($request->has('start_date')) {
                $startDate = Carbon::createFromFormat('d.m.Y', $request->start_date)->startOfDay();
                $query->where('start_date', '>=', $startDate);
            } elseif ($request->has('end_date')) {
                $endDate = Carbon::createFromFormat('d.m.Y', $request->end_date)->endOfDay();
                $query->where('end_date', '<=', $endDate);
            }
        }

        if ($request->has('is_indefinite')) {
            $query->whereNull('end_date');
        }

        if ($request->has('reason')) {
            $query->where('reason', 'like', '%' . $request->input('reason') . '%');
        }

        $skips = $query->paginate($perPage, ['*'], 'page', $page);

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

    /**
     * Парсит дату из строки с учётом нескольких форматов.
     *
     * @param string $date
     * @return Carbon
     * @throws \Exception
     */
    private function parseDate(string $date): Carbon
    {
        $formats = ['d.m.Y', 'Y-m-d'];
        foreach ($formats as $format) {
            $parsedDate = Carbon::createFromFormat($format, $date);
            if ($parsedDate !== false) {
                return $parsedDate;
            }
        }
        throw new \Exception("Wrong format of date. Use d.m.Y or Y-m-d.");
    }

    private function applyFilters($query, Request $request)
    {
        if ($request->has('student_name')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->student_name . '%');
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('start_date') || $request->has('end_date')) {
            if ($request->has('start_date') && $request->has('end_date')) {
                $startDate = Carbon::createFromFormat('d.m.Y', $request->start_date)->startOfDay();
                $endDate = Carbon::createFromFormat('d.m.Y', $request->end_date)->endOfDay();

                $query->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function ($q) use ($startDate, $endDate) {
                            $q->where('start_date', '<=', $startDate)
                                ->where('end_date', '>=', $endDate);
                        });
                });
            } elseif ($request->has('start_date')) {
                $startDate = Carbon::createFromFormat('d.m.Y', $request->start_date)->startOfDay();
                $query->where('start_date', '>=', $startDate);
            } elseif ($request->has('end_date')) {
                $endDate = Carbon::createFromFormat('d.m.Y', $request->end_date)->endOfDay();
                $query->where('end_date', '<=', $endDate);
            }
        }

        if ($request->has('is_indefinite')) {
            $query->whereNull('end_date');
        }

        if ($request->has('reason')) {
            $query->where('reason', 'like', '%' . $request->input('reason') . '%');
        }

        return $query;
    }

}
