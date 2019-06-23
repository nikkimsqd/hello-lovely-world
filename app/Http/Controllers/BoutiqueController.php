<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\File;
use App\Category;
use App\User;
use App\Boutique;
use App\Rent;
use App\Tag;
use App\Declinedtransaction;
use App\Prodtag;
use App\Order;
use App\Categoryrequest;
use App\Mto;
use App\Measurementtype;
use App\Measurement;
use App\MeasurementRequest;
use App\Categorymeasurement;
use App\Province;
use App\Region;
use App\City;
use App\Barangay;
use App\RefProvince;
use App\RefRegion;
use App\RefCity;
use App\RefBrgy;
use App\Rentableproduct;
use App\Fabric;
use App\Notifications\RentRequest;
use App\Notifications\NewCategoryRequest;
use App\Notifications\ContactCustomer;
use App\Notifications\MtoUpdateForCustomer;
use App\Notifications\RentApproved;
use App\Notifications\RentUpdateForCustomer;
use App\Notifications\BoutiqueDeclinesMto;
use Sample\PayPalClient;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;


class BoutiqueController extends Controller
{

	public function viewNotifications($notificationID)
	{
		$page_title = "Notification";
   		$id = Auth()->user()->id;
		$user = User::find($id);
    	$boutique = Boutique::where('userID', $id)->first();
		$notifications = $user->notifications;
		$notificationsCount = $user->unreadNotifications->count();
		// dd($notifications);

		foreach($notifications as $notification) {
			if($notification->id == $notificationID) {

				if($notification->type == 'App\Notifications\RentRequest'){
					$notif = $notification;
					$notification->markAsRead();

					$rent = Rent::where('rentID', $notif->data['rentID'])->first();

					// return view('boutique/rentNotification', compact('page_title', 'boutique', 'user', 'notifications', 'notificationsCount', 'rent'));
					return redirect('/rents/'.$rent['rentID']);

				}elseif ($notification->type == 'App\Notifications\NewMTO') {
					$notif = $notification;
					$notification->markAsRead();
					$mto = Mto::where('id', $notif->data['mtoID'])->first();

					// return view('boutique/mtoNotification', compact('page_title', 'boutique', 'user', 'notifications', 'notificationsCount', 'mto'));
					return redirect('/made-to-orders/'.$mto['id']);

				}elseif ($notification->type == 'App\Notifications\CustomerAcceptsOffer') {
					$notif = $notification;
					$notification->markAsRead();
					$mto = Mto::where('id', $notif->data['mtoID'])->first();

					// return view('boutique/mtoNotification', compact('page_title', 'boutique', 'user', 'notifications', 'notificationsCount', 'mto'));
					return redirect('/made-to-orders/'.$mto['id']);
				}
			}
		}

		
	}

	public function getnotifications()
	{
		$page_title = "Notification";
   		$id = Auth()->user()->id;
		$user = User::find($id);
    	$boutique = Boutique::where('userID', $id)->first();
		$notifications = $user->notifications;
		$notificationsCount = $user->unreadNotifications->count();
	}

	public function dashboard()
	{
		if(Auth()->user()->roles == "boutique") {
			$page_title = "Dashboard";
	   		$id = Auth()->user()->id;
			$user = User::find($id);
	    	$boutique = Boutique::where('userID', $id)->first();

			$rents = Rent::where('boutiqueID', $boutique['id'])->get();

			$notifications = $user->notifications;
			$notificationsCount = $user->unreadNotifications->count();
			// dd($notifications);

			// foreach($notifications as $notification) {
			// 	foreach ($notification['data'] as $value) {
			// 		dd($notification['data']);
			// 	}
			// }

	        $rentArray = $rents->toArray();
	        array_multisort(array_column($rentArray, "created_at"), SORT_DESC, $rentArray);

			return view('boutique/dashboard',compact('user', 'boutique', 'rents' ,'customer', 'page_title', 'notifications', 'notificationsCount')); 
		}else {
			return redirect('/shop');
		}
	}

