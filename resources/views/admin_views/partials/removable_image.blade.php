{{--
    A saved picture with a red × in its corner. The × asks for confirmation, then removes the picture
    through its own route (DELETE $url) without saving the rest of the form.
    @include('admin_views.partials.removable_image', ['src' => ..., 'url' => ..., 'confirm' => 'Remove this logo?'])
    Optional: 'wide' => true for logos (a wider box).
--}}
<div class="adm-imgwrap" data-image-area>
    <div class="adm-img {{ ($wide ?? false) ? 'adm-img--wide' : '' }}" data-image-box>
        <img src="{{ $src }}" alt="" draggable="false">
        <button type="button" class="adm-img__x" title="Remove this picture" aria-label="Remove this picture"
                data-image-remove="{{ $url }}" data-confirm="{{ $confirm ?? 'Remove this picture?' }}">&times;</button>
    </div>
    <p class="adm-img__status text-muted" data-image-status aria-live="polite"></p>
</div>
@include('admin_views.partials.image_tools')
