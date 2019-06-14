@extends('layouts.boutique')
@extends('boutique.sections') 

@section('content')

<section class="content">
  <div class="row">
    <div class="col-md-12">
      <div class="box">
        <div class="box-header">
          <h3 class="box-title">MTO ID: {{$mto['id']}}</h3>
        </div>
        <!-- /.box-header -->

        <div class="box-body">
          <div class="row">
            <div class="col-md-6">
              <?php 
                $measurementData = json_decode($mto->measurement['data']);
                $fabricChoice = json_decode($mto['fabricChoice']);
                $fabricSuggestion = json_decode($mto['fabricSuggestion']);
              ?>

              <h4>Customer Name: <b>{{$mto->customer['fname'].' '.$mto->customer['lname']}}</b></h4>
              <h4>Date request placed: <b>{{$mto['created_at']->format('M d, Y')}}</b></h4>

              <hr>
              <h4><b>MTO Details</b></h4>
              <h4>Date of item's use: <b>{{date('M d, Y',strtotime($mto['dateOfUse']))}}</b></h4>
              <h4>Fabric Choice:</h4>
              @if($mto['fabricID'] != null)
                <h4>Fabric Type: <b>{{$mto->fabric['name']}}</b></h4>
                <h4>Fabric Color: <b>{{$mto->fabric['color']}}</b></h4>
              @elseif($mto['suggestFabric'] != null)
                <h4><i>User wants you to recommend which type of fabric to use.</i></h4>
                <!-- if($mto['suggestFabric'] != null) -->
              @elseif($mto['fabricChoice'] != null)
                @foreach($fabricChoice as $fabChoice =>$value)
                  <h4>{{$fabChoice}}: <b>{{ucfirst($value)}}</b></h4>
                @endforeach
              @endif
              <h4>Customer's Notes/Instructions: <b>{{$mto['notes']}}</b></h4>
              @if($mto['price'] != null)
                <h4>Price: <b>{{$mto['price']}}</b></h4>
              @endif

              <hr>
              <h4><b>Customer's Measurements Details</b></h4>
              @foreach($measurementData as $measurementName => $value)
                <h4>{{$measurementName}}: <b>{{$value}} inches</b></h4>
              @endforeach
              <h4>Customer's Height: <b>{{$mto['height']}} cm</b></h4>

              <hr>
              @if($mto['fabricSuggestion'] != null)
              <h4>Your Fabric Recommendation</h4>
              @foreach($fabrics as $fabric)
              @if($fabric['id'] == $fabricSuggestion->FabricID)
                <h4>Fabric Type: <b>{{$fabric['name']}}</b></h4>
                <h4>Fabric Color: <b>{{$fabric['color']}}</b></h4>
              @endif
              @endforeach
              <h4>Price: <b>{{$fabricSuggestion->price}}</b></h4>

              <!-- @foreach($fabricSuggestion as $fabSuggestion =>$value) -->
                  <!-- <h4>{{$fabSuggestion}}: <b>{{ucfirst($value)}}</b></h4> -->
              <!-- @endforeach -->
              @endif
              <a href="" data-toggle="modal" data-target="#recommendFabricModal">Recommend fabric to use with price here.</a>
              <hr>
              @if($mto['orderID'] == null)
              <form action="{{url('/addPrice')}}" method="post">
                {{csrf_field()}}
                <h4>Add price of item:</h4>
                <input type="number" name="price" class="input form-control"><br>
                <input type="text" name="mtoID" value="{{$mto['id']}}" hidden>

                <input type="submit" name="btn_submit" value="Place Offer" class="btn btn-primary">
              </form>
              @endif

            </div>
            <div class="col-md-6">

              <img src="{{ asset('/uploads/').$mto->productFile['filename'] }}" style="width:80%; height: auto; object-fit: cover;margin: 10px; text-align: right;">
            </div>
          </div>
        </div>

        <div class="box-footer" style="text-align: right;">
            <a href="" data-toggle="modal" data-target="#declineModal" class="btn btn-danger">Decline Request</a>
            <a href="{{url('made-to-orders')}}" class="btn btn-default">Back to MTOs</a>
          @if($mto['status'] == "Pending")
            <a href="" data-toggle="modal" data-target="#declineModal" class="btn btn-danger">Decline Request</a>
            <a href="{{url('halfapproveMto/'.$mto['id'])}}" class="btn btn-primary">Contact customer for negotiations</a>
          @elseif($mto['status'] == "In-Transaction")
            @if($mto['finalPrice'] != null)
            <a href="" data-toggle="modal" data-target="#declineModal" class="btn btn-danger">Decline Request</a>
            <a href="{{url('/acceptMto/'.$mto['id'])}}" class="btn btn-success">Accept Request</a>
            @else
            <a href="" data-toggle="modal" data-target="#declineModal" class="btn btn-danger">Decline Request</a>
            <input type="submit" class="btn btn-success" disabled value="Accept Request">
            @endif
          @elseif($mto['status'] == "In-Progress")
            @if($mto['paymentStatus'] == "Not Yet Paid")
              <input type="submit" class="btn btn-primary" value="For Pickup" disabled>
            @else
              <a href="" class="btn btn-primary" data-toggle="modal" data-target="#forPickupModal">For Pickup</a>
            @endif
          @endif
        </div>

      </div>
    </div>
  </div>
