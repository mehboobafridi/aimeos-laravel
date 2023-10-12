 <!-- start page title -->
<div class="row">
    <div class="col-md-1"></div>
    <div class="col-md-3">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            @if (isset($title))
            <h4 class="mb-0">{{ $title }}</h4>
                
            @endif

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    @if(isset($li_1))
                    <li class="breadcrumb-item active">{{ $li_1 }}</li>
                    @endif
                    @if(isset($li_2))
                    <li class="breadcrumb-item">{{ $li_2 }}</li>
                    @endif
                </ol>
            </div>
            
        </div>
    </div>
    <div class="col-md-8">
    </div>
</div>     
<!-- end page title -->