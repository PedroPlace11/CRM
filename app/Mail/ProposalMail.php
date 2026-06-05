<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProposalMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $mailSubject;
    public string $mailBody;
    public string $attachmentPath;
    public string $attachmentName;

    public function __construct(
        string $subject,
        string $body,
        string $attachmentPath,
        string $attachmentName,
    ) {
        $this->mailSubject = $subject;
        $this->mailBody = $body;
        $this->attachmentPath = $attachmentPath;
        $this->attachmentName = $attachmentName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->mailSubject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.proposal', with: ['body' => $this->mailBody]);
    }

    /** @return array<int, Attachment> */
    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->attachmentPath)->as($this->attachmentName),
        ];
    }
}