</section>


<!-- RECOMMEND FABRIC -->
<div class="modal fade" id="recommendFabricModal" role="dialog">
  <div class="modal-dialog modal-sm">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title"><b>Recommend a fabric</b></h3>
      </div>

      <div class="modal-body">
        <form action="{{url('/recommendFabric')}}" method="post">
        {{csrf_field()}}

          <h4>Fabric Type:</h4> 
          <select id="fabric-type" class="form-control mb-3">
            <option disabled selected>Choose fabric type</option>
            @foreach($fabs as $fab => $name)
            <option value="{{$fab}}">{{$fab}}</option>
            @endforeach
          </select><br>
          <h4>Fabric Color:</h4> 
          <select id="fabric-color" class="form-control mb-3" name="fabricSuggestion[fabricID]" disabled>
            <option disabled selected="selected">Select Fabric Type first</option>
          </select><br>
          <h4>Price:</h4> 
          <input type="text" name="fabricSuggestion[price]" class="form-control" placeholder="Price">
          <input type="text" name="mtoID" value="{{$mto['id']}}" hidden>
      </div>

      <div class="modal-footer">
        <input type="submit" name="btn_sumbit" class="btn btn-success" value="Submit">
        </form>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<!-- MODAL -->
<div class="modal fade" id="forPickupModal" role="dialog">
    <div class="modal-dialog ">
      <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
          </div>

          <div class="modal-body">
            <p>Submit MTO for Pickup?</p>
            <!-- <input type="text" name="orerID" value="{{$mto['id']}}" hidden> -->
          </div>

          <div class="modal-footer">
            <a href="{{url('submitMTO/'.$mto['id'])}}" class="btn btn-primary">Confirm</a>
          </div>
      </div> 
    </div>
</div>


<!-- DECLINE RENT -->
<div class="modal fade" id="declineModal" role="dialog">
  <div class="modal-dialog modal-lg">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h3 class="modal-title"><b>Submit a reason</b></h3>
      </div>

      <div class="modal-body">
        <form action="{{url('/declineMto')}}" method="post">
        {{csrf_field()}}
        <textarea name="reason" rows="3" cols="50" class="input form-control" placeholder="Place your reason here"></textarea>
        <input type="text" name="mtoID" value="{{$mto['id']}}" hidden>
      </div>

      <div class="modal-footer">
        <input type="submit" name="btn_sumbit" class="btn btn-success" value="Submit">
        </form>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>



<style type="text/css">
  h4{ margin-top: 0;}
  .borderless td, .borderless th {border: none;}
  .measurements {padding-left: 0;}
</style>




@endsection


@section('scripts')

<script type="text/javascript">

$('.transactions').addClass("active");
$('.mtos').addClass("active");

$('#fabric-type').on('change', function(){
    $('#fabric-color').empty();
    $('#fabric-color').append('<option disabled selected="selected">Choose fabric color</option>');
    $('#fabric-color').prop('disabled',false);

    var type = $(this).val();
    $.ajax({
        url: "/hinimo/public/getFabricColor/"+type,
        success:function(data){ 
            data.colors.forEach(function(color){
                $('#fabric-color').append('<option value="'+color.id+'">'+color.color+'</option>');
                // $('#fabric-color').next().find('.list').append('<li data-value="'+color.id+'" class="option">'+color.color+'</li>');
            });
        }
    });
});
</script>

@endsection

