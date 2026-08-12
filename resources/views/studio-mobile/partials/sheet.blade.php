<div
    x-show="imageStudioMobileSheetOpen"
    x-cloak
    class="sm-sheet-backdrop"
    @click="closeImageStudioMobileSheet()"
></div>
<div
    x-show="imageStudioMobileSheetOpen"
    x-cloak
    class="sm-sheet"
    role="dialog"
    aria-modal="true"
    @keydown.escape.window="closeImageStudioMobileSheet()"
>
    <div class="sm-sheet__handle"></div>
    <header class="sm-sheet__head">
        <h2 class="sm-sheet__title" x-text="imageStudioMobileSheetTitle()"></h2>
        <button type="button" class="sm-sheet__close" @click="closeImageStudioMobileSheet()" aria-label="Fechar">×</button>
    </header>
    <div class="sm-sheet__body">
        @include('studio-mobile.partials.panels.tools')
        @include('studio-mobile.partials.panels.text')
        @include('studio-mobile.partials.panels.bg')
        @include('studio-mobile.partials.panels.layers')
        @include('studio-mobile.partials.panels.export')
    </div>
</div>
