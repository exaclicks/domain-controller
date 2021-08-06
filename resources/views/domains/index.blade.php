@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pull-left">
                <h2>Domain List </h2>
            </div>
            <div class="pull-right">
                <a class="btn btn-success" href="{{ route('domains.create') }}" title="Create a domain"> <i class="fas fa-plus-circle"></i>
                    </a>
            </div>
        </div>
    </div>

    @if ($message = Session::get('success'))
        <div class="alert alert-success">
            <p>{{ $message }}</p>
        </div>
    @endif

    <table class="table table-bordered table-responsive-lg">
        <tr>
            <th>id</th>
            <th>Name</th>
            <th>Hosting</th>
            <th>Status</th>
            <th>Bought Time</th>
            <th>Finish Time</th>
            <th width="280px">Action</th>
        </tr>
        @foreach ($domains as $domain)
            <tr>
                <td>{{ $domain->id }}</td>
                <td>{{ $domain->name }}</td>
                <td>{{ $domain->hosting }}</td>
                <td>{{ $domain->status }}</td>
                <td>{{$domain->bought_time}}</td>
                <td>{{$domain->finish_time}}</td>
                <td>
                    <form action="{{ route('domains.destroy', $domain->id) }}" method="POST">

                        <a href="{{ route('domains.show', $domain->id) }}" title="show">
                            <i class="fas fa-eye text-success  fa-lg"></i>
                        </a>

                        <a href="{{ route('domains.edit', $domain->id) }}">
                            <i class="fas fa-edit  fa-lg"></i>

                        </a>

                        @csrf
                        @method('DELETE')

                        <button type="submit" title="delete" style="border: none; background-color:transparent;">
                            <i class="fas fa-trash fa-lg text-danger"></i>

                        </button>
                    </form>
                </td>
            </tr>
        @endforeach
    </table>


@endsection
