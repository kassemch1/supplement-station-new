@extends('layouts.admin')

@section('title', 'Manage Products')

@section('content')
        <!-- Page Headings Start -->
        <div class="row justify-content-between align-items-center mb-10">

            <!-- Page Heading Start -->
            <div class="col-12 col-lg-auto mb-20">
                <div class="page-heading">
                    <h3>Manage Products</h3>
                </div>
            </div><!-- Page Heading End -->

        </div><!-- Page Headings End -->

        <!-- Add or Edit Product Start -->

        <div class="add-edit-product-wrap col-12">

            <div class="add-edit-product-form">
                <form id="productForm" method="post" action="{{route('manageProducts.update')}}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="product_id" value="{{$product->id}}">
                    <h4 class="title">Edit Product</h4>

                    <div class="row">
                        <div class="col-lg-6 col-12 mb-30">
                            <label for="">Name</label>
                            <input name="name" class="form-control" type="text"  placeholder="Product Name" value="{{$product->name}}">
                            @if ($errors->has('name'))
                                <span class="text-danger">{{ $errors->first('name') }}</span>
                            @endif
                        </div>
                        <div class="col-lg-6 col-12 mb-30">
                            <label for="">Price</label>
                            <input name="price" class="form-control" type="text"  placeholder="Product Price" value="{{$product->price}}">
                            @if ($errors->has('price'))
                                <span class="text-danger">{{ $errors->first('price') }}</span>
                            @endif
                        </div>
                        @include('admin_views.manage_products._category_brand', ['product' => $product ?? null])
                        <div class="col-lg-6 col-12 mb-30">
                            <label for="">Discount</label>
                            <input name="discount" class="form-control" type="text"  placeholder="Product Discount" value="{{$product->discount}}">
                            @if ($errors->has('discount'))
                                <span class="text-danger">{{ $errors->first('discount') }}</span>
                            @endif
                        </div>

                        <div class="col-12 mb-30">
                            <label for="">Description</label>
                            <textarea name="description" class="form-control">{{$product->description}}</textarea>
                            @if ($errors->has('description'))
                                <span class="text-danger">{{ $errors->first('description') }}</span>
                            @endif
                        </div>

                        <div class="col-12 mb-30">
                            <label>Product Picture(s)</label>
                            <input class="dropify" type="file" name="images[]" multiple>
                            <label class="adomx-checkbox mt-10"><input type="checkbox" name="keep_background" value="1"> <i class="icon"></i> Keep the original background <small class="text-muted">(by default a plain white background is removed)</small></label>
                            @if ($errors->has('images'))
                                <span class="text-danger">{{ $errors->first('images') }}</span>
                            @endif
                        </div>
                        <div class="col-12 mb-30">
                            <label>Current pictures <small class="text-muted">(drag to change the order; the first one is the main picture in the shop)</small></label>
                            <div data-image-area>
                                <div class="pimg-list" data-reorder-url="{{ route('productImage.reorder', $product->id) }}">
                                    @foreach($product->images as $image)
                                        <div class="adm-img pimg" data-id="{{ $image->id }}" data-image-box>
                                            <img src="{{ asset($image->url) }}" alt="" draggable="false">
                                            <span class="pimg__main">Main</span>
                                            <button type="button" class="adm-img__x" title="Remove this picture" aria-label="Remove this picture"
                                                    data-image-remove="{{ route('productImage.destroy', ['id' => $image->id]) }}"
                                                    data-confirm="Remove this picture from the product?">&times;</button>
                                        </div>
                                    @endforeach
                                </div>
                                <p class="adm-img__status text-muted" data-image-status aria-live="polite"></p>
                            </div>
                            @include('admin_views.partials.image_tools')
                        </div>

                        <div class="col-12 mb-30">
                            <label>Stock</label>
                            <div class="adomx-checkbox-radio-group">
                                <label class="adomx-radio"><input type="radio" name="stock" value="in-stock" {{ $product->stock ? 'checked' : '' }}> <i class="icon"></i> In Stock</label>
                                <label class="adomx-radio"><input type="radio" name="stock" value="out-of-stock" {{ !$product->stock ? 'checked' : '' }}> <i class="icon"></i> Out of Stock</label>
                            </div>
                            @if ($errors->has('stock'))
                                <span class="text-danger">{{ $errors->first('stock') }}</span>
                            @endif
                        </div>

                    </div>
                    <!-- Success Alert -->
                    <div id="successAlert" class="alert alert-success mt-3" style="display: none;">
                        <strong>Success!</strong> Product saved successfully.
                    </div>
                    <!-- Error Alert -->
                    <div id="errorAlert" class="alert alert-danger mt-3" style="display: none;">
                        <strong>Error!</strong> The product could not be saved. Please check the fields above.
                    </div>
                    <!-- Button Group Start -->
                    <div class="row">
                        <div class="d-flex flex-wrap justify-content-end col mbn-10">
                            <button class="button button-outline button-primary mb-10 ml-10 mr-0" type="submit" id="submitBtn">Save & Publish</button>
                        </div><!-- Button Group End -->
                    </div>
                </form>
            </div>

        </div><!-- Add or Edit Product End -->
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        $('#productForm').submit(function (e) {
            e.preventDefault(); // Prevent default form submission

            // Serialize form data
            var formData = new FormData(this);

            // Send AJAX request
            $.ajax({
                type: 'POST',
                url: $(this).attr('action'), // Use form's action attribute as URL
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $('#successAlert').fadeIn();
                    setTimeout(function () {
                        $('#successAlert').fadeOut();
                    }, 3000);
                    $('.text-danger').remove(); // Remove existing error messages
                },
                error: function (xhr, status, error) {
                    $('#errorAlert').fadeIn();
                    setTimeout(function () {
                        $('#errorAlert').fadeOut();
                    }, 3000);
                    let errors = xhr.responseJSON.errors;
                    $('.text-danger').remove(); // Remove existing error messages

                    // Loop through the errors and display them
                    $.each(errors, function(field, messages) {
                        // Handle radio button errors
                        if (field === 'stock') {
                            let inputField = $('[name="stock"]').last(); // Select the last radio button for proper placement
                            inputField.closest('.adomx-checkbox-radio-group').after('<span class="text-danger">' + messages[0] + '</span>');
                        }
                        // Handle image input errors
                        else if (field.startsWith('images')) {
                            let inputField = $('[name="images[]"]');
                            inputField.after('<span class="text-danger">' + messages[0] + '</span>');
                        }
                        // Handle other fields
                        else {
                            let inputField = $('[name="' + field + '"]');
                            inputField.after('<span class="text-danger">' + messages[0] + '</span>');
                        }
                    });
                }
            });
        });
    });
