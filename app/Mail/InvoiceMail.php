<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking) {}

    public function envelope(): Envelope
    {
        $brand = $this->booking->brand;
        return new Envelope(
            subject: "Invoice {$this->booking->invoice_no} · Booking {$this->booking->ref} · {$brand->name}",
            replyTo: $brand->email ? [new Address($brand->email, $brand->name)] : [],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'bookings.invoice', with: ['booking' => $this->booking, 'company' => $this->booking->company, 'print' => false]);
    }
}
