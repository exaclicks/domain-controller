@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Unused Domain List </h2>
        </div>
        <div class="pull-right">
            <a class="btn btn-success margin-bottom-1 mb-1" href="{{ route('un_used_domain_create') }}" title="Create a domain">Create</i>
            </a>

        </div>
    </div>
</div>



<table class="table table-bordered table-responsive-lg">
    <tr>
        <th>id</th>
        <th>Name</th>
    </tr>
    @foreach ($domains as $domain)
    <tr>
        <td>{{ $domain->id }}</td>
        <td>{{ $domain->name }}</td>
    </tr>
    @endforeach
</table>


@endsection