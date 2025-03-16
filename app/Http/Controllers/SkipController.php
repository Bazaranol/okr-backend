<?php

namespace App\Http\Controllers;

use App\Models\Skip;
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

    public function store(Request $request){
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'document' => 'required|file|mimes:pdf,docx,doc|max:2048',
        ]);

        $documentPath = null;
        if($request->hasFile('document')){
            $documentPath = $request->file('document')->store('documents', 'public');
        }

        $skip = Skip::create([
            'user_id' => Auth::user()->id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
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
