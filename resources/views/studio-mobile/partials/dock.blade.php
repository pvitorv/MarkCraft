<nav class="sm-dock" aria-label="Ferramentas">
    @php
        $tabs = [
            ['id' => 'tools', 'label' => 'Criar'],
            ['id' => 'text', 'label' => 'Texto'],
            ['id' => 'bg', 'label' => 'Fundo'],
            ['id' => 'layers', 'label' => 'Camadas'],
            ['id' => 'export', 'label' => 'Exportar'],
        ];
    @endphp
    @foreach($tabs as $tab)
        <button
            type="button"
            class="sm-dock__btn"
            :class="imageStudioSidebarTab === '{{ $tab['id'] }}' && imageStudioMobileSheetOpen ? 'sm-dock__btn--on' : ''"
            @click="openImageStudioMobileSheet('{{ $tab['id'] }}')"
        >{{ $tab['label'] }}</button>
    @endforeach
</nav>
