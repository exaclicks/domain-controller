@extends('layouts.app')

@section('content')


<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Content Editor</h2>
        </div>




        <div class="pull-right">
            <form action="{{ route('contents.destroy', $content->id) }}" method="POST">

                @csrf
                @method('DELETE')

                <button type="submit" title="delete" style="border: none; background-color:transparent;">
                    <i class="btn btn-danger">DELETE</i>
                    <br>
                    <small>
                        Eğerki ilgili bahis sitesi kapandıysa, veya uygunsuz içerik ise silebiliriz.
                    </small>
                </button>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript" src="{{ asset('js/select2.js') }}"></script>
<link href="{{ asset('css/select2.min.css') }}" rel="stylesheet">

<script>
    jQuery(document).ready(function() {
        //change selectboxes to selectize mode to be searchable
        jQuery("#select_page").select2();
    });
</script>



<form action="{{ route('contents.update', $content->id) }}" method="POST">
    @csrf
    @method('PUT')



    <div class="row">

        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Title:</strong>
                <textarea class="form-control" style="height:50px" name="last_title" maxlength="60" placeholder="Title">{{ $content->last_title }}</textarea>
                <small>{!! $content->rewriter_title !!}</small>
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Description:</strong>
                <textarea class="form-control" style="height:100px" name="last_description" maxlength="160" placeholder="Description">{{ $content->last_description }}</textarea>
                <small>{!! $content->rewriter_description !!} </small>
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Link:</strong>
                <input class="form-control" type="text" id="last_link" name="last_link" placeholder="Link" value="{{ $content->last_link }}" />
                <small>{!! $content->first_link !!} </small>
            </div>
        </div>


        <div class="col-xs-6 col-sm-6 col-md-6">
            <div class="form-group">
                <strong>Category</strong>
                <br>
                <small>
                    İlgili kategoriyle eşleştirelim.
                </small>
                <br>
                <select name="category_id" id="category_id" style="width:200px;" class="operator">
                    @foreach ($categories as $key => $category_item)
                    @if (isset($category->id))
                    @if ($category_item->id == $category->id)
                    <option value="{{$category_item->id}}" selected>{{$category_item->name}}</option>
                    @else
                    <option value="{{$category_item->id}}">{{$category_item->name}}</option>
                    @endif
                    @else
                    <option value="{{$category_item->id}}">{{$category_item->name}}</option>
                    @endif
                    @endforeach
                </select>

            </div>
        </div>




        <div class="col-xs-6 col-sm-6 col-md-6">
            <div class="form-group">
                <strong>Company</strong>
                <br>
                <small>
                    Burada bu yazının hangi bahis şirketine ait olduğunu seçmemiz gerekiyor, eğerki deneme bonusu veren siteler gibi genel bir yazı ise şirketsiz seçeneği seçilmeli.
                </small>
                <br>

                <select name="bet_company_id" id="bet_company_id" style="width:200px;" class="operator">
                    @foreach ($bet_companies as $key => $bet_company_item)
                    @if (isset($bet_company->id))
                    @if ($bet_company_item->id == $bet_company->id)
                    <option value="{{$bet_company_item->id}}" selected>{{$bet_company_item->name}}</option>
                    @else
                    <option value="{{$bet_company_item->id}}">{{$bet_company_item->name}}</option>
                    @endif
                    @else
                    <option value="{{$bet_company_item->id}}">{{$bet_company_item->name}}</option>
                    @endif
                    @endforeach
                </select>
            </div>
        </div>






        <div class="col-xs-6 col-sm-6 col-md-6">
            <div class="form-group">
                <strong>Old Content</strong>
                <textarea class="ckeditor" id="last_content" name="first_content">{!! $content->first_content !!}</textarea>
            </div>
        </div>




        <div class="col-xs-6 col-sm-6 col-md-6">
            <div class="form-group">
                <strong>Last Content</strong>
                <br>
            
                <button type="submit" title="delete" style="border: none; background-color:transparent;">
                        <i class="btn btn-success">YAZDIR</i>
                    </button>

                    <small>

                        Henüz bu içeriği yeniden yazdırmamışsın. eğer eski içeriği düzenlediysen yeniden yazdırmak için butona tıkla

                    </small>




                <textarea class="ckeditor" id="last_content" name="last_content">
                @if ($content->last_content)
                {{ $content->last_content }}

                @else
                {{ $content->rewriter_content }}

                @endif

                </textarea>
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12 text-center">
            <button type="submit" class="btn btn-primary">Publish</button>
        </div>
    </div>

</form>


<script type="text/javascript">
    $(document).ready(function() {
        $('.ckeditor').ckeditor();
        $('#rewriter_content').ckeditor();

    });
</script>

@endsection