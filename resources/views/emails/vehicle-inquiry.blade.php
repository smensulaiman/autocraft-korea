<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>New vehicle inquiry</title>
<style>
    .inquiry-message p { margin: 0 0 8px; }
    .inquiry-message p:last-child { margin-bottom: 0; }
    .inquiry-message ul, .inquiry-message ol { margin: 0 0 8px; padding-left: 20px; }
    .inquiry-message ul { list-style-type: disc; }
    .inquiry-message ol { list-style-type: decimal; }
</style>
</head>
<body style="margin:0; padding:0; background:#f2f0e9; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; color:#1b1b1b;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f2f0e9; padding:24px 12px;">
<tr>
<td align="center">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:600px; max-width:100%; background:#ffffff; border:1px solid #dcd8cc;">

    {{-- Header --}}
    <tr>
        <td style="background:#1b1b1b; padding:20px 28px;">
            <span style="font-family:'Courier New',monospace; font-size:11px; letter-spacing:.2em; text-transform:uppercase; color:#df3636;">New Website Inquiry</span>
            <h1 style="margin:4px 0 0; font-size:18px; font-weight:900; letter-spacing:.02em; text-transform:uppercase; color:#ffffff;">Autocraft Korea</h1>
        </td>
    </tr>

    {{-- Vehicle photo --}}
    @if ($vehicle->thumbUrl())
    <tr>
        <td>
            <img src="{{ $vehicle->thumbUrl() }}" alt="{{ $vehicle->title() }}" width="600" style="width:100%; max-width:600px; display:block; border-bottom:1px solid #dcd8cc;">
        </td>
    </tr>
    @endif

    {{-- Vehicle summary --}}
    <tr>
        <td style="padding:24px 28px 8px;">
            <h2 style="margin:0; font-size:20px; font-weight:900; letter-spacing:-.01em;">{{ $vehicle->title() }}</h2>
            <p style="margin:4px 0 0; font-size:12px; color:#6b6b6b;">
                Stock #{{ $vehicle->listing_id ?: \Illuminate\Support\Str::limit($vehicle->id, 8, '') }}
                @if ($vehicle->vin) &middot; VIN {{ $vehicle->vin }} @endif
            </p>
        </td>
    </tr>

    {{-- Price --}}
    <tr>
        <td style="padding:12px 28px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="font-size:26px; font-weight:900; color:#df3636;">
                        @if ($vehicle->displayPriceUsd())
                            ${{ number_format($vehicle->displayPriceUsd()) }}
                        @else
                            Price on request
                        @endif
                    </td>
                    @if ($vehicle->price_original)
                    <td align="right" style="font-size:12px; color:#6b6b6b;">
                        ₩{{ number_format((float) $vehicle->price_original) }} {{ $vehicle->price_original_currency }} in Korea
                    </td>
                    @endif
                </tr>
            </table>
        </td>
    </tr>

    {{-- Specs --}}
    @if (count($specs))
    <tr>
        <td style="padding:4px 28px 20px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid #dcd8cc; border-left:1px solid #dcd8cc;">
                @foreach (array_chunk($specs, 2, true) as $row)
                <tr>
                    @foreach ($row as $label => $value)
                    <td width="50%" style="padding:8px 10px; border-right:1px solid #dcd8cc; border-bottom:1px solid #dcd8cc;">
                        <span style="display:block; font-family:'Courier New',monospace; font-size:10px; letter-spacing:.1em; text-transform:uppercase; color:#8a8a8a;">{{ $label }}</span>
                        <span style="display:block; font-size:13px; font-weight:600;">{{ $value }}</span>
                    </td>
                    @endforeach
                    @if (count($row) === 1)
                    <td width="50%" style="border-bottom:1px solid #dcd8cc;"></td>
                    @endif
                </tr>
                @endforeach
            </table>
        </td>
    </tr>
    @endif

    {{-- Photo strip --}}
    @if (count($photos) > 1)
    <tr>
        <td style="padding:0 28px 20px;">
            <table role="presentation" cellpadding="0" cellspacing="0">
                <tr>
                    @foreach ($photos as $photo)
                    <td style="padding-right:6px;">
                        <img src="{{ $photo['thumb'] ?? $photo['url'] }}" alt="" width="90" height="68" style="width:90px; height:68px; object-fit:cover; display:block; border:1px solid #dcd8cc;">
                    </td>
                    @endforeach
                </tr>
            </table>
        </td>
    </tr>
    @endif

    {{-- Links --}}
    <tr>
        <td style="padding:0 28px 24px;">
            <table role="presentation" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="padding-right:10px;">
                        <a href="{{ $vehicleUrl }}" style="display:inline-block; background:#1b1b1b; color:#ffffff; font-family:'Courier New',monospace; font-size:11px; font-weight:700; letter-spacing:.15em; text-transform:uppercase; text-decoration:none; padding:12px 20px;">View Listing</a>
                    </td>
                    @if ($vehicle->listing_url)
                    <td>
                        <a href="{{ $vehicle->listing_url }}" style="display:inline-block; background:#ffffff; color:#1b1b1b; border:1px solid #1b1b1b; font-family:'Courier New',monospace; font-size:11px; font-weight:700; letter-spacing:.15em; text-transform:uppercase; text-decoration:none; padding:11px 20px;">Original Source</a>
                    </td>
                    @endif
                </tr>
            </table>
        </td>
    </tr>

    {{-- Divider --}}
    <tr><td style="border-top:1px solid #dcd8cc;"></td></tr>

    {{-- Inquiry details --}}
    <tr>
        <td style="padding:24px 28px;">
            <span style="display:block; font-family:'Courier New',monospace; font-size:11px; letter-spacing:.2em; text-transform:uppercase; color:#df3636; margin-bottom:10px;">Inquiry Details</span>

            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
                <tr>
                    <td width="80" style="padding:4px 0; color:#6b6b6b; vertical-align:top;">Name</td>
                    <td style="padding:4px 0; font-weight:600;">{{ $inquiry->name }}</td>
                </tr>
                <tr>
                    <td style="padding:4px 0; color:#6b6b6b; vertical-align:top;">Email</td>
                    <td style="padding:4px 0; font-weight:600;"><a href="mailto:{{ $inquiry->email }}" style="color:#1b1b1b;">{{ $inquiry->email }}</a></td>
                </tr>
                @if ($inquiry->phone)
                <tr>
                    <td style="padding:4px 0; color:#6b6b6b; vertical-align:top;">Phone</td>
                    <td style="padding:4px 0; font-weight:600;">{{ $inquiry->phone }}</td>
                </tr>
                @endif
                <tr>
                    <td style="padding:4px 0; color:#6b6b6b; vertical-align:top;">Sent</td>
                    <td style="padding:4px 0; font-weight:600;">{{ $inquiry->created_at?->format('M j, Y g:i A') }}</td>
                </tr>
            </table>

            @if ($inquiry->message)
            <div class="inquiry-message" style="margin-top:16px; padding:16px; background:#f2f0e9; border:1px solid #dcd8cc; font-size:14px; line-height:1.6;">
                {!! $inquiry->message !!}
            </div>
            @endif
        </td>
    </tr>

    {{-- Footer --}}
    <tr>
        <td style="padding:16px 28px 24px; border-top:1px solid #dcd8cc;">
            <p style="margin:0; font-size:11px; color:#8a8a8a;">Reply to this email to respond directly to {{ $inquiry->name }} at {{ $inquiry->email }}.</p>
        </td>
    </tr>

</table>
</td>
</tr>
</table>
</body>
</html>
