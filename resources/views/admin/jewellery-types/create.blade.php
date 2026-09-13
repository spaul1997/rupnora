@extends('admin.layouts.app')

@section('title', 'Add Jewellery Type')

@section('content')
    <x-admin.page-header title="Add Jewellery Type" :breadcrumb="[['label' => 'Jewellery Types', 'url' => route('admin.jewellery-types.index')], ['label' => 'Add Type']]" />

    <form method="POST" action="{{ route('admin.jewellery-types.store') }}">
        @include('admin.jewellery-types._form')
    </form>
@endsection
