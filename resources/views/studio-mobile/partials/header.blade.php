<header class="sm-header safe-top">
    <a href="{{ route('home') }}" class="sm-header__back" aria-label="Início">←</a>
    <div class="sm-header__title">
        <span class="sm-header__brand">Studio</span>
    </div>
    <button type="button" class="sm-header__action" @click="openImageStudioDimensionsModal()">Formato</button>
    <button type="button" class="sm-header__action sm-header__action--primary" @click="imageStudioExport('png')">PNG</button>
    <button type="button" class="sm-header__menu" @click="imageStudioMobileMenuOpen = !imageStudioMobileMenuOpen" aria-label="Menu">⋯</button>
</header>

<div x-show="imageStudioMobileMenuOpen" x-cloak class="sm-menu-backdrop" @click="imageStudioMobileMenuOpen = false"></div>
<div x-show="imageStudioMobileMenuOpen" x-cloak class="sm-menu" @click.outside="imageStudioMobileMenuOpen = false">
    <button type="button" @click="fitImageStudioCanvas(); imageStudioMobileMenuOpen = false">Ajustar canvas</button>
    <button type="button" @click="imageStudioExport('jpg'); imageStudioMobileMenuOpen = false">Baixar JPG</button>
    <button type="button" @click="openImageStudioTemplatesModal(); imageStudioMobileMenuOpen = false">Layouts</button>
    <button type="button" @click="clearImageStudioWorkspace(); imageStudioMobileMenuOpen = false">Limpar</button>
</div>
