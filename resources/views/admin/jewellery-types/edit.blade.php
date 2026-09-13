@extends('admin.layouts.app')

@section('title', 'Edit Jewellery Type')

@section('content')
    <x-admin.page-header title="Edit Jewellery Type" :breadcrumb="[['label' => 'Jewellery Types', 'url' => route('admin.jewellery-types.index')], ['label' => $type->name]]" />

    <form method="POST" action="{{ route('admin.jewellery-types.update', $type) }}">
        @include('admin.jewellery-types._form')
    </form>
@endsection
