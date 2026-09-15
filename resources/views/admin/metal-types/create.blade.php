@extends('admin.layouts.app')

@section('title', 'Add Metal Type')

@section('content')
    <x-admin.page-header title="Add Metal Type" :breadcrumb="[['label' => 'Metal Types', 'url' => route('admin.metal-types.index')], ['label' => 'Add Metal Type']]" />

    <form method="POST" action="{{ route('admin.metal-types.store') }}">
        @include('admin.metal-types._form')
    </form>
@endsection
