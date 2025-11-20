<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>{{ ucfirst(last(request()->segments())) }}</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fa fa-home"></i></a></li>
                    @foreach(request()->segments() as $segment)
                        <li class="breadcrumb-item @if($loop->last) active @endif">
                            @if($loop->last)
                                {{ ucfirst($segment) }}
                            @else
                                <a href="{{ url(implode('/', array_slice(request()->segments(), 0, $loop->iteration))) }}">{{ ucfirst($segment) }}</a>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
</div>
