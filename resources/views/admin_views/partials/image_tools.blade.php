{{-- Styles and script for removable pictures on the admin pages (products, options, brands, categories); added once per page --}}
@once
@push('styles')
<style>
    .adm-imgwrap { margin-bottom: 10px; }
    .adm-img { position: relative; display: inline-block; width: 112px; height: 112px; border: 2px solid #e5e7eb;
               border-radius: 12px; background: #fff; vertical-align: top; }
    .adm-img img { width: 100%; height: 100%; object-fit: contain; border-radius: 10px; padding: 4px; pointer-events: none; }
    .adm-img--wide { width: 168px; height: 84px; }
    .adm-img__x { position: absolute; top: -9px; right: -9px; width: 26px; height: 26px; border-radius: 50%; border: 0; padding: 0;
                  background: #b91c1c; color: #fff; font-size: 18px; line-height: 26px; cursor: pointer;
                  box-shadow: 0 2px 6px rgba(0, 0, 0, .25); z-index: 2; }
    .adm-img__x:hover { background: #7f1d1d; }
    .adm-img__x:disabled { opacity: .5; cursor: wait; }
    .adm-img__status { margin: 8px 0 0; min-height: 0; font-size: 13px; }
    .adm-img__status:empty { display: none; }
</style>
@endpush
@push('scripts')
<script>
    // The × on a saved picture: confirm, delete it through its route, take it off the page
    $(document).on('pointerdown', '[data-image-remove]', function (e) { e.stopPropagation(); });
    $(document).on('click', '[data-image-remove]', function () {
        var $button = $(this);
        var $area = $button.closest('[data-image-area]');
        var $status = $area.find('[data-image-status]').first();
        if (!confirm($button.data('confirm') || 'Remove this picture?')) {
            return;
        }
        $button.prop('disabled', true);
        $.ajax({url: $button.data('image-remove'), type: 'POST', dataType: 'json',
                data: {_token: '{{ csrf_token() }}', _method: 'DELETE'}, headers: {Accept: 'application/json'}})
            .done(function (response) {
                $button.closest('[data-image-box]').remove();
                $status.removeClass('text-danger').addClass('text-muted').text((response && response.message) || 'Picture removed.');
                $area.trigger('image:removed');
            })
            .fail(function (xhr) {
                $button.prop('disabled', false);
                $status.removeClass('text-muted').addClass('text-danger')
                    .text((xhr.responseJSON && xhr.responseJSON.message) || 'The picture could not be removed. Reload the page and try again.');
            });
    });
</script>
@endpush
@endonce
