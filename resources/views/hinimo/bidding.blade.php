@extends('layouts.hinimo')
@extends('hinimo.sections')


@section('titletext')
	Hinimo | {{$page_title}}
@endsection


@section('body')
<div class="breadcumb_area breadcumb-style-two bg-img" style="background-image: url(essence/img/bg-img/breadcumb2.jpg);">
        <div class="container h-100">
            <div class="row h-100 align-items-center">
                <div class="col-12">
                    <div class="page-title text-center">
                        <h2>{{$page_title}}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- ##### Blog Wrapper Area Start ##### -->
    <div class="blog-wrapper section-padding-80">
        <div class="container">
            <div class="row">
               
                <!-- Single Blog Area -->
                <div class="col-12 col-lg-6">
                    <div class="single-blog-area mb-50">
                        <img src="{{asset('short/x.jpg')}}" alt="">
                        <!-- Post Title -->
                        <div class="post-title">
                            <a href="#">Vivamus sed nunc in arcu cursus mollis quis et orci. Interdum et malesuada</a>
                        </div>
                        <!-- Hover Content -->
                        <div class="hover-content">
                            <!-- Post Title -->
                            <div class="hover-post-title">
                                <a href="#">Vivamus sed nunc in arcu cursus mollis quis et orci. Interdum et malesuada</a>
                            </div>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce enim nulla, mollis eu metus in, sagittis fringilla tortor. Phasellus purus dignissim convallis.</p>
                            <a href="#">Continue reading <i class="fa fa-angle-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection