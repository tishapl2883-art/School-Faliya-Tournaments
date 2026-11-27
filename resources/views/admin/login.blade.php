@extends('layouts.app')
@section('title', 'Admin Login')
@section('content')
<h1 class="title">Admin Login</h1>
<form class="card form narrow" method="post" action="{{ route('login') }}">
    @csrf
    <label class="full">Email<input type="email" name="email" value="{{ old('email') }}" required autofocus></label>
    <label class="full">Password<input type="password" name="password" required></label>
    <label class="full check"><input type="checkbox" name="remember" value="1"> Remember me</label>
    <button class="btn gold full">Login</button>
</form>
@endsection