    public function showProducts()
	{
		if(Auth()->user()->roles == "boutique") {
			$page_title = "Products";
	   		$user = Auth()->user()->id;
			$boutique = Boutique::where('userID', $user)->first();
			$products = Product::where('boutiqueID', $boutique['id'])->get();
			$productCount = Product::where('boutiqueID', $boutique['id'])->get()->count();
			$notifications = Auth()->user()->notifications;
			$notificationsCount = Auth()->user()->unreadNotifications->count();

			return view('boutique/products', compact('products', 'boutique', 'user', 'productCount', 'page_title', 'notifications', 'notificationsCount'));
		}else {
			return redirect('/shop');
		}
	}

	public function addProduct()
	{
		if(Auth()->user()->roles == "boutique") {
			$page_title = "Add Product";
			$user = Auth()->user()->id;
			$boutique = Boutique::where('userID', $user)->first();
			$categories = Category::all();
			$tags = Tag::all();
			$notifications = Auth()->user()->notifications;
			$notificationsCount = Auth()->user()->unreadNotifications->count();
	        $regions = Region::all();

			// foreach ($boutiques as $boutique) {
			// 	$boutique;
			// }


			return view('boutique/addProducts', compact('categories', 'boutique', 'user', 'tags', 'page_title', 'notifications', 'notificationsCount','regions'));
		}else {
			return redirect('/shop');
		}
	}

	public function getProvince($regCode)
    {
        $userid = Auth()->user()->id;
        $provinces = Province::where('regCode', $regCode)->get();

        return response()->json(['provinces' => $provinces]);
    }

	public function getCity($provCode)
    {
        $userid = Auth()->user()->id;
        $cities = City::where('provCode', $provCode)->get();
        
        return response()->json(['cities' => $cities]);
    }

    public function getBrgy($citymunCode)
    {
        $barangays = Barangay::where('citymunCode', $citymunCode)->orderBy('brgyDesc', 'ASC')->get();
        
        // $brgys = Brgy::where('citymunCode', $citymunCode)->orderBy('brgyDesc', 'ASC')->get();

        return response()->json(['brgys' => $barangays]);
    }

	public function saveProduct(Request $request)
	{
    	$id = Auth()->user()->id;
		$boutique = Boutique::where('userID', $id)->first();
    	
    	$product = Product::create([
    		'boutiqueID' => $boutique['id'],
    		'productName' => $request->input('productName'),
    		'productDesc' => $request->input('productDesc'),
    		'price' => $request->input('retailPrice'),
    		'category' => $request->input('category'),
    		'productStatus' => "Available"
    		]);

		// $arrayloc = array();
		// array_push($arrayloc, $request->input('locationsAvailable'));
		// $locations = json_encode($arrayloc);
		// $locs = json_decode($locations);
		// dd($locs);

    	if($request->input('rentPrice') != null){
			$locations = json_encode($request->input('locationsAvailable'));
	    	$rp = Rentableproduct::create([
	    		'price' => $request->input('rentPrice'),
	    		'depositAmount' => $request->input('depositAmount'),
	    		'penaltyAmount' => $request->input('penaltyAmount'),
	    		'limitOfDays' => $request->input('limitOfDays'),
	    		'fine' => $request->input('fine'),
	    		'locationsAvailable' => $locations
	    	]);

	    	$product->update([
	    		'rpID' => $rp['id']
	    	]);
    	}

    	$tags = $request->input('tags');
        foreach($tags as $tag) {
	    	Prodtag::create([
	    		'tagID' => $tag,
	    		'productID' => $product['id']
	    	]);
		}


		$randomKey = str_random(10);
		// dd($randomKey);

    	$uploads = $request->file('file');
    	if($request->hasFile('file')) {
    	foreach($uploads as $upload){
    		$files = new File();
    		// $name = $upload->getClientOriginalName();
	        $destinationPath = public_path('uploads');
	        $random = substr(sha1(mt_rand().microtime()), mt_rand(0,35),7).$upload->getClientOriginalName();
	        $filename = $destinationPath.'\\'. $random;
	        $upload->move($destinationPath, $filename);

	       	$files->userID = $id;
	       	$files->productID = $product['id'];
	        $files->filename = "/".$random;
	      	$files->save();
	      	$filename = "/".$random;
    	}
      }

     //  if($request->hasFile('file')) {
    	// foreach($uploads as $upload){
    	// 	$files = new File();
    	// 	$name = $upload->getClientOriginalName();
	    //     $destinationPath = public_path('uploads');
	    //     // $filename = substr(sha1(mt_rand().microtime()), mt_rand(0,35),7).$file->getClientOriginalName();
	    //     $filename = $destinationPath.'\\'. $name;
	    //     $upload->move($destinationPath, $filename);

	    //    	$files->userID = $id;
	    //    	$files->productID = $product['id'];
	    //     $files->filename = "/".$name;
	    //   	$files->save();
	    //   	$filename = "/".$name;
    	// }
     //  }

    	return redirect('/products');
	}

