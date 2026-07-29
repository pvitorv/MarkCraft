@extends('layouts.ferramentas')

@section('title', 'Image Studio')
@section('heading', 'Image Studio')
@section('subtitle', 'Monte capas e artes · rascunho no navegador · exporte para o blog')
@section('main_class', 'studio-main--canvas')

@section('vite')
    <meta name="studio-save-url" content="{{ $studioSaveUrl }}">
    <meta name="studio-catalog-url" content="{{ $studioCatalogUrl }}">
    <meta name="studio-remove-bg-url" content="{{ $studioRemoveBgUrl }}">
    <meta name="studio-bg-driver" content="{{ $imageStudioCatalog['background_removal_driver'] ?? 'rembg' }}">
    @vite(['resources/css/studio.css', 'resources/js/image-studio/app-studio.js'])
@endsection

@section('top_actions')
    <a href="{{ route('painel.posts.create') }}">Escrever artigo</a>
@endsection

@section('content')
@php
    $c = $imageStudioCatalog;
    $bgRemovalEmbed = [
        'available' => (bool) ($c['background_removal_available'] ?? false),
        'driver' => $c['background_removal_driver'] ?? 'rembg',
        'label' => $c['background_removal_label'] ?? '',
        'client' => (bool) ($c['background_removal_client'] ?? false),
    ];
@endphp

<script type="application/json" id="criasys-image-studio-presets">@json($c['presets'] ?? [])</script>
<script type="application/json" id="criasys-image-studio-bg-removal">@json($bgRemovalEmbed)</script>
<script type="application/json" id="criasys-image-studio-defaults">@json($c['defaults'] ?? [])</script>
<script type="application/json" id="criasys-image-studio-primary-formats">@json($c['primary_formats'] ?? [])</script>
<script type="application/json" id="criasys-image-studio-group-order">@json($c['group_order'] ?? [])</script>
<script type="application/json" id="criasys-image-studio-templates">@json($c['templates'] ?? [])</script>
<script type="application/json" id="criasys-image-studio-packs">@json($c['packs'] ?? [])</script>
<script type="application/json" id="criasys-image-studio-pack-categories">@json($c['pack_categories'] ?? [])</script>
<script type="application/json" id="criasys-image-studio-brand">@json($c['brand'] ?? null)</script>
<script type="application/json" id="criasys-image-studio-fonts">@json($c['fonts'] ?? [])</script>
<script type="application/json" id="criasys-image-studio-icons">@json($c['icon_glyphs'] ?? [])</script>
<script type="application/json" id="criasys-image-studio-icon-fonts">@json($c['icon_fonts'] ?? [])</script>
<script type="application/json" id="criasys-image-studio-elements">@json($c['elements'] ?? [])</script>
<script type="application/json" id="criasys-image-studio-element-groups">@json($c['element_groups'] ?? [])</script>

<div x-data="blogImageStudio" x-cloak class="studio-app">
    <div class="studio-status-row">
        <p class="studio-flash studio-flash-err" x-show="error" x-text="error" x-cloak></p>
        <p class="studio-flash studio-flash-ok" x-show="message && !error" x-text="message" x-cloak></p>
        <p class="hint" x-show="imageStudioSaving" x-cloak>Salvando rascunho…</p>
    </div>
    @include('painel.studio.image_studio_workspace', ['imageStudioCatalog' => $c])
    @include('painel.studio.image_studio_modals')
</div>
@endsection
