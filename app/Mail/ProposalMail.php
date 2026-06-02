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

    public function __construct(
        public string $subject,
        public string $body,
        public string $attachmentPath,
        public string $attachmentName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.proposal', with: ['body' => $this->body]);
    }

    /** @return array<int, Attachment> */
    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->attachmentPath)->as($this->attachmentName),
        ];
    }
}
