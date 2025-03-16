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
    public function index() {
        $skips = Skip::with('user')->get();

        return response()->json(['data' => $skips]);
    }

    public function delete(Request $request) {
        $request->validate([
            'skip_id' => 'required'
        ]);
        Skip::where('id', $request->input('skip_id'))->delete();

        return response()->json(['data' => 'Skip was deleted'], 200);
    }

    public function store(SkipRequest $request){
        $documentPath = null;
        if ($request->hasFile('document')) {
            $documentPath = $request->file('document')->store('documents', 'public');
        }

        $skip = Skip::create([
            'user_id' => Auth::user()->id,
            'start_date' => Carbon::createFromFormat('d.m.Y', $request->start_date)->format('Y-m-d'),
            'end_date' => Carbon::createFromFormat('d.m.Y', $request->end_date)->format('Y-m-d'),
            'document_path' => $documentPath,
        ]);

        return response()->json(['message' => 'Skip was created!', 'data' => $skip], 201);
    }

    public function updateStatus(Request $request, Skip $skip){
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $skip->update(['status' => $request->status]);

        return response()->json(['message' => 'Status was updated!', 'data' => $skip], 201);
    }

    public function exportSkipsToCsv() {
        $skips = Skip::with('user')->get();

        $csv = Writer::createFromFileObject(new SplTempFileObject());
        $csv->insertOne(['ID', 'User ID', 'User name', 'From', 'To', 'Document Path']);

        foreach ($skips as $skip) {
            $csv->insertOne([
                $skip->id,
                $skip->user->id,
                $skip->user->name,
                $skip->start_date,
                $skip->end_date,
                $skip->document_path,
            ]);
        }

        $csv->output('skips.csv');
        exit;
    }
}