	public function viewProduct($productID)
	{
		if(Auth()->user()->roles == "boutique") {
			$page_title = "View Product";
			$user = Auth()->user()->id;
			$boutiques = Boutique::where('userID', $user)->get();
			$product = Product::where('id', $productID)->first();
			$category = Category::where('id', $product['category'])->first();
			$tags = ProdTag::where('productID', $productID)->get();
			$notifications = Auth()->user()->notifications;
			$notificationsCount = Auth()->user()->unreadNotifications->count();

			foreach ($boutiques as $boutique) {
				$boutique;
			}

			return view('boutique/viewProduct', compact('product', 'category', 'boutique', 'user', 'page_title', 'tags', 'notifications', 
			'notificationsCount'));
		}else {
			return redirect('/shop');
		}
	}

	public function editView($productID)
	{
		if(Auth()->user()->roles == "boutique") {
			$page_title = "Edit Product";
			$user = Auth()->user()->id;
			$boutiques = Boutique::where('userID', $user)->get();
			$product = Product::where('id', $productID)->first();
			$categories = Category::all();
			$tags = Tag::all();
			$prodtags = ProdTag::where('productID', $productID)->get();
			$notifications = Auth()->user()->notifications;
			$notificationsCount = Auth()->user()->unreadNotifications->count();

			foreach ($boutiques as $boutique) {
				$boutique;
			}
			foreach ($categories as $category) {
				$category;
			}

			$mensCategories = Category::where('gender', "Mens")->get();
			$womensCategories = Category::where('gender', "Womens")->get();
			// dd($womensCategories);

			return view('boutique/editView', compact('product', 'categories', 'mensCategories', 'womensCategories', 'boutique', 'user', 'page_title', 'tags', 'prodtags', 'notifications', 'notificationsCount'));
			}else {
			return redirect('/shop');
		}
	}

	public function editProduct($productID, Request $request)
	{
		$id = Auth()->user()->id;
		$boutique = Boutique::where('userID', $id)->first();

		if($request->input('forRent') == null) {
			$rentPrice = null;
		}else {
			$rentPrice = $request->input('rentPrice');
		}

		if($request->input('forSale') == null) {
			$productPrice = null;
		}else {
			$productPrice = $request->input('productPrice');
		}
    	
    	$products = Product::where('productID', $productID)->update([
    		'boutiqueID' => $boutique['id'],
    		'productName' => $request->input('productName'),
    		'productDesc' => $request->input('productDesc'),
    		'productPrice' => $productPrice,
    		'rentPrice' => $rentPrice,
    		'category' => $request->input('category'),
    		'productStatus' => $request->input('productStatus'),
    		'forRent' => $request->input('forRent'),
    		'forSale' => $request->input('forSale'),
    		'customizable' => $request->input('customizable')
    		]);


    	$uploads = $request->file('file');

    	if($request->hasFile('file')) {
    	File::where('productID', $productID)->delete();
    	
    	foreach($uploads as $upload){
    		$files = new File();
    		$name = $upload->getClientOriginalName();
	        $destinationPath = public_path('uploads');
	        $filename = $destinationPath.'\\'. $name;
	        $upload->move($destinationPath, $filename);

	       	$files->userID = $id;
	       	$files->productID = $productID;
	        $files->filename = "/".$name;
	      	$files->save();
	      	$filename = "/".$name;
    	}
      }

      return redirect('viewproduct/'.$productID);

	}

