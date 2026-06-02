<?php

namespace App\Http\Controllers;

use App\Mail\ProposalMail;
use App\Models\Deal;
use App\Models\DealEmail;
use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ProposalController extends Controller
{
    public function store(Request $request, Deal $deal)
    {
        Gate::authorize('update', $deal);

        $request->validate([
            'file'  => ['required', 'file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,png'],
            'title' => ['nullable', 'string', 'max:191'],
        ]);

        $file = $request->file('file');
        $path = $file->store("deals/{$deal->id}/proposals", 'local');

        $proposal = Proposal::create([
            'deal_id'     => $deal->id,
            'uploaded_by' => $request->user()->id,
            'title'       => $request->input('title') ?? $file->getClientOriginalName(),
            'file_path'   => $path,
            'file_name'   => $file->getClientOriginalName(),
            'mime_type'   => $file->getClientMimeType(),
            'size_bytes'  => $file->getSize(),
        ]);

        return back()->with('success', 'Proposta carregada.');
    }

    public function send(Request $request, Deal $deal, Proposal $proposal)
    {
        Gate::authorize('update', $deal);
        abort_unless($proposal->deal_id === $deal->id, 404);

        $data = $request->validate([
            'to_email' => ['required', 'email'],
            'subject'  => ['required', 'string', 'max:191'],
            'body'     => ['required', 'string'],
        ]);

        // Send email with proposal attached.
        Mail::to($data['to_email'])->send(new ProposalMail(
            subject: $data['subject'],
            body:    $data['body'],
            attachmentPath: Storage::disk('local')->path($proposal->file_path),
            attachmentName: $proposal->file_name,
        ));

        $proposal->update(['sent_at' => now()]);

        DealEmail::create([
            'deal_id'     => $deal->id,
            'proposal_id' => $proposal->id,
            'sent_by'     => $request->user()->id,
            'kind'        => 'proposal',
            'to_email'    => $data['to_email'],
            'subject'     => $data['subject'],
            'body'        => $data['body'],
            'sent_at'     => now(),
        ]);

        return back()->with('success', 'Proposta enviada ao cliente.');
    }

    public function download(Deal $deal, Proposal $proposal)
    {
        Gate::authorize('view', $deal);
        abort_unless($proposal->deal_id === $deal->id, 404);
        return Storage::disk('local')->download($proposal->file_path, $proposal->file_name);
    }

    public function destroy(Deal $deal, Proposal $proposal)
    {
        Gate::authorize('update', $deal);
        abort_unless($proposal->deal_id === $deal->id, 404);
        Storage::disk('local')->delete($proposal->file_path);
        $proposal->delete();
        return back();
    }
}
