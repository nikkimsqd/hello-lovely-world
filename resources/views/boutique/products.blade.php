@extends('layouts.boutique')
@extends('boutique.sections')


@section('content')

<section class="content">
  <div class="row">
    <div class="row">
      <div class="col-md-12">
        <div class="col-md-9">
        
          <div class="total-products">
              <p><span>{{$productCount}}</span> products found</p>
          </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
             <a class="btn btn-block btn-info" href="{{url('addproduct')}}">Add Products here</a>
            </div>
        </div>
      </div>
    </div>

  @if(empty($products))
    <label>You have no products in your store</label>
  @else
  @foreach($products as $product)

    <div class=" col-12 col-sm-6 col-lg-4" style="padding-right: 20px; padding-left: 20px;"> <!-- change to col-lg-3 if dako ra -->
      <div class="box " style="padding: 10px;">
        <div class="box-body">
          <?php $counter = 1; ?>

          @foreach( $product->productFile as $image)

          @if($counter == 1)  
            <img src="{{ asset('/uploads').$image['filepath'] }}" style="width:100%; height: 350px; object-fit: cover;">
          @else
          @endif
          <?php $counter++; ?>
          @endforeach

          <div class="row">
            <a href="{{ url('viewproduct/'.$product['id']) }}">
              <h4>{{ $product['productName'] }}</h4>
            </a>
            <h2></h2>

            <a href="{{ url('viewproduct/'.$product['id']) }}" class="btn btn-block btn-primary">View Product</a>
          </div>
        </div>
      </div>
    </div>
  @endforeach
  @endif
  </div>
</section>

@endsection


@section('scripts')
<script type="text/javascript">

$('.products').addClass("active");
$('.allproducts').addClass("active");

</script>


@endsection