	public function delete($productID)
	{
		$product = Product::where('id', $productID)->delete();

		return redirect('/products');

	}

	public function rents()
	{
		if(Auth()->user()->roles == "boutique") {
	    	$page_title = "Rents";
	    	$id = Auth()->user()->id;
	    	$boutique = Boutique::where('userID', $id)->first();

	    	$rents = Rent::where('boutiqueID', $boutique['id'])->get();
			$pendings = Rent::where('boutiqueID', $boutique['id'])->where('status', 'Pending')->get();
			$inprogress = Rent::where('boutiqueID', $boutique['id'])->where('status', 'In-Progress')->get();
			$ondeliveries = Rent::where('boutiqueID', $boutique['id'])->where('status', 'On Delivery')->get();
			$histories = Rent::where('boutiqueID', $boutique['id'])->whereIn('status', ['Declined', 'Completed'])->get();
			// dd($pendings);
			$notifications = Auth()->user()->notifications;
			$notificationsCount = Auth()->user()->unreadNotifications->count();

			return view('boutique/rents', compact( 'pendings', 'inprogress', 'ondeliveries', 'histories', 'boutique', 'page_title', 'rents', 'notifications', 'notificationsCount'));
		}else {
			return redirect('/shop');
		}
	}

	public function getRentInfo($rentID)
	{
		if(Auth()->user()->roles == "boutique") {
			$page_title = "Rent Information";
	    	$id = Auth()->user()->id;
	    	$boutique = Boutique::where('userID', $id)->first();

			$rent = Rent::where('rentID', $rentID)->first();
        	$measurements = json_decode($rent->measurement->data);

			$notifications = Auth()->user()->notifications;
			$notificationsCount = Auth()->user()->unreadNotifications->count();
		
			return view('boutique/rentinfo', compact('rent', 'boutique', 'page_title', 'notifications', 'notificationsCount', 'measurements'));
		}else {
			return redirect('/shop');
		}
	}

	public function approveRent(Request $request)
	{
		$id = Auth()->user()->id;
    	$boutique = Boutique::where('userID', $id)->first();
		$currentDate = date('Y-m-d');
		$rentID = $request->input('rentID');
		$customerID = $request->input('customerID');
		$customer = User::where('id', $customerID)->first();
		$rent = rent::where('rentID', $rentID)->first();

		$product = Product::where('id', $rent['productID'])->update([
			'productStatus' => "Not Available"
		]);

		Rent::where('rentID', $rentID)->update([
			'approved_at' => $currentDate,
			'status' => "In-Progress"
		]);
    	

    	$customer->notify(new RentApproved($rentID, $boutique['boutiqueName']));

		return redirect('/rents/'.$rent['rentID']);
	}

	// public function updateRentInfo(Request $request)
	// {
	// 	$id = Auth()->user()->id;
 //    	$boutique = Boutique::where('userID', $id)->first();
	// 	$customerID = $request->input('customerID');
	// 	$customer = User::where('id', $customerID)->first();
	// 	$rentID = $request->input('rentID');
	// 	$rent = Rent::where('rentID', $rentID)->first();
	// 	$newTotal = $rent['total'] + $request->input('amountDeposit');
	
	// 	// dd($request->input('amountPenalty'));

