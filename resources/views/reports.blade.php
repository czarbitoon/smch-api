@extends('layouts.app')

@section('content')
<form hx-post="/reportDevice">
    <!-- Add device_id and issue_description fields -->
    <input type="hidden" name="device_id" value="...">
    <input type="text" name="issue_description" required>
    <button type="submit">Report Device</button>
</form>
@endsection
