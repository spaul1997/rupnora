@extends('admin.layouts.app')

@section('title', 'Edit Category')

@section('content')
    <x-admin.page-header title="Edit Category" :breadcrumb="[['label' => 'Categories', 'url' => route('admin.categories.index')], ['label' => $category->name]]" />

    <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data">
        @include('admin.categories._form')
    </form>
@endsection
