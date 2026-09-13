@extends('admin.layouts.app')

@section('title', 'Edit Collection')

@section('content')
    <x-admin.page-header title="Edit Collection" :breadcrumb="[['label' => 'Collections', 'url' => route('admin.jewellery-collections.index')], ['label' => $collection->name]]" />

    <form method="POST" action="{{ route('admin.jewellery-collections.update', $collection) }}">
        @include('admin.jewellery-collections._form')
    </form>
@endsection
