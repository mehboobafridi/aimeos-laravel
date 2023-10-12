extends('layouts.master')
@section('css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/jq-3.6.0/jszip-2.5.0/dt-1.11.3/b-2.1.1/b-html5-2.1.1/datatables.min.css"/>

@endsection
@section('title') {{ $settings['title'] }} @endsection
@section('content')
<style>
    .dt-buttons{
       margin-bottom: 1.2rem;
    }
    .left-col {
    float: left;
    width: 25%;
}
 
.center-col {
    float: left;
    width: 50%;
}
 
.right-col {
    float: left;
    width: 25%;
}

</style>
<div class="main py-4">
@if (Session::has('message'))
         <div class="alert alert-success">{{ Session::get('message') }}</div>
         @elseif (Session::has('error'))
         <div class="alert alert-danger">{{ Session::get('error') }}</div>
         @endif
    <div class="card card-body border-0 shadow table-wrapper table-responsive" style="min-height:180px;">
       <h2 class="mb-4 h5">{{ $settings['title'] }}</h2>
	<div>Loading...</div>
       <div id="message_box5">
       </div>
    </div>
 </div>
@endsection
@section('script')
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/bs4/jq-3.6.0/jszip-2.5.0/dt-1.11.3/b-2.1.1/b-html5-2.1.1/datatables.min.js"></script>
<script>

$("document").ready(function(){
    setTimeout(function(){
       $("div.alert").remove();
    }, 5000 ); // 5 secs




    function get_progress()
    {
    
        {
            let ur = "{{route('show_progress')}}";
            ur = ur.replace("https", "http").replace("http", "https");

            $.ajax({
                type: 'GET',
                url: ur,
                contentType: 'application/json; charset=utf-8',
                success: function(data) {
                    if(data['__progress__'].includes("http"))
                    {
                        window.location.href = data['__progress__'];
                    }
                    $div = jQuery('<div class="alert alert-success" style="display: none;">' + data['__progress__'] + '</div>');
                    $div.prependTo(jQuery('#message_box5')).fadeIn(500).delay(3000).fadeOut(1000, function() { jQuery(this).remove(); });

                    setTimeout(get_progress, 5000);
                },
                error: function(data) {
                    $div = jQuery('<div class="alert alert-success" style="display: none;">' + data['__progress__'] + '</div>');
                    $div.prependTo(jQuery('#message_box5')).fadeIn(500).delay(3000).fadeOut(1000, function() { jQuery(this).remove(); });

                    setTimeout(get_progress, 5000);
                }
                });
        }           
    
    }


    get_progress();

});

jQuery('document').ready(function() {
    function loading() {

        $.ajax({
                type: 'GET',
                url: '{{ $settings['url'] }}',
                contentType: 'application/json; charset=utf-8',
                success: function(data) {
                    // $div = jQuery('<div class="alert alert-success" style="display: none;">' + data['__progress__'] + '</div>');
                    // $div.prependTo(jQuery('#message_box')).fadeIn(500).delay(3000).fadeOut(1000, function() { jQuery(this).remove(); });
                    $p=0;
                    // setTimeout(get_progress, 5000);
                },
                error: function(data) {
                    // $div = jQuery('<div class="alert alert-success" style="display: none;">' + data['__progress__'] + '</div>');
                    // $div.prependTo(jQuery('#message_box')).fadeIn(500).delay(3000).fadeOut(1000, function() { jQuery(this).remove(); });
                    $p=0;

                    // setTimeout(get_progress, 5000);
                }
                });
 
    }

    loading();

});



</script>


@endsection
