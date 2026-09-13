@extends('admin.layouts.app')

@section('title', 'Add Product')

@section('content')
    <x-admin.page-header title="Add Product" :breadcrumb="[['label' => 'Products', 'url' => route('admin.products.index')], ['label' => 'Add Product']]" />

    <form
        method="POST"
        action="{{ route('admin.products.store') }}"
        enctype="multipart/form-data"
        x-data="{ submitting: false }"
        x-on:submit="if (submitting) { $event.preventDefault(); return; } submitting = true; if (window.CKEDITOR) { Object.values(CKEDITOR.instances).forEach((editor) => editor.updateElement()) }"
    >
        @include('admin.products._form')
    </form>
@endsection
