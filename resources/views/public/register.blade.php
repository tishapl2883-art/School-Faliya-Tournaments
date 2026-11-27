@extends('layouts.app')
@section('title', 'Player Registration')
@section('content')
<h1 class="title">Player Registration</h1>
@if ($site['registration_open'] !== '1')
    <div class="card empty">Registration is closed. Please contact the organiser.</div>
@else
<form class="card form" method="post" action="{{ route('register') }}" enctype="multipart/form-data" data-once>
    @csrf
    <label class="full">Full name *<input name="name" value="{{ old('name') }}" maxlength="100" required></label>
    <label>Mobile number *<input name="mobile" value="{{ old('mobile') }}" inputmode="numeric" pattern="[6-9][0-9]{9}" maxlength="10" required></label>
    <label>Date of birth *<input type="date" name="dob" value="{{ old('dob') }}" required></label>
    <label class="full">Village / Faliya *<input name="village" value="{{ old('village') }}" maxlength="100" required></label>
    @foreach (['role' => ['Playing role', \App\Models\Player::ROLES], 'batting_style' => ['Batting style', \App\Models\Player::BATTING], 'bowling_style' => ['Bowling style', \App\Models\Player::BOWLING], 'tshirt_size' => ['T-shirt size', \App\Models\Player::SIZES]] as $field => [$label, $options])
        <label>{{ $label }} *
            <select name="{{ $field }}" required>
                <option value="">Select</option>
                @foreach ($options as $o)<option @selected(old($field) === $o)>{{ $o }}</option>@endforeach
            </select>
        </label>
    @endforeach
    <label>Name on jersey<input name="jersey_name" value="{{ old('jersey_name') }}" maxlength="20"></label>
    <label>Jersey number<input type="number" name="jersey_number" value="{{ old('jersey_number') }}" min="0" max="999"></label>
    <label class="full">Player photo * (JPG, PNG or WEBP, up to 4 MB)<input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required></label>
    @if ((int) $site['registration_fee'] > 0)
        @php
            $upiParams = $site['upi_id'] ? http_build_query([
                'pa' => $site['upi_id'],
                'pn' => 'JAYMAL BAKULBHAI PATEL',
                'am' => (int) $site['registration_fee'],
                'cu' => 'INR',
                'mc' => '0000',
                'mode' => '02',
                'purpose' => '00',
                'tn' => $site['title'].' Registration',
            ], '', '&', PHP_QUERY_RFC3986) : '';
            $phonepeUrl = $upiParams ? 'phonepe://pay?'.$upiParams : '';
            $androidPhonepeIntent = $upiParams ? 'intent://pay?'.$upiParams.'#Intent;scheme=upi;package=com.phonepe.app;end' : '';
            $upiUrl = $upiParams ? 'upi://pay?'.$upiParams : '';
        @endphp
        <div class="full pay" style="text-align:center">
            <strong style="font-size:17px">Registration fee: &#8377;{{ $site['registration_fee'] }}</strong>
            @if (! empty($site['payment_qr']))
                @php $qrSrc = str_starts_with($site['payment_qr'], 'images/') ? asset($site['payment_qr']) : asset('storage/'.$site['payment_qr']); @endphp
                <div style="margin:10px auto">
                    @if ($phonepeUrl)
                        <a href="{{ $phonepeUrl }}" data-phonepe-intent="{{ $androidPhonepeIntent }}" title="Tap QR to pay &#8377;{{ $site['registration_fee'] }} in PhonePe" style="display:inline-block">
                            <img src="{{ $qrSrc }}" alt="Tap to pay via PhonePe QR" style="width:240px;max-width:100%;border-radius:12px;border:2px solid var(--gold);background:#fff;cursor:pointer">
                        </a>
                    @else
                        <img src="{{ $qrSrc }}" alt="Payment QR Code" style="width:240px;max-width:100%;border-radius:12px;border:2px solid var(--gold);background:#fff">
                    @endif
                </div>
                <span style="font-size:13px">Tap the QR code or button below to open <strong>PhonePe</strong> for &#8377;{{ $site['registration_fee'] }}.</span>
            @endif
            @if ($upiParams)
                <div class="actions" style="justify-content:center;margin:10px 0 6px">
                    <a class="btn small" href="{{ $phonepeUrl }}" data-phonepe-intent="{{ $androidPhonepeIntent }}" style="background:#5f259f;color:#fff">Pay &#8377;{{ $site['registration_fee'] }} in PhonePe</a>
                    <a class="btn small gold" href="{{ $upiUrl }}">Other UPI App</a>
                    @if (! empty($qrSrc))
                        <a class="btn small" href="{{ $qrSrc }}" download="parnera-phonepe-qr.jpg">Save QR</a>
                    @endif
                    <button type="button" class="btn small" id="copy-upi" data-upi="{{ $site['upi_id'] }}">Copy UPI ID</button>
                </div>
                <span style="font-size:12px">UPI ID: <b>{{ $site['upi_id'] }}</b></span>
            @endif
        </div>
        @if ($upiParams)
            <script>
            (() => {
                if (/android/i.test(navigator.userAgent)) {
                    document.querySelectorAll('[data-phonepe-intent]').forEach((a) => {
                        a.setAttribute('href', a.dataset.phonepeIntent);
                    });
                }
                const copyBtn = document.getElementById('copy-upi');
                copyBtn?.addEventListener('click', async () => {
                    try {
                        await navigator.clipboard.writeText(copyBtn.dataset.upi);
                        copyBtn.textContent = 'Copied!';
                        setTimeout(() => { copyBtn.textContent = 'Copy UPI ID'; }, 1800);
                    } catch (e) {
                        prompt('Copy this UPI ID:', copyBtn.dataset.upi);
                    }
                });
            })();
            </script>
        @endif
        <label>UPI transaction ID *<input name="payment_ref" value="{{ old('payment_ref') }}" maxlength="60" required></label>
        <label>Payment screenshot *<input type="file" name="payment_proof" accept="image/jpeg,image/png,image/webp" required></label>
    @endif
    <label class="full check"><input type="checkbox" name="terms" value="1" required><span>I accept the <a href="{{ route('page', 'terms') }}" target="_blank">terms and conditions</a>.</span></label>
    <button class="btn gold full">Submit Registration</button>
</form>
@endif
@endsection
