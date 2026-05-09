<!doctype html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Admin | Manage Special Offers</title>
    <meta name="robots" content="noindex, follow" />
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="{{ asset('admin_assets/css/vendor/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/vendor/material-design-iconic-font.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/vendor/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/vendor/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/vendor/cryptocurrency-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/plugins/plugins.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/helper.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/style.css') }}">
    <link id="cus-style" rel="stylesheet" href="{{ asset('admin_assets/css/style-primary.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        .so-page-heading h3 { margin:0; font-weight:600; }
        .so-page-heading p { color:#94a3b8; margin-top:6px; max-width:720px; }

        .so-panel {
            background: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.01));
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 10px;
            padding: 24px;
            margin-bottom: 24px;
        }
        .so-panel-title {
            display:flex; align-items:center; gap:10px;
            margin: 0 0 18px;
            font-size: 16px; font-weight:600;
            color:#e5e7eb;
        }
        .so-panel-title .so-icon {
            width:32px; height:32px; border-radius:8px;
            display:inline-flex; align-items:center; justify-content:center;
            background: rgba(37,99,235,0.15); color:#60a5fa;
        }
        .so-panel-title .so-count {
            margin-left:auto;
            background: rgba(255,255,255,0.06);
            color:#cbd5e1;
            padding: 2px 10px; border-radius: 999px;
            font-size: 12px; font-weight:500;
        }

        .so-form label { color:#cbd5e1; font-size: 13px; margin-bottom:6px; }
        .so-add-btn { padding: 10px 18px; }

        .so-grid {
            display:grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 14px;
        }
        .so-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 10px;
            overflow: hidden;
            display:flex; flex-direction:column;
            transition: transform .15s ease, border-color .15s ease;
        }
        .so-card:hover { transform: translateY(-2px); border-color: rgba(96,165,250,0.5); }
        .so-card-image {
            width:100%; height:140px;
            background:#fff;
            display:flex; align-items:center; justify-content:center;
            overflow:hidden;
            padding: 8px;
        }
        .so-card-image img { max-width:100%; max-height:100%; width:auto; height:auto; object-fit:contain; }
        .so-card-image .placeholder { color:#475569; font-size: 24px; }
        .so-card-body { padding: 10px 12px; flex:1; display:flex; flex-direction:column; gap:4px; }
        .so-card-name { font-weight:600; color:#f1f5f9; font-size:13px; line-height:1.3; }
        .so-card-price { color:#60a5fa; font-weight:600; font-size:13px; }
        .so-card-meta { color:#94a3b8; font-size:11px; margin-top:auto; }
        .so-card-actions {
            display:flex; align-items:center; gap:6px;
            padding: 8px 10px;
            border-top: 1px solid rgba(255,255,255,0.06);
            background: rgba(0,0,0,0.15);
        }
        .so-card-actions .position-form { display:flex; align-items:center; gap:5px; flex:1; }
        .so-card-actions .position-form label { color:#94a3b8; font-size: 10px; margin:0; text-transform:uppercase; letter-spacing:0.5px; }
        .so-card-actions input[type=number] {
            width: 44px; height: 28px;
            background:#0f172a; color:#fff;
            border:1px solid rgba(255,255,255,0.1); border-radius:5px;
            padding: 2px 6px; font-size:12px;
        }
        .so-btn {
            border:none; border-radius:5px; height:28px;
            display:inline-flex; align-items:center; justify-content:center;
            cursor:pointer; font-size:11px; padding:0 8px;
            transition: filter .15s ease;
        }
        .so-btn:hover { filter: brightness(1.15); }
        .so-btn-save { background:#2563eb; color:#fff; }
        .so-btn-delete { background:#dc2626; color:#fff; width:28px; padding:0; }

        .so-empty {
            text-align:center;
            padding: 60px 20px;
            border: 1px dashed rgba(255,255,255,0.12);
            border-radius: 10px;
            color:#94a3b8;
        }
        .so-empty .fa { color:#475569; font-size: 48px; margin-bottom: 16px; display:block; }
        .so-empty h5 { color:#cbd5e1; }

        .so-alert { border-radius:8px; border:none; padding:14px 18px; margin-bottom:18px; }
        .so-alert-success { background: rgba(34,197,94,0.12); color:#4ade80; border:1px solid rgba(34,197,94,0.3); }
        .so-alert-error   { background: rgba(220,38,38,0.12); color:#f87171; border:1px solid rgba(220,38,38,0.3); }

        /* Select2 dark theme */
        .select2-container--default .select2-selection--single { background:#0f172a; border:1px solid rgba(255,255,255,0.1); height:42px; color:#fff; border-radius:6px; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { color:#fff; line-height:42px; padding-left:14px; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height:42px; }
        .select2-container--default .select2-selection--single .select2-selection__placeholder { color:#64748b; }
        .select2-dropdown { background:#0f172a; border-color:rgba(255,255,255,0.1); color:#fff; }
        .select2-search--dropdown .select2-search__field { background:#1e293b; color:#fff; border:1px solid rgba(255,255,255,0.1); border-radius:4px; padding:6px 10px; }
        .select2-results__option { color:#e2e8f0; padding: 8px 12px; }
        .select2-container--default .select2-results__option--highlighted[aria-selected] { background:#2563eb; color:#fff; }
        .select2-container--default .select2-results__option[aria-selected=true] { background: rgba(255,255,255,0.05); }
    </style>
</head>

<body class="skin-dark">

<div class="main-wrapper">

    @include('partials/adminHeader')
    @include('partials/adminSideBar')

    <div class="content-body">
        <div class="row mb-20">
            <div class="col-12">
                <div class="so-page-heading">
                    <h3>Manage Special Offers</h3>
                    <p>Pick up to {{ $maxOffers }} products to feature in the "Our Special Offers" section on the home page. Lower position numbers appear first.</p>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="so-alert so-alert-success">
                <i class="fa fa-check-circle mr-2"></i> {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="so-alert so-alert-error">
                @foreach($errors->all() as $err)
                    <div><i class="fa fa-exclamation-circle mr-2"></i>{{ $err }}</div>
                @endforeach
            </div>
        @endif

        <div class="so-panel">
            <h4 class="so-panel-title">
                <span class="so-icon"><i class="fa fa-plus"></i></span>
                Add a product to special offers
                <span class="so-count">{{ $offers->count() }} / {{ $maxOffers }}</span>
            </h4>
            @if($isFull)
                <div class="so-alert so-alert-error mb-0">
                    <i class="fa fa-info-circle mr-2"></i>
                    You've reached the maximum of {{ $maxOffers }} special offers. Remove one below before adding another.
                </div>
            @else
                <form method="POST" action="{{ route('admin.specialOffers.store') }}" class="so-form">
                    @csrf
                    <div class="row align-items-end">
                        <div class="col-12 col-lg-8 mb-3 mb-lg-0">
                            <label for="product_id">Product</label>
                            <select name="product_id" id="product_id" class="form-control" required>
                                <option value="">— Select a product —</option>
                                @foreach($availableProducts as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} — ${{ number_format($p->price, 2) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-lg-4">
                            <button type="submit" class="btn btn-primary so-add-btn w-100">
                                <i class="zmdi zmdi-plus"></i> Add to Special Offers
                            </button>
                        </div>
                    </div>
                </form>
            @endif
        </div>

        <div class="so-panel">
            <h4 class="so-panel-title">
                <span class="so-icon" style="background: rgba(245,158,11,0.15); color:#fbbf24;"><i class="fa fa-star"></i></span>
                Current Special Offers
                <span class="so-count">{{ $offers->count() }} / {{ $maxOffers }}</span>
            </h4>

            @if($offers->count() > 0)
                <div class="so-grid">
                    @foreach($offers as $offer)
                        <div class="so-card">
                            <div class="so-card-image">
                                @if($offer->product && $offer->product->images->first())
                                    <img src="{{ asset($offer->product->images->first()->url) }}" alt="">
                                @else
                                    <span class="placeholder"><i class="fa fa-image"></i></span>
                                @endif
                            </div>
                            <div class="so-card-body">
                                <div class="so-card-name">{{ $offer->product->name ?? '(deleted product)' }}</div>
                                <div class="so-card-price">
                                    @if($offer->product)
                                        ${{ number_format($offer->product->price, 2) }}
                                    @else
                                        —
                                    @endif
                                </div>
                                <div class="so-card-meta">Added {{ $offer->created_at->diffForHumans() }}</div>
                            </div>
                            <div class="so-card-actions">
                                <form method="POST" action="{{ route('admin.specialOffers.update', $offer->id) }}" class="position-form">
                                    @csrf
                                    @method('PUT')
                                    <label>Pos</label>
                                    <input type="number" min="1" max="{{ $maxOffers }}" name="position" value="{{ $offer->position }}" required>
                                    <button type="submit" class="so-btn so-btn-save">Save</button>
                                </form>
                                <form method="POST" action="{{ route('admin.specialOffers.destroy', $offer->id) }}" onsubmit="return confirm('Remove this product from special offers?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="so-btn so-btn-delete" title="Remove">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="so-empty">
                    <i class="fa fa-star"></i>
                    <h5>No special offers yet</h5>
                    <p>Add products above to feature them on the home page.</p>
                </div>
            @endif
        </div>

    </div>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
<script src="{{ asset('admin_assets/js/vendor/modernizr-3.6.0.min.js') }}"></script>
<script src="{{ asset('admin_assets/js/vendor/jquery-3.3.1.min.js') }}"></script>
<script src="{{ asset('admin_assets/js/vendor/popper.min.js') }}"></script>
<script src="{{ asset('admin_assets/js/vendor/bootstrap.min.js') }}"></script>
<script src="{{ asset('admin_assets/js/plugins/plugins.js') }}"></script>
<script src="{{ asset('admin_assets/js/main.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function () {
        $('#product_id').select2({
            placeholder: '— Select a product —',
            width: '100%',
        });
    });
</script>

</body>
</html>
