@extends('admin.layouts.app')

@section('title', 'Add Influencer')

@section('content')
    <x-admin.page-header title="Add Influencer" :breadcrumb="[['label' => 'Influencers', 'url' => route('admin.influencers.index')], ['label' => 'Add Influencer']]" />

    <form method="POST" action="{{ route('admin.influencers.store') }}" enctype="multipart/form-data">
        @include('admin.influencers._form')
    </form>
@endsection
