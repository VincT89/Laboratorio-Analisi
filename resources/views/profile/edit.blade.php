@extends('layouts.app')
@section('title', 'Il mio Profilo')

@section('breadcrumb')
    <span>Il mio Profilo</span>
@endsection

@section('content')
<div class="profile-wrap">
    <div class="profile-section">
        @include('profile.partials.update-profile-information-form')
    </div>
    <div class="profile-section">
        @include('profile.partials.update-password-form')
    </div>
</div>
@endsection