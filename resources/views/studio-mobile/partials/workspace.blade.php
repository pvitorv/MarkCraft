<div class="sm-workspace">
    <div class="sm-workspace__canvas-wrap">
        <p x-show="message" x-cloak x-text="message" class="sm-flash sm-flash--ok"></p>
        <p x-show="error" x-cloak x-text="error" class="sm-flash sm-flash--err"></p>
        @include('studio-mobile.partials.canvas')
    </div>

    @include('studio-mobile.partials.dock')
</div>

@include('studio-mobile.partials.sheet')
@include('studio.partials.image_studio_modals')
