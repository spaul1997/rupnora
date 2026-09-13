@extends('admin.layouts.app')

@section('title', 'Add FAQ')

@section('content')
    <x-admin.page-header title="Add FAQ" :breadcrumb="[['label' => 'FAQ', 'url' => route('admin.faqs.index')], ['label' => 'Add FAQ']]" />

    <form method="POST" action="{{ route('admin.faqs.store') }}">
        @include('admin.faqs._form')
    </form>
@endsection