	// 	$rent->update([
	// 		'dateToBeReturned' => $request->input('dateToBeReturned'),
	// 		'amountDeposit' => $request->input('amountDeposit'),
	// 		'amountPenalty' => $request->input('amountPenalty'),
	// 		'total' => $newTotal
	// 	]);
		
 //    	$customer->notify(new RentUpdateForCustomer($rentID, $boutique['boutiqueName']));

	// 	return redirect('rents/'.$rentID);
	// }

	// public function declineRent(Request $request)
	// {
	// 	$declinedrent = DeclinedRent::create([
	// 		'rentID' => $request->input('rentID'),
	// 		'reason' => $request->input('reason')
	// 	]);
	// 	// dd($declinedrent);

	// 	Rent::where('rentID', $request->input('rentID'))->update([
	// 		'status' => "Declined"
	// 	]);

	// 	return redirect('/rents');
	// }

	// public function makeOrderforRent(Request $request)
	// {
	// 	$rentID = $request->input('rentID');
	
	// 	$rent = Rent::where('rentID', $rentID)->first();
	// 	$order = Order::create([
	// 		'subtotal' => $rent['subtotal'],
	// 		'deliveryfee' => $rent['deliveryFee'],
	// 		'total' => $rent['total'],
	// 		'boutiqueID' => $rent['boutiqueID'],
	// 		'deliveryAddress' => $rent->address['id'],
	// 		'status' => 'For Pickup',
	// 		'rentID' => $rent['rentID'],
	// 		'userID' => $rent['customerID'],
	// 		'paymentStatus' => $rent['paymentStatus']
	// 	]);
	// 	// dd($order['id']);

	// 	$rent->update([
	// 		'orderID' => $order['id'],
	// 		'status' => 'For Pickup'
	// 		]);

	// 	return redirect('rents/'.$rentID);
	// }

	public function rentReturned($rentID)
	{
		$currentDate = date('Y-m-d');
		$rent = Rent::where('rentID', $rentID)->first();
        $rent->update([
        	'completed_at' => $currentDate,
            'status' => "Completed"
        ]);

        $order = Order::where('rentID', $rentID)->update([
        	'status' => "Completed"
        ]);

        return redirect('/rents/'.$rent['$rentID']);
	}

	public function getwomens()
	{
		if(Auth()->user()->roles == "boutique") {
			$page_title = "Womens";
	   		$user = Auth()->user()->id;
			$boutique = Boutique::where('userID', $user)->first();
			// $products = Product::where('gender', 'Womens')->get();
			$products = Product::all();
			// $productCount = Product::where('gender', 'Womens')->get()->count();
			$notifications = Auth()->user()->notifications;
			$notificationsCount = Auth()->user()->unreadNotifications->count();

			foreach($products as $product){
				dd($products->getCategory);
			}

			return view('boutique/products',compact('products', 'boutique', 'user', 'productCount', 'page_title', 'notifications', 'notificationsCount'));
		}else {
			return redirect('/shop');
		}
	}

	public function getmens()
	{
		if(Auth()->user()->roles == "boutique") {
			$page_title = "Mens";
	   		$user = Auth()->user()->id;
			$boutique = Boutique::where('userID', $user)->first();
			$products = Product::where('gender', 'Mens')->get();
			$productCount = Product::where('gender', 'Mens')->get()->count();
			$notifications = Auth()->user()->notifications;
			$notificationsCount = Auth()->user()->unreadNotifications->count();

			return view('boutique/products',compact('products', 'boutique', 'user', 'productCount', 'page_title', 'notifications', 'notificationsCount'));
		}else {
			return redirect('/shop');
		}
	}

