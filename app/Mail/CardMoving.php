<?php

namespace App\Mail;

use App\Models\Card;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CardMoving extends Mailable
{
    use Queueable, SerializesModels;

    protected Card $card;
    protected $newColumnName;
    protected $actingUserEmail;
    protected $cardTitleBeforeUpdate;

    public function __construct(
        Card $card,
        $newColumnName,
        $actingUserEmail,
        $cardTitleBeforeUpdate
    ) {
        $this->card = $card;
        $this->actingUserEmail = $actingUserEmail;
        $this->newColumnName = $newColumnName;
        $this->cardTitleBeforeUpdate = $cardTitleBeforeUpdate;
        $this->afterCommit();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your card was moved',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'CardMovingView',
            with: [
                'cardTitle' => $this->card->title,
                'columnTitle' => $this->newColumnName,
                'userEmail' => $this->actingUserEmail,
                'creatorName' => $this->card->user->name,
                'cardTitleBeforeUpdate' => $this->cardTitleBeforeUpdate,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

}
