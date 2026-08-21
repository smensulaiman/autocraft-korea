<?php

namespace App\Mail;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VehicleInquiryReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Inquiry $inquiry)
    {
        $this->inquiry->loadMissing('vehicle');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New inquiry: '.$this->inquiry->vehicle->title(),
            cc: array_map(fn (string $email) => new Address($email), config('services.sales.cc_emails', [])),
            replyTo: [new Address($this->inquiry->email, $this->inquiry->name)],
        );
    }

    public function content(): Content
    {
        $vehicle = $this->inquiry->vehicle;

        return new Content(
            view: 'emails.vehicle-inquiry',
            with: [
                'vehicle' => $vehicle,
                'inquiry' => $this->inquiry,
                'vehicleUrl' => route('vehicles.show', $vehicle),
                'photos' => array_slice($vehicle->photoUrls(), 0, 4),
                'specs' => array_filter([
                    'Mileage' => $vehicle->mileage !== null ? number_format($vehicle->mileage).' km' : null,
                    'Engine' => $vehicle->engine_cc ? number_format($vehicle->engine_cc).' cc' : null,
                    'Fuel' => $vehicle->fuel_type && $vehicle->fuel_type !== 'unknown' ? ucfirst($vehicle->fuel_type) : null,
                    'Transmission' => $vehicle->transmission && $vehicle->transmission !== 'unknown' ? ucfirst($vehicle->transmission) : null,
                    'Drivetrain' => $vehicle->drive_type ? strtoupper($vehicle->drive_type) : null,
                    'Body' => $vehicle->body_type && $vehicle->body_type !== 'unknown' ? ucfirst($vehicle->body_type) : null,
                    'Color' => $vehicle->color ? ucfirst($vehicle->color) : null,
                ]),
            ],
        );
    }
}