	public function getembellishments()
	{
		if(Auth()->user()->roles == "boutique") {
			$page_title = "Embellishments";
	   		$user = Auth()->user()->id;
			$boutique = Boutique::where('userID', $user)->first();
			$products = Product::where('gender', 'Mens')->get();
			$productCount = Product::where('gender', 'Mens')->get()->count();
			$notifications = Auth()->user()->notifications;
			$notificationsCount = Auth()->user()->unreadNotifications->count();

			return view('boutique/products',compact('products', 'boutique', 'user', 'productCount', 'page_title', 'notifications', 'notificationsCount'));
		}else {
			return redirect('/shop');
		}
	}

	public function getcustomizables()
	{
		if(Auth()->user()->roles == "boutique") {
			$page_title = "Customizable Items";
	   		$user = Auth()->user()->id;
			$boutique = Boutique::where('userID', $user)->first();
			$products = Product::where('customizable', 'Yes')->get();
			$productCount = Product::where('customizable', 'Yes')->get()->count();
			$notifications = Auth()->user()->notifications;
			$notificationsCount = Auth()->user()->unreadNotifications->count();

			return view('boutique/products',compact('products', 'boutique', 'user', 'productCount', 'page_title', 'notifications', 'notificationsCount'));
		}else {
			return redirect('/shop');
		}
	}

	public function categories()
	{
		$page_title = "Categories";
		$user = Auth()->user()->id;
		$boutique = Boutique::where('userID', $user)->first();
		$categories = Category::all();
		$womens = Category::where('gender', "Womens")->get();
		$mens = Category::where('gender', "Mens")->get();

		$notifications = Auth()->user()->notifications;
		$notificationsCount = Auth()->user()->unreadNotifications->count();

		return view('boutique/categories', compact('user', 'categories','womens', 'mens', 'page_title', 'boutique', 'notifications', 'notificationsCount'));
	}

	public function requestCategory(Request $request)
	{
		$user = Auth()->user()->id;
		$boutique = Boutique::where('userID', $user)->first();

		$categoryRequest = Categoryrequest::create([
			'boutiqueID' => $boutique['id'],
			'categoryName' => $request->input('categoryName'),
			'gender' => $request->input('gender'),
			'status' => "Pending"
		]);

		$admin = User::where('roles', 'admin')->first();
        $admin->notify(new NewCategoryRequest($categoryRequest['id']));

        return redirect('/categories');
	}

	public function tags()
	{

	}

	public function madeToOrders()
	{
    	$page_title = "Made-to-Orders";
   		$id = Auth()->user()->id;
		$boutique = Boutique::where('userID', $id)->first();
		$notifications = Auth()->user()->notifications;
		$notificationsCount = Auth()->user()->unreadNotifications->count();
		$mtos = Mto::where('boutiqueID', $boutique['id'])->get();


		// $pendings = Mto::where('boutiqueID', $boutique['id'])->where('status', "Pending")->get();
		// $intransactions = Mto::where('boutiqueID', $boutique['id'])->where('status', "In-Transaction")->get();
		// $inprogress = Mto::where('boutiqueID', $boutique['id'])->where('status', "In-Progress")->get();

		// dd($inprogress);

		return view('boutique/madetoorders',compact('boutique', 'page_title', 'notifications', 'notificationsCount', 'mtos'));
	}

    public function getMadeToOrder($mtoID)
    {
    	$page_title = "View Made-to-Order";
        $id = Auth()->user()->id;
		$boutique = Boutique::where('userID', $id)->first();
		$notifications = Auth()->user()->notifications;
		$notificationsCount = Auth()->user()->unreadNotifications->count();
		$mto = Mto::where('id', $mtoID)->first();
		$fabrics = Fabric::where('boutiqueID', $boutique['id'])->get();
        $fabs = $fabrics->groupBy('name');

		// $measurementNames = Categorymeasurement::where('categoryID', $mto['categoryID'])->get();
		$measurements = Measurement::where('typeID', $mto['id'])->first();


        return view('boutique/madetoorderInfo', compact('boutique', 'page_title', 'notifications', 'notificationsCount', 'mto', 'measurements', 'fabs', 'fabrics'));
    }

