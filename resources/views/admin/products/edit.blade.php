@extends('admin.layouts.app')

@section('title', 'Edit Product')

@section('content')
    <x-admin.page-header title="Edit Product" :breadcrumb="[['label' => 'Products', 'url' => route('admin.products.index')], ['label' => $product->name]]">
        <x-slot:actions>
            <a href="{{ route('admin.products.show', $product) }}" class="admin-btn-secondary">View Product</a>
        </x-slot:actions>
    </x-admin.page-header>

    <form
        method="POST"
        action="{{ route('admin.products.update', $product) }}"
        enctype="multipart/form-data"
        x-data="{ submitting: false }"
        x-on:submit="if (submitting) { $event.preventDefault(); return; } submitting = true; if (window.CKEDITOR) { Object.values(CKEDITOR.instances).forEach((editor) => editor.updateElement()) }"
    >
        @include('admin.products._form')
    </form>
@endsection
