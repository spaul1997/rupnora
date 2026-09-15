@extends('admin.layouts.app')

@section('title', 'Edit Metal Type')

@section('content')
    <x-admin.page-header title="Edit Metal Type" :breadcrumb="[['label' => 'Metal Types', 'url' => route('admin.metal-types.index')], ['label' => $type->name]]" />

    <form method="POST" action="{{ route('admin.metal-types.update', $type) }}">
        @include('admin.metal-types._form')
    </form>
@endsection
