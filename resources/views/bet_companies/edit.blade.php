@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Edit</h2>
        </div>
        <div class="pull-right">
            <a class="btn btn-primary" href="{{ route('bet_companies.index') }}" title="Go back"> <i class="fas fa-backward "></i> </a>
        </div>
    </div>
</div>



<form action="{{ route('bet_companies.update', $bet_company->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>name:</strong>
                <input type="text" name="name" value="{{ $bet_company->name }}" class="form-control" placeholder="Name">
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>status:</strong>
                <textarea class="form-control" style="height:50px" name="status" placeholder="status">{{ $bet_company->status }}</textarea>
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>type:</strong>
                <textarea class="form-control" style="height:50px" name="type" placeholder="type">{{ $bet_company->type }}</textarea>
            </div>
        </div>

    </div>

    <div class="col-xs-12 col-sm-12 col-md-12 text-center">
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
    </div>

</form>
@endsection