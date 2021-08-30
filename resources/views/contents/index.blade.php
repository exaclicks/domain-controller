@extends('layouts.app')


@section('content')





<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Content List </h2>
        </div>
    </div>
</div>



<table class="table table-bordered data-table">
        <thead>
            <tr>
             <th>id</th>
             <th>First Title</th>
             <th>Last Title</th>
             <th>Status</th>
             <th width="280px">Action</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>


    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" />
    <link href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" ></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap5.min.js" ></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js" ></script>  
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js "  ></script>

  

    
    <script type="text/javascript">
  $(function () {
      
    var table = $('.data-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
          url: "{{ route('contents.index') }}",
          data: function (d) {
                d.approved = $('#approved').val(),
                
                d.search = $('input[type="search"]').val()
            }
        },
        columns: [
            {data: 'id', name: 'id'},
            {data: 'first_title', name: 'first_title'},
            {data: 'last_title', name: 'last_title'},
            {data: 'status_text', name: 'status_text'},
            {data: 'action', name: 'action'},

        ]
    });
  
    $('#approved').change(function(){
        table.draw();
    });
      
  });
</script>
@endsection
