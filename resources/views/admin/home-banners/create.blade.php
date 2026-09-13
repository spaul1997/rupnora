@extends('admin.layouts.app')

@section('title', 'Add Home Banner')

@section('content')
    <x-admin.page-header title="Add Home Banner" :breadcrumb="[['label' => 'Home Banners', 'url' => route('admin.home-banners.index')], ['label' => 'Add Banner']]" />

    <form method="POST" action="{{ route('admin.home-banners.store') }}" enctype="multipart/form-data">
        @include('admin.home-banners._form')
    </form>
@endsection
