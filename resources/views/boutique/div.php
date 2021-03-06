@extends('layouts.boutique')
@extends('boutique.sections')


@section('content')
<section class="content">
  <div class="row">
    <div class="col-md-12">
      <div class="box box-success">

        <div class="box-header with-border">
          <h3 class="box-title">Categories</h3>
        </div>

        <div class="box-body">
          <div class="col-md-5"> 
          </div>

          <div class="col-md-5">
          </div>

        </div>
        <div class="box-footer" style="text-align: right;">
         <a class="btn btn-warning" href="/hinimo/public/dashboard/"><i class="fa fa-arrow-left"> Back to dasboard</i></a>
         <a class="btn btn-primary" href="/hinimo/public/addCategories/"><i class="fa fa-plus"> Add a Category</i></a>
        </div>
      </div>
    </div>
  </div>
</section>


<!-- collapsible box -->
<div class="box">
  <div class="box-header with-border">
    <h3 class="box-title">Title</h3>

    <div class="box-tools pull-right">
      <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip"
              title="Collapse">
        <i class="fa fa-minus"></i></button>
      <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
        <i class="fa fa-times"></i></button>
    </div>
  </div>
  <div class="box-body">
    Start creating your amazing application!
  </div>
  <!-- /.box-body -->
  <div class="box-footer">
    Footer
  </div>
  <!-- /.box-footer-->
</div>



<!-- MODAL -->
<a href="" class="btn essence-btn" data-toggle="modal" data-target="#madeToOrderModal">[name here]</a>

<div class="modal fade" id="refundCustomer" role="dialog">
  <div class="modal-dialog ">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h3 class="modal-title"><b>Confirm Alterations</b></h3>
      </div>

      <!-- <form action="{{url('submitOrder')}}" method="post"> -->
        <!-- {{csrf_field()}} -->
        <div class="modal-body">
          <p>Did client show up at scheduled fittings? </p>
          <input type="text" name="orderID" value="{{$order['id']}}" hidden>
        </div>

        <div class="modal-footer">
          <!-- <a href="" class="btn btn-default" id="noAlterations">No</a>
          <a href="" class="btn btn-primary" id="yesAlterations">Yes</a> -->
          <input type="text" id="alterationID" value="{{$order['alterationID']}}">
          <input type="submit" id="noAlterations" name="btn_submit" class="btn btn-default" value="No">
          <input type="submit" id="yesAlterations" name="btn_submit" class="btn btn-primary" value="Yes">
        </div>
      <!-- </form> -->
    </div> 
  </div>
</div>

@endsection


    $userID = Auth()->user()->id;
    $user = User::find($userID);
    $page_title = 'Biddings';
    $boutique = Boutique::where('userID', $userID)->first();
    $notifications = $user->notifications;
    $notificationsCount = $user->unreadNotifications->count();
    $biddingsCount = Bidding::all()->count();

    return view('boutique/viewBidding', compact('userID', 'user', 'page_title', 'biddingsCount', 'boutique', 'notificationsCount', 'notifications', 'biddingsCount'));