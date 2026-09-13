@extends('admin.layouts.app')

@section('title', 'Add Collection')

@section('content')
    <x-admin.page-header title="Add Collection" :breadcrumb="[['label' => 'Collections', 'url' => route('admin.jewellery-collections.index')], ['label' => 'Add Collection']]" />

    <form method="POST" action="{{ route('admin.jewellery-collections.store') }}">
        @include('admin.jewellery-collections._form')
    </form>
@endsection
