@extends('admin.layouts.app')

@section('title', 'Edit Home Banner')

@section('content')
    <x-admin.page-header title="Edit Home Banner" :breadcrumb="[['label' => 'Home Banners', 'url' => route('admin.home-banners.index')], ['label' => $homeBanner->heading]]" />

    <form method="POST" action="{{ route('admin.home-banners.update', $homeBanner) }}" enctype="multipart/form-data">
        @include('admin.home-banners._form', ['banner' => $homeBanner])
    </form>
@endsection