    public function halfapproveMto($mtoID)
    {
		$mto = Mto::where('id', $mtoID)->first();

		Mto::where('id', $mtoID)->update([
			'status' => 'In-Transaction'
		]);

		$customer = $mto->customer;
        $customer->notify(new ContactCustomer($mto['id'], $mto->boutique['boutiqueName']));

		return redirect('/made-to-orders/'.$mto['id']);    	
    }

    public function addPrice(Request $request)
    {
    	$mtoID = $request->input('mtoID');
		$mto = Mto::where('id', $mtoID)->first();
    	$customer = $mto->customer;
	
    	Mto::where('id', $mtoID)->update([
    		'price' => $request->input('price')
    	]);

        $customer->notify(new MtoUpdateForCustomer($mtoID, $mto->boutique['boutiqueName']));

    	return redirect('/made-to-orders/'.$mtoID);
    }

    public function recommendFabric(Request $request)
    {
    	$mtoID = $request->input('mtoID');
		$mto = Mto::where('id', $mtoID)->first();

		$fabricSuggestion = $request->input('fabricSuggestion');
        $fabSuggestion = json_encode($fabricSuggestion);
        // dd($fabSuggestion);

    	Mto::where('id', $mtoID)->update([
    		'fabricSuggestion' => $fabSuggestion
    	]);
    	return redirect('/made-to-orders/'.$mtoID);
    }

    public function acceptMto($mtoID)
    {
    	$mto = Mto::where('id', $mtoID)->first();
    	$mto->update([
    		'status' => 'In-Progress'
    	]);

    	return redirect('/made-to-orders/'.$mto['id']);
    }

    public function declineMto(Request $request)
    {
    	$mtoID = $request->input('mtoID');
    	$mto = Mto::where('id', $mtoID)->first();

    	$dt = Declinedtransaction::create([
    		'type' => 'mto',
    		'typeID' => $mtoID,
    		'reason' => $request->input('reason')
    	]);

    	Mto::where('id', $mtoID)->update([
    		'status' => $dt['id']
    	]);

    	$customer = User::where('id', $mto->customer['id'])->first();
        $customer->notify(new BoutiqueDeclinesMto($mto));

    	return redirect('/made-to-orders/'.$mtoID);
    }

    public function submitMTO($mtoID)
    {
    	$mto = Mto::where('id', $mtoID)->first();
    	$mto->update([
    		'status' => 'For Pickup'
    	]);

    	$order = Order::create([
    		'userID' => $mto['userID'],
    		'subtotal' => $mto['subtotal'],
    		'deliveryfee' => $mto['deliveryFee'],
    		'total' => $mto['total'],
    		'boutiqueID' => $mto['boutiqueID'],
    		'deliveryAddress' => $mto['deliveryAddress'],
    		'status' => $mto['status'],
    		'paymentStatus' => $mto['paymentStatus'],
    		'mtoID' => $mto['id']
    	]);

    	$mto->update([
    		'orderID' => $order['id']
    	]);

    	return redirect('made-to-orders/'.$mtoID);
    }

    public function getOrders()
    {
    	$page_title = "Orders";
   		$id = Auth()->user()->id;
		$boutique = Boutique::where('userID', $id)->first();
		$notifications = Auth()->user()->notifications;
		$notificationsCount = Auth()->user()->unreadNotifications->count();
		$orders = Order::where('boutiqueID', $boutique['id'])->where('cartID', '!=', null)->get();

		return view('boutique/orders', compact('page_title', 'boutique', 'notifications', 'notificationsCount', 'orders'));
    }

    public function getOrder($orderID)
    {
    	$page_title = "View Order";
   		$id = Auth()->user()->id;
		$boutique = Boutique::where('userID', $id)->first();
		$notifications = Auth()->user()->notifications;
		$notificationsCount = Auth()->user()->unreadNotifications->count();
		$order = Order::where('id', $orderID)->first();

		return view('boutique/orderinfo', compact('page_title', 'boutique', 'notifications', 'notificationsCount', 'order'));
    }

