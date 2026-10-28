@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
        <div class="row justify-content-between align-items-center mb-10">
            <div class="col-12 col-lg-auto mb-20">
                <div class="page-heading">
                    <h3>Settings</h3>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success mb-20">
                <i class="fa fa-check-circle mr-2"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger mb-20">
                <i class="fa fa-exclamation-circle mr-2"></i> {{ session('error') }}
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="mb-10"><i class="zmdi zmdi-email mr-2"></i> New order emails</h4>
                        <p class="text-muted mb-20">Every time a customer places an order, an email with the order details is sent here.</p>

                        <form method="POST" action="{{ route('admin.settings.update') }}">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-lg-8 col-12 mb-20">
                                    <label for="order_emails">Send new order emails to</label>
                                    <input id="order_emails" name="order_emails" type="text" inputmode="email" autocomplete="off"
                                           class="form-control @error('order_emails') is-invalid @enderror"
                                           placeholder="e.g. owner@gmail.com" value="{{ old('order_emails', $orderEmails) }}" required>
                                    @error('order_emails')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text">To send it to more than one person, separate the addresses with commas (up to {{ $maxOrderEmails }}).</small>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="zmdi zmdi-save"></i> Save
                            </button>
                        </form>

                        <hr class="my-4">

                        <form method="POST" action="{{ route('admin.settings.testEmail') }}">
                            @csrf
                            <p class="text-muted mb-10">Check that the emails arrive: this sends a test email to the saved address{{ str_contains($orderEmails, ',') ? 'es' : '' }}.</p>
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="zmdi zmdi-email"></i> Send a test email
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- New-order notifications on the phone (App\Services\AdminPush); the buttons are run by the script below --}}
        <div class="row mt-4">
            <div class="col-12">
                <div class="card" data-push data-ready="{{ $pushKey ? 1 : 0 }}" data-test="{{ route('admin.push.test') }}">
                    <div class="card-body">
                        <h4 class="mb-10"><i class="zmdi zmdi-smartphone mr-2"></i> iPhone notifications</h4>
                        <p class="text-muted mb-20">Get a notification on your iPhone for every new order, even when the phone is locked and the app is closed.</p>

                        @if(!$pushKey)
                            <div class="alert alert-warning mb-20">Notifications can't be set up on this server: PHP's OpenSSL could not make the keys they need.</div>
                        @endif

                        <p class="mb-10" data-push-status>Checking this device…</p>
                        <div class="mb-20">
                            <button type="button" class="btn btn-primary" data-push-on hidden><i class="zmdi zmdi-notifications-active"></i> Turn on notifications on this device</button>
                            <button type="button" class="btn btn-outline-primary" data-push-off hidden>Turn off on this device</button>
                            <button type="button" class="btn btn-outline-primary" data-push-test @if($pushDevices->isEmpty()) hidden @endif><i class="zmdi zmdi-notifications"></i> Send a test notification</button>
                        </div>

                        <h5 class="mb-10">Set it up on your iPhone (iOS 16.4 or newer)</h5>
                        <ol class="text-muted mb-20">
                            <li>On the iPhone, open <strong>{{ url('/admin') }}</strong> in Safari.</li>
                            <li>Tap the Share button, then <strong>Add to Home Screen</strong>, then <strong>Add</strong>.</li>
                            <li>Open <strong>SS Admin</strong> from the home screen and log in. The app keeps its own login, separate from Safari.</li>
                            <li>In the app, open <strong>Settings</strong> from the menu, tap <strong>Turn on notifications on this device</strong>, then <strong>Allow</strong>.</li>
                        </ol>
                        <p class="text-muted mb-20">To choose the sound or show them on the lock screen: iPhone Settings &gt; Notifications &gt; SS Admin.</p>

                        @if($pushDevices->isNotEmpty())
                            <h5 class="mb-10">Devices getting notifications</h5>
                            @foreach($pushDevices as $device)
                                <form method="POST" action="{{ route('admin.push.destroy', $device->id) }}" class="d-flex align-items-center mb-10">
                                    @csrf
                                    @method('DELETE')
                                    <span class="mr-2"><strong>{{ $device->device ?: 'Device' }}</strong>
                                        <small class="text-muted">since {{ $device->created_at?->timezone(config('app.display_timezone'))->format('M j, Y') }}</small></span>
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Stop notifications on this device?')">Remove</button>
                                </form>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
@endsection

@push('scripts')
<script>
    // The buttons of "iPhone notifications" (turning on and off is AdminUI.push in resources/assets/admin/admin-ui.js)
    (function ($) {
        var push = AdminUI.push;
        var $box = $('[data-push]');
        var $status = $box.find('[data-push-status]'), $on = $box.find('[data-push-on]'), $off = $box.find('[data-push-off]'), $test = $box.find('[data-push-test]');
        var ios = /iPhone|iPad|iPod/.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
        var standalone = window.navigator.standalone === true || window.matchMedia('(display-mode: standalone)').matches;

        function status(text, error) { $status.text(text).toggleClass('text-danger', !!error); }

        $test.on('click', function () {
            $test.prop('disabled', true);
            $.ajax({url: $box.data('test'), method: 'POST', dataType: 'json'})
                .done(function (r) { status(r.message); })
                .fail(function (xhr) { status(push.reason(xhr), true); })
                .always(function () { $test.prop('disabled', false); });
        });

        if (!$box.data('ready')) {
            status('Notifications are not available.', true);
            return;
        }
        if (!push.supported()) {
            status(ios && !standalone
                ? 'On an iPhone, notifications only work in the home-screen app: follow the steps below, then open this page in the app.'
                : 'This browser can\'t show notifications here. On an iPhone, follow the steps below.');
            return;
        }

        function show(subscription) {
            var blocked = Notification.permission === 'denied';
            $on.prop('hidden', !!subscription || blocked);
            $off.prop('hidden', !subscription);
            if (blocked) {
                status('Notifications are blocked for this app. Allow them in iPhone Settings > Notifications > SS Admin (or in this browser\'s site settings).', true);
            } else {
                status(subscription ? 'Notifications are on for this device.' : 'Notifications are off for this device.');
            }
        }
        push.current().then(show);

        $on.on('click', function () {
            $on.prop('disabled', true);
            push.turnOn().then(function (subscription) {
                show(subscription);
                $test.prop('hidden', false);
            }, function (e) {
                if (Notification.permission !== 'granted') {
                    show(null);
                } else {
                    status('Notifications could not be turned on (' + push.reason(e) + '). Reload the page and try again.', true);
                }
            }).then(function () { $on.prop('disabled', false); });
        });

        $off.on('click', function () {
            $off.prop('disabled', true);
            push.turnOff().then(function () { show(null); }, function (e) {
                status('Notifications could not be turned off (' + push.reason(e) + ').', true);
            }).then(function () { $off.prop('disabled', false); });
        });
    })(jQuery);
</script>
@endpush