</script>
<style>
    .pimg-list { display: flex; flex-wrap: wrap; gap: 14px; }
    .pimg { cursor: grab; touch-action: none; user-select: none; transition: box-shadow .15s, opacity .15s; }
    .pimg:first-child { border-color: #b91c1c; }
    .pimg__main { display: none; position: absolute; left: 6px; bottom: 6px; padding: 1px 7px; border-radius: 6px;
                  background: #b91c1c; color: #fff; font-size: 11px; font-weight: 700; }
    .pimg:first-child .pimg__main { display: block; }
    .pimg-ghost { opacity: .9; border: 2px solid #b91c1c; border-radius: 12px; background: #fff; box-shadow: 0 10px 24px rgba(0, 0, 0, .25); z-index: 1000; }
    .pimg-ghost img { width: 100%; height: 100%; object-fit: contain; border-radius: 10px; }
    .pimg-ghost .pimg__main, .pimg-ghost .adm-img__x { display: none; }
    .pimg.is-placeholder { opacity: .35; border-style: dashed; }
</style>
<script>
    // Product pictures: dragging sets the order and saves it right away through its own route. The × on
    // each picture is the shared one of every admin page (admin_views/partials/image_tools).
    $(function () {
        var $list = $('.pimg-list');
        var $status = $list.closest('[data-image-area]').find('[data-image-status]');
        var csrf = '{{ csrf_token() }}';

        function say(text, isError) {
            $status.text(text).toggleClass('text-danger', !!isError).toggleClass('text-muted', !isError);
        }

        function saveOrder() {
            var ids = $list.children('.pimg').map(function () { return $(this).data('id'); }).get();
            $.ajax({url: $list.data('reorder-url'), type: 'POST', dataType: 'json',
                    data: {_token: csrf, ids: ids}, headers: {Accept: 'application/json'}})
                .done(function () { say('Order saved.'); })
                .fail(function () { say('The order could not be saved. Reload the page and try again.', true); });
        }

        // Drag with a mouse or a finger: the picture follows the pointer, a dashed gap shows where it lands
        // Moving the picture in the page drops the pointer capture, so moves and the release are followed
        // on the whole document
        var drag = null;
        $list.on('pointerdown', '.pimg', function (e) {
            if (e.button !== undefined && e.button !== 0) {
                return;
            }
            var rect = this.getBoundingClientRect();
            drag = {item: this, startX: e.clientX, startY: e.clientY, dx: e.clientX - rect.left, dy: e.clientY - rect.top,
                    width: rect.width, height: rect.height, moved: false, ghost: null, from: $list.children().index(this)};
            e.preventDefault();
        });
        document.addEventListener('pointermove', function (e) {
            if (!drag) {
                return;
            }
            var item = drag.item;
            if (!drag.moved) {
                if (Math.abs(e.clientX - drag.startX) + Math.abs(e.clientY - drag.startY) < 6) {
                    return;
                }
                drag.moved = true;
                drag.ghost = item.cloneNode(true);
                $(drag.ghost).removeClass('pimg').addClass('pimg-ghost')
                    .css({position: 'fixed', width: drag.width, height: drag.height, margin: 0, pointerEvents: 'none'});
                document.body.appendChild(drag.ghost);
                $(item).addClass('is-placeholder');
            }
            $(drag.ghost).css({left: e.clientX - drag.dx, top: e.clientY - drag.dy});
            // The gap goes before the first picture whose middle is past the pointer
            var target = null;
            $list.children('.pimg').not(item).each(function () {
                var r = this.getBoundingClientRect();
                var sameRow = e.clientY >= r.top && e.clientY <= r.bottom;
                if ((sameRow && e.clientX < r.left + r.width / 2) || e.clientY < r.top) {
                    target = this;
                    return false;
                }
            });
            if (target) {
                if (target !== item.nextSibling) {
                    $list[0].insertBefore(item, target);
                }
            } else if (item !== $list.children().last()[0]) {
                $list[0].appendChild(item);
            }
        });
        function drop() {
            if (!drag) {
                return;
            }
            var moved = drag.moved && $list.children().index(drag.item) !== drag.from;
            if (drag.ghost) {
                drag.ghost.remove();
            }
            $(drag.item).removeClass('is-placeholder');
            drag = null;
            if (moved) {
                saveOrder();
            }
        }
        document.addEventListener('pointerup', drop);
        document.addEventListener('pointercancel', drop);
    });
</script>
@endpush
