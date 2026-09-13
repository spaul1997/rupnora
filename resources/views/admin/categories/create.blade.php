@extends('admin.layouts.app')

@section('title', 'Add Category')

@section('content')
    <x-admin.page-header title="Add Category" :breadcrumb="[['label' => 'Categories', 'url' => route('admin.categories.index')], ['label' => 'Add Category']]" />

    <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data">
        @include('admin.categories._form')
    </form>
@endsection