    public function submitOrder($orderID)
    {
    	$order = Order::where('id', $orderID)->first();
    	$order->update([
    		'status' => 'For Pickup'
    	]);

    	return redirect('/orders/'.$order['id']);
    }

  //   public function requestCustomer(Request $request)
  //   {
  //   	$id = Auth()->user()->id;
		// $boutique = Boutique::where('userID', $id)->first();
  //   	$customer = User::where('id', $request->input('customerID'))->first();

  //   	$mtoID = $request->input('mtoID');
  //   	$measurement = $request->input('measurements');
  //       $data = json_encode($measurement);

  //   	$measurementrequest = Measurementrequest::create([
  //   		'mtoID' => $mtoID,
  //   		'mtID' => $data
  //   	]);
        
  //       $customer->notify(new MeasurementRequests($measurementrequest['id'], $boutique['boutiqueName']));

  //   	return redirect('/made-to-orders/'.$mtoID);
  //   }

    public static function getPaypalOrder($orderId)
    {
    	$page_title = "Paypal Order";
   		$id = Auth()->user()->id;
		$user = User::find($id);
    	$boutique = Boutique::where('userID', $id)->first();
		$notifications = $user->notifications;
		$notificationsCount = $user->unreadNotifications->count();

    	$rent = Rent::where('paypalOrderID', $orderId)->first();
    	$mto = Mto::where('paypalOrderID', $orderId)->first();
    	// $order = Order::where('paypalOrderID', $orderId)->first();

    	if($rent != null){
    		$client = PayPalClient::client();
	        $response = $client->execute(new OrdersGetRequest($orderId));
	        $order = $response->result;

        	return view('boutique/paypalOrderDetails', compact('user', 'boutique', 'page_title', 'notifications', 'notificationsCount', 'rent', 'mto', 'order'));

    	}elseif($mto != null){
    		$client = PayPalClient::client();
	        $response = $client->execute(new OrdersGetRequest($orderId));
	        $order = $response->result;
        	
        	return view('boutique/paypalOrderDetails', compact('user', 'boutique', 'page_title', 'notifications', 'notificationsCount', 'rent', 'mto', 'order'));
    	}
    }

    public function fabrics()
    {
    	$page_title = "Paypal Order";
   		$id = Auth()->user()->id;
		$user = User::find($id);
    	$boutique = Boutique::where('userID', $id)->first();
		$notifications = $user->notifications;
		$notificationsCount = $user->unreadNotifications->count();
		$fabrics = Fabric::where('boutiqueID', $boutique['id'])->get();

    	return view('boutique/fabrics', compact('user', 'boutique', 'page_title', 'notifications', 'notificationsCount', 'fabrics'));
    }

    public function addFabric(Request $request)
    {
   		$id = Auth()->user()->id;
    	$boutique = Boutique::where('userID', $id)->first();

    	$name = ucfirst($request->input('fabricName'));
    	$color = ucfirst($request->input('fabricColor'));
    	$nameQuery = Fabric::where('name', $name)->first();
    	$colorQuery = Fabric::where('color', $color)->first();

	    	// dd($colorQuery);
	    // if(empty($nameQuery) && !empty($colorQuery)){
	    // 	dd($nameQuery);
	    // }
	    // elseif($nameQuery != null && $colorQuery != null){
	    // 	dd($colorQuery);
	    // }else{
	    // 	dd("oops");
	    // }

    	// if($nameQuery == null && $colorQuery == null){
	    // 	Fabric::create([
	    // 		'boutiqueID' => $boutique['id'],
	    // 		'name' => $name,
	    // 		'color' => $color
	    // 	]);
	    // }elseif($nameQuery != null && $colorQuery == null){
	    	Fabric::create([
	    		'boutiqueID' => $boutique['id'],
	    		'name' => $name,
	    		'color' => $color
	    	]);
	    // }

    	return redirect('/fabrics');
    }

}