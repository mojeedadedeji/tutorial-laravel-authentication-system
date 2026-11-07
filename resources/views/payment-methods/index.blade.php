@extends('layouts.app')

@section('title', 'Payment methods - ' . config('app.name'))

@section('content')
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card">
                <h2>Saved payment methods</h2>

                @if (session('status'))
                    <div class="alert alert-success" role="alert">{{ session('status') }}</div>
                @endif

                @if ($paymentMethods->isEmpty())
                    <p class="muted">No payment methods saved yet. Add one using the form.</p>
                @else
                    <ul class="list-unstyled" style="display: grid; gap: 0.75rem;">
                        @foreach ($paymentMethods as $method)
                            <li class="d-flex align-items-center justify-content-between" style="border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 0.875rem 1rem;">
                                <div>
                                    <div class="fw-semibold">
                                        @if ($method->type === 'paystack') Paystack
                                        @elseif ($method->type === 'opay') OPay
                                        @else {{ ucfirst($method->brand) }} •••• {{ $method->last4 }}
                                        @endif
                                        @if ($method->is_default)
                                            <span class="badge bg-primary ms-1">Default</span>
                                        @endif
                                    </div>
                                    <div class="small muted">
                                        {{ $method->cardholder_name }}
                                        @if ($method->type === 'card')
                                            · Expires {{ str_pad($method->expiry_month, 2, '0', STR_PAD_LEFT) }}/{{ $method->expiry_year }}
                                        @else
                                            · {{ ucfirst($method->type) }} account
                                        @endif
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('payment-methods.destroy', $method) }}" onsubmit="return confirm('Remove this payment method?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn" style="background: #dc2626; padding: 0.4rem 0.9rem; font-size: 0.8125rem;">Remove</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <h2>Add payment method</h2>
                <form method="POST" action="{{ route('payment-methods.store') }}">
                    @csrf

                    <div class="field">
                        <label for="type">Payment type</label>
                        <select id="type" name="type" required>
                            <option value="card" @selected(old('type', 'card') === 'card')>Debit / Credit card</option>
                            <option value="paystack" @selected(old('type') === 'paystack')>Paystack</option>
                            <option value="opay" @selected(old('type') === 'opay')>OPay</option>
                        </select>
                        @error('type')<div class="text-danger" style="font-size: 0.8125rem; margin-top: 0.25rem;">{{ $message }}</div>@enderror
                    </div>

                    <div class="field" id="card-fields">
                        <label for="brand">Card brand</label>
                        <select id="brand" name="brand" required>
                            <option value="visa" @selected(old('brand') === 'visa')>Visa</option>
                            <option value="mastercard" @selected(old('brand') === 'mastercard')>Mastercard</option>
                            <option value="amex" @selected(old('brand') === 'amex')>American Express</option>
                            <option value="verve" @selected(old('brand') === 'verve')>Verve</option>
                            <option value="other" @selected(old('brand') === 'other')>Other</option>
                        </select>
                        @error('brand')<div class="text-danger" style="font-size: 0.8125rem; margin-top: 0.25rem;">{{ $message }}</div>@enderror
                    </div>

                    <div class="field">
                        <label for="cardholder_name">Name on card</label>
                        <input id="cardholder_name" type="text" name="cardholder_name" value="{{ old('cardholder_name') }}" required autocomplete="cc-name">
                        @error('cardholder_name')<div class="text-danger" style="font-size: 0.8125rem; margin-top: 0.25rem;">{{ $message }}</div>@enderror
                    </div>

                    <div class="field" id="card-number-field">
                        <label for="card_number">Card number</label>
                        <input id="card_number" type="text" name="card_number" value="{{ old('card_number') }}" inputmode="numeric" autocomplete="cc-number" placeholder="4242 4242 4242 4242" maxlength="23">
                        <div class="muted" style="font-size: 0.75rem; margin-top: 0.25rem;">Only the last 4 digits are stored.</div>
                        @error('card_number')<div class="text-danger" style="font-size: 0.8125rem; margin-top: 0.25rem;">{{ $message }}</div>@enderror
                    </div>

                    <div class="field" id="card-expiry-field">
                        <label>Expiry date</label>
                        <div class="d-flex gap-2">
                            <select name="expiry_month" required aria-label="Expiry month" style="flex: 1;">
                                <option value="">Month</option>
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" @selected((string) old('expiry_month') === (string) $m)>{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                                @endfor
                            </select>
                            <select name="expiry_year" required aria-label="Expiry year" style="flex: 1;">
                                <option value="">Year</option>
                                @for ($y = (int) date('Y'); $y <= (int) date('Y') + 20; $y++)
                                    <option value="{{ $y }}" @selected((string) old('expiry_year') === (string) $y)>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        @error('expiry_month')<div class="text-danger" style="font-size: 0.8125rem; margin-top: 0.25rem;">{{ $message }}</div>@enderror
                        @error('expiry_year')<div class="text-danger" style="font-size: 0.8125rem; margin-top: 0.25rem;">{{ $message }}</div>@enderror
                    </div>

                    <div class="field">
                        <label class="d-flex align-items-center gap-2" style="font-weight: 500;">
                            <input type="checkbox" name="is_default" value="1" @checked(old('is_default')) style="width: auto;">
                            Set as default payment method
                        </label>
                    </div>

                    <button type="submit" class="btn">Save payment method</button>
                </form>
            </div>
        </div>
    </div>

    <script>
    (function () {
        var typeSelect = document.getElementById('type');
        var cardFields = ['card-fields', 'card-number-field', 'card-expiry-field'];

        function toggleCardFields() {
            var isCard = typeSelect.value === 'card';
            cardFields.forEach(function (id) {
                var el = document.getElementById(id);
                if (el) {
                    el.style.display = isCard ? '' : 'none';
                    el.querySelectorAll('input, select').forEach(function (input) {
                        input.required = isCard && input.name !== 'is_default';
                    });
                }
            });
            var brandSelect = document.getElementById('brand');
            if (brandSelect) brandSelect.required = isCard;
        }

        typeSelect.addEventListener('change', toggleCardFields);
        toggleCardFields();
    })();
    </script>
@endsection
