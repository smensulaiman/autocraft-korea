<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Privacy Policy — AutoCraft Korea')] class extends Component
{
    //
};
?>

<div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 sm:py-14">

    {{-- Header --}}
    <div class="border-b border-line pb-6">
        <p class="microlabel flex items-center gap-1.5">
            <span class="inline-block size-1.5 bg-signal"></span>
            Legal
        </p>
        <h1 class="mt-1 text-3xl font-black tracking-tight uppercase sm:text-5xl" style="font-stretch: 115%;">
            Privacy Policy
        </h1>
        <p class="mt-2 font-mono text-[11px] tracking-widest text-ink/50 uppercase tabular-nums">
            Last updated {{ now()->format('F j, Y') }}
        </p>
    </div>

    <div class="mt-8 max-w-3xl space-y-6">

            <p class="border border-line bg-white p-5 text-[15px] leading-relaxed text-ink/80 text-justify [hyphens:auto] sm:p-6">
                AutoCraft Korea ("we", "us", "our") operates this website to help buyers outside Korea find and
                inquire about vehicles held by Korean dealers. This policy explains what listing data we display,
                what personal information we collect from you, and how it is used.
            </p>

            <section id="listings" class="scroll-mt-24 border border-line bg-white p-5 sm:p-6">
                <h2 class="flex items-center gap-2 text-lg font-black tracking-tight uppercase text-ink">
                    <span class="grid size-6 shrink-0 place-items-center bg-ink font-mono text-[11px] font-semibold text-white">1</span>
                    About the vehicle listings
                </h2>
                <div class="mt-3 space-y-3 text-[15px] leading-relaxed text-ink/80 text-justify [hyphens:auto]">
                    <p>
                        The vehicles shown on this site are <strong class="font-bold text-ink">not our own stock</strong>.
                        Listing data — photos, specifications, pricing, and availability — is fetched from the
                        public vehicle data API at <a href="https://demo.carapis.com/" target="_blank" rel="noopener" class="font-semibold text-ink underline decoration-signal/50 underline-offset-2 hover:text-signal">demo.carapis.com</a>,
                        a third-party marketplace source, and reflects inventory held by independent Korean
                        dealers. We do not own, hold, or store any of these vehicles ourselves, and we cannot
                        guarantee that a listing is still available at the moment you view it, since dealer stock
                        changes outside of our control.
                    </p>
                    <p>
                        Prices displayed include a markup over the price supplied by the API, which represents our
                        margin for sourcing and facilitating the transaction on your behalf.
                    </p>
                </div>
            </section>

            <section id="inquiries" class="scroll-mt-24 border border-line bg-white p-5 sm:p-6">
                <h2 class="flex items-center gap-2 text-lg font-black tracking-tight uppercase text-ink">
                    <span class="grid size-6 shrink-0 place-items-center bg-ink font-mono text-[11px] font-semibold text-white">2</span>
                    How an inquiry is handled
                </h2>
                <p class="mt-3 text-[15px] leading-relaxed text-ink/80 text-justify [hyphens:auto]">
                    When you submit an inquiry about a vehicle, we do not sell or ship the car to you directly.
                    Instead, one of our sales agents contacts the Korean dealer who holds that vehicle on your
                    behalf to confirm availability, pricing, and export details, and then follows up with you
                    directly. Because dealer stock can change quickly, we recommend treating any listed price and
                    availability as indicative until our sales agent confirms it with the dealer.
                </p>
            </section>

            <section id="collect" class="scroll-mt-24 border border-line bg-white p-5 sm:p-6">
                <h2 class="flex items-center gap-2 text-lg font-black tracking-tight uppercase text-ink">
                    <span class="grid size-6 shrink-0 place-items-center bg-ink font-mono text-[11px] font-semibold text-white">3</span>
                    Information we collect
                </h2>
                <p class="mt-3 text-[15px] leading-relaxed text-ink/80 text-justify [hyphens:auto]">
                    When you submit a vehicle inquiry through this site, we collect:
                </p>
                <ul class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                    @foreach (['Your name', 'Your email address', 'Your phone number (if provided)', 'Any message you include, and the vehicle you inquired about'] as $item)
                        <li class="flex items-start gap-2 border border-line bg-paper px-3 py-2 text-[14px] text-ink/80">
                            <span class="mt-1.5 inline-block size-1.5 shrink-0 bg-signal"></span>
                            {{ $item }}
                        </li>
                    @endforeach
                </ul>
                <p class="mt-3 text-[15px] leading-relaxed text-ink/80 text-justify [hyphens:auto]">
                    We also automatically collect standard technical data (such as browser type and IP address)
                    through normal web server logging, used only for security and troubleshooting purposes.
                </p>
            </section>

            <section id="use" class="scroll-mt-24 border border-line bg-white p-5 sm:p-6">
                <h2 class="flex items-center gap-2 text-lg font-black tracking-tight uppercase text-ink">
                    <span class="grid size-6 shrink-0 place-items-center bg-ink font-mono text-[11px] font-semibold text-white">4</span>
                    How we use your information
                </h2>
                <p class="mt-3 text-[15px] leading-relaxed text-ink/80 text-justify [hyphens:auto]">
                    Your inquiry details are used to:
                </p>
                <ul class="mt-3 space-y-2">
                    @foreach ([
                        'Let our sales agent contact the relevant Korean dealer about the vehicle you asked about',
                        'Let our sales agent follow up with you regarding pricing, availability, and export logistics',
                        'Respond to any other questions you raise with us directly',
                    ] as $item)
                        <li class="flex items-start gap-2 text-[15px] leading-relaxed text-ink/80">
                            <span class="mt-2 inline-block size-1.5 shrink-0 bg-signal"></span>
                            <span class="text-justify [hyphens:auto]">{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>
                <p class="mt-3 text-[15px] leading-relaxed text-ink/80 text-justify [hyphens:auto]">
                    We do not sell your personal information. It is shared only with the specific dealer(s)
                    relevant to your inquiry, and is not passed to unrelated third parties.
                </p>
            </section>

            <section id="retention" class="scroll-mt-24 border border-line bg-white p-5 sm:p-6">
                <h2 class="flex items-center gap-2 text-lg font-black tracking-tight uppercase text-ink">
                    <span class="grid size-6 shrink-0 place-items-center bg-ink font-mono text-[11px] font-semibold text-white">5</span>
                    Data retention
                </h2>
                <p class="mt-3 text-[15px] leading-relaxed text-ink/80 text-justify [hyphens:auto]">
                    We retain inquiry records for as long as reasonably necessary to handle your request and
                    maintain a record of past communications, after which they may be deleted.
                </p>
            </section>

            <section id="rights" class="scroll-mt-24 border border-line bg-white p-5 sm:p-6">
                <h2 class="flex items-center gap-2 text-lg font-black tracking-tight uppercase text-ink">
                    <span class="grid size-6 shrink-0 place-items-center bg-ink font-mono text-[11px] font-semibold text-white">6</span>
                    Your rights
                </h2>
                <p class="mt-3 text-[15px] leading-relaxed text-ink/80 text-justify [hyphens:auto]">
                    You may ask us to access, correct, or delete the personal information we hold about you at any
                    time by contacting us using the details below.
                </p>
            </section>

            <section id="contact" class="scroll-mt-24 border border-line bg-ink p-5 text-white sm:p-6">
                <h2 class="flex items-center gap-2 text-lg font-black tracking-tight uppercase">
                    <span class="grid size-6 shrink-0 place-items-center bg-signal font-mono text-[11px] font-semibold text-white">7</span>
                    Contact us
                </h2>
                <p class="mt-3 text-[15px] leading-relaxed text-white/75 text-justify [hyphens:auto]">
                    If you have questions about this policy or how your information is handled, please reach out
                    through the contact options on this site.
                </p>
            </section>

            <a href="{{ route('vehicles.index') }}"
               class="inline-flex items-center gap-2 pt-2 font-mono text-[11px] tracking-widest text-ink/60 uppercase transition hover:text-signal">
                &larr; Back to stock
            </a>
    </div>
</div>
