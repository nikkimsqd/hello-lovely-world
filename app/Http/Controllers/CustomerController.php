<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Category;
use App\Product;
use App\Cart;
use App\User;
use App\City;
use App\Barangay;
use App\Address;
use App\Boutique;
use App\Rent;
use App\Profiling;
use App\Bidding;
use App\Bid;
use App\Prodtag;
use App\Tag;
use App\Order;
use App\File;
use App\Mto;
use App\Measurement;
// use App\MeasurementRequest;
use App\Categorymeasurement;
use App\Cartitem;
use App\Fabric;
use App\Sharepercentage;
use App\Gallery;
use App\Set;
use App\Measurementrequest;
use App\Notifications\RentRequest;
use App\Notifications\NewMTO;
use App\Notifications\CustomerAcceptsOffer;
use App\Notifications\CustomerCancelMto;
use App\Notifications\CustomerAcceptsBid;
use App\Notifications\NewOrder;
use App\Notifications\CustomerPaysOrder;
use App\Notifications\NewBidding;
use App\Notifications\CustomerDoesntAcceptOffer;
use Sample\PayPalClient;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;


class CustomerController extends Controller
{
    public function shop() //ipa una n para mo check una sa auth
    {
        if (Auth::check()) { //check if nay naka login nga user
            if(Auth()->user()->roles == "customer") {
                $page_title = "Shop";
                $userID = Auth()->user()->id;
                $products = Product::all();
                $productsCount = $products->count();
                $categories = Category::all();
                $boutiques = Boutique::all();
                $notAvailables = Product::where('productStatus', 'Not Available')->get();
                $cart = Cart::where('userID', $userID)->where('status', 'Active')->first();
                if($cart != null){
                    $cartCount = $cart->items->count();
                }else{
                    $cartCount = 0;
                }

                // dd($cart);
                // foreach($cart->items as $item)
                // {
                //     foreach($item->product->productFile as $file)
                //     {
                //         dd($file['productID']);
                //     }
                // }

                // $productsArray = $products->toArray();
                // array_multisort(array_column($products, "created_at"), SORT_DESC, $products);
                // dd($products);

                $sets = Set::all();

                $notifications;
                $notificationsCount;
                $this->getNotifications($notifications, $notificationsCount);

                return view('hinimo/shop', compact('products', 'categories', 'cart', 'cartCount', 'userID', 'productsCount', 'boutiques', 'notAvailables', 'page_title', 'notifications', 'notificationsCount', 'sets'));
                

            } else if(Auth()->user()->roles == "boutique") {
                return redirect('/dashboard');
            } else if(Auth()->user()->roles == "admin") {
                return redirect('/admin-dashboard');
            } else if(Auth()->user()->roles == "courier") {
                return redirect('/ionic-dashboard');
            }        
        }else {
            $page_title = "Shop";
            $userID = null;
            $products = Product::where('productStatus', 'Available')->get();
            $productsCount = $products->count();
            $categories = Category::all();
            // $cartCount = Cart::where('userID', "")->where('status', "Pending")->count();
            $cart = null;
            $cartCount = null;
            $boutiques = Boutique::all();
            $notAvailables = Product::where('productStatus', 'Not Available')->get();
            $notificationsCount = null;
            $sets = Set::all();

            return view('hinimo/shop', compact('products', 'categories', 'cart', 'cartCount', 'userID', 'productsCount', 'boutiques', 'notAvailables', 'page_title', 'notificationsCount', 'sets'));
        }
    }

    public function getNotifications(&$notifications, &$notificationsCount)
    {
        if (Auth::check()) {
            $userID = Auth()->user()->id;
            $user = User::find($userID);
            $notifications = $user->notifications;
            $notificationsCount = $user->unreadNotifications->count();
        }
    }

    public function welcome()
    {
    }

    public function getStarted()
    {
        $page_title = "profiling";
        return view('hinimo/getstarted', compact('page_title'));
    }

    public function profiling(Request $request)
    {
        $page_title = "profiling";
        $userID = Auth()->user()->id;

        $tops = $request->input('tops');
        $sweaters = $request->input('sweaters');
        $jackets = $request->input('jackets');
        $pants = $request->input('pants');
        $dresses = $request->input('dresses');

        $data = array();

        array_push($data, $tops);
        array_push($data, $sweaters);
        array_push($data, $jackets);
        array_push($data, $pants);
        array_push($data, $dresses);
        // dd($data);

        $data_encoded = json_encode($data);

        
        $profilings = Profiling::create([
            'userID' => $userID,
            'data' => $data_encoded
        ]);

        return redirect('/user-profiling/done');
    }

    public function profilingDone()
    {
        $boutiques = Boutique::all();

        return view('hinimo/profiling-done', compact('boutiques'));
    }

    public function viewBoutique($boutiqueID)
    {
        // if (Auth::check()) {
        // $userID = Auth()->user()->id;
        // }

        if(Auth()->user()->roles == "customer") {
        $userID = Auth()->user()->id;
    	$categories = Category::all();
    	$products = Product::where('boutiqueID', $boutiqueID)->where('productStatus', 'Available')->get();
        $productsCount = $products->count();
        $boutiques = Boutique::all();
        $boutique = Boutique::where('id', $boutiqueID)->first();
        $page_title = $boutique['boutiqueName'];
        $notAvailables = Product::where('boutiqueID', $boutiqueID)->where('productStatus', 'Not Available')->get();
        $cart = Cart::where('userID', $userID)->where('status', 'Active')->first();
        if($cart != null){
            $cartCount = $cart->items->count();
        }else{
            $cartCount = 0;
        }

        $notifications;
        $notificationsCount;
        $this->getNotifications($notifications, $notificationsCount);

    	return view('hinimo/boutiqueProfile', compact('categories', 'products', 'productsCount', 'cart', 'cartCount', 'userID', 'boutiques', 'boutique', 'notAvailables', 'page_title', 'notifications', 'notificationsCount'));

        }else if(Auth()->user()->roles == "boutique") {
            return redirect('/dashboard');
        } else if(Auth()->user()->roles == "admin") {
            return redirect('/admin-dashboard');
        } 
    }

    public function productDetails($productID)
    {
        $user = Auth()->user();
    	$product = Product::where('id', $productID)->first();
        $addresses = Address::where('userID', $user['id'])->get();
        $boutiques = Boutique::all();
        $page_title = "Shop";
        $notifications;
        $notificationsCount;
        $this->getNotifications($notifications, $notificationsCount);
        $cart = Cart::where('userID', $user['id'])->where('status', 'Active')->first();
        if($cart != null){
            $cartCount = $cart->items->count();
        }else{
            $cartCount = 0;
        }

        $cities = City::all();
        $sp = Sharepercentage::where('id', '1')->first();
        $percentage = $sp['sharePercentage'] / 100;
        // dd($product->rentDetails['locationsAvailable']);
        
        // $totalPrice = $product['rentPrice'] + $product['deliveryFee'];

    	return view('hinimo/single-product-details', compact('product', 'cart', 'cartCount', 'user', 'addresses', 'boutiques', 'page_title', 'notifications', 'notificationsCount', 'cities', 'percentage'));
    }

    public function addtoCart($productID)
    {
   		$userID = Auth()->user()->id;
        $cart = Cart::where('userID', $userID)->orderBy('created_at', 'DESC')->first();

        if($cart == null){
            $cart = Cart::create([
                'userID' => $userID,
                'status' => "Active"
            ]);

        }else{
            if($cart['status'] == "Inactive"){
                $cart = Cart::create([
                    'userID' => $userID,
                    'status' => "Active"
                ]);
            }
        }

        Cartitem::create([
            'cartID' => $cart['id'],
            'productID' => $productID
        ]);
    	

    	return redirect('/shop');
    }

    public function addSettoCart($productID)
    {
        $userID = Auth()->user()->id;
        $cart = Cart::where('userID', $userID)->orderBy('created_at', 'DESC')->first();

        if($cart == null){
            $cart = Cart::create([
                'userID' => $userID,
                'status' => "Active"
            ]);

        }else{
            if($cart['status'] == "Inactive"){
                $cart = Cart::create([
                    'userID' => $userID,
                    'status' => "Active"
                ]);
            }
        }

        Cartitem::create([
            'cartID' => $cart['id'],
            'setID' => $productID
        ]);
        

        return redirect('/shop');
    }

    public function removeItem($cartID)
    {
        $item = Cartitem::where('id', $cartID)->delete();

        return response()->json(['item' => $item]);
    }

    public function getCart($productID)
    {
    	$product = Product::find($productID);

    	return response()->json(['product' => $product, 
    		'owner' => $product->owner,
    		'category' => $product->getCategory
    		]);
    }

    public function placeOrder(Request $request)
    {
        $userID = Auth()->user()->id;
        $deliveryAddress = $request->input('deliveryAddress');
        $addressID = $request->input('selectAddress');
            // dd($addressID);

        if($deliveryAddress != null && $addressID == "addAddress"){
            $address = Address::create([
                'userID' => $userID, 
                'contactName' => $request->input('fullname'), 
                'phoneNumber' => $request->input('phoneNumber'),
                'completeAddress' => $request->input('deliveryAddress'),
                'lat' => $request->input('lat'), 
                'lng' => $request->input('lng'), 
                'status' => "Not Default"
            ]);
            $addressID = $address['id'];
        }elseif($deliveryAddress != null && $addressID != "addAddress"){

        }

        $billingName = ucwords($request->input('fullname')); //remove ni
        $boutiqueCount = $request->input('boutiqueCount');
        for ($i=1; $i <= $boutiqueCount; $i++) { 

            $orders = $request->input("order$i");
            $order = Order::create([
                'userID' => $request->input('userID'),
                'cartID' => $request->input('cartID'),
                'subtotal' => $orders['subtotal'],
                'deliveryfee' => $orders['deliveryfee'],
                'total' => $orders['total'],
                'boutiqueID' => $orders['boutiqueID'],
                'deliveryAddress' => $addressID, //remove ni
                'billingName' => $billingName, //remove ni
                'phoneNumber' => $addressID, //remove ni
                'boutiqueShare' => $orders['boutiqueShare'],
                'adminShare' => $orders['adminShare'],
                'status' => 'Pending',
                'paymentStatus' => 'Not Yet Paid',
                'addressID' => $addressID
            ]);

            $cart = Cart::where('id', $request->input('cartID'))->first();
            $cart = Cart::where('id', $order['cartID'])->first();
            $cart->update([
                'status' => 'Inactive'
            ]);

            foreach($cart->items as $item){
                if($item->product != null){
                    $product = Product::where('id', $item->product['id'])->first();
                    $productQuantity = $product['quantity'] - 1;
                    $product->update([
                        'quantity' => $productQuantity
                    ]);

                    if($product['quantity'] == 0){
                        $product->update([
                            'productStatus' => "Not Available"
                        ]);
                    }   
                }else{
                    $set = Set::where('id', $item->set['id'])->first();
                    $setQuantity = $set['quantity'] - 1;
                    $set->update([
                        'quantity' => $setQuantity
                    ]);

                    if($set['quantity'] == 0){
                        $set->update([
                            'setStatus' => "Not Available"
                        ]);
                    }
                }
            }
            

            $boutique = Boutique::where('id', $orders['boutiqueID'])->first();
            $boutiqueseller = User::find($boutique['userID']);
            $boutiqueseller->notify(new NewOrder($order));
        }

        return redirect('/view-order/'.$order['id']);
    }

    public function checkout()
    {
        $page_title = "Checkout";
        $userID = Auth()->user()->id;
        $user = User::find($userID);
        $boutiques = Boutique::all();
        // $notAvailables = Product::where('productStatus', 'Not Available')->get();
        $addresses = Address::where('userID', $userID)->get();
        $cart = Cart::where('userID', $userID)->where('status', 'Active')->first();
        if($cart != null){
            $cartCount = $cart->items->count();
        }else{
            $cartCount = 0;
        }

        $sp = Sharepercentage::where('id', '1')->first();
        $percentage = $sp['sharePercentage'] / 100;
        $notifications;
        $notificationsCount;
        $this->getNotifications($notifications, $notificationsCount);

        // $orders = array();
        // foreach($cart->items as $item){
        //     if(!in_array($item->product->owner, $orders)){
        //         // dd("naa");
        //         array_push($orders, $item->product->owner);
        //     }else{
        //     }
        // }
        // foreach($orders as $order){
                // dd($order->product->owner);
            // if(in_array($order->product->owner, $orders)){
                // dd($order->product->owner['boutiqueName']);
            // }else{
                // dd($order->product->owner);
            // }
        // }   
            // dd($orders);

    	return view('hinimo/checkout', compact('page_title', 'cart', 'cartCount', 'user', 'boutiques', 'notifications', 'notificationsCount', 'percentage', 'addresses'));
    }

    public function useraccount()
    {
        $page_title = "My Account";
        $id = Auth()->user()->id;
        $user = User::find($id);
        $categories = Category::all();
        $products = Product::all();
        $addresses = Address::where('userID', $id)->get();
        $boutiques = Boutique::all();
        // $cities = City::where('provCode', '0722')->orderBy('citymunDesc', 'ASC')->get();
        // $barangays = Barangay::all();
        $notifications;
        $notificationsCount;
        $this->getNotifications($notifications, $notificationsCount);
        $cart = Cart::where('userID', $id)->where('status', 'Active')->first();
        if($cart != null){
            $cartCount = $cart->items->count();
        }else{
            $cartCount = 0;
        }


        return view('hinimo/useraccount', compact('categories', 'products', 'cart', 'cartCount', 'user', 'addresses', 'boutiques', 'page_title', 'notifications', 'notificationsCount'));
    }

    public function getBrgy($citymunCode)
    {
        $brgys = Barangay::where('citymunCode', $citymunCode)->orderBy('brgyDesc', 'ASC')->get();

        return response()->json(['brgys' => $brgys]);
    }

    public function addAddress(Request $request)
    {
        $id = Auth()->user()->id;

        $addresses = Address::where('userID', $id)->get();

        if(count($addresses) > 0) {
            $address = Address::create([
                'userID' => $id, 
                'contactName' => $request->input('contactName'), 
                'phoneNumber' => $request->input('phoneNumber'),
                'completeAddress' => $request->input('completeAddress'),
                'lat' => $request->input('lat'), 
                'lng' => $request->input('lng'), 
                'status' => "Not Default"
            ]);

        }else{
            $address = Address::create([
                'userID' => $id, 
                'contactName' => $request->input('contactName'), 
                'phoneNumber' => $request->input('phoneNumber'),
                'completeAddress' => $request->input('completeAddress'),
                'lat' => $request->input('lat'), 
                'lng' => $request->input('lng'), 
                'status' => "Default"
            ]);
        }

        

        return redirect('/user-account');
    }

    public function setAsDefault($addressID)
    {
        $userID = Auth()->user()->id;

        $addresses = Address::where('userID', $userID)->where('status', "Default")->update([
            'status' => "Not Default"
        ]);

        $address = Address::where('id', $addressID)->update([
            'status' => "Default"
        ]);

        return redirect('/user-account#addresses');
    }

    public function sortBy($condition)
    {
        // $userID = Auth()->user()->id;
        // $categories = Category::all();
        // $cartCount = Cart::where('userID', $userID)->where('status', "Pending")->count();
        // $carts = Cart::where('userID', $userID)->where('status', "Pending")->get();
        // dd($carts);

        if ($condition == "newest") {

            $products = Product::all();
            $sorted = sort($products['created_at']);
        }

        return redirect('/shop');
    }

    public function getProducts($condition)
    {
        if ($condition == "newest") {

            $products = Product::all();

            foreach ($products as $product) {
                $product->owner;
                $product->getCategory;
                $product->productFile;
            }

            $productsArray = $products->toArray();

            array_multisort(array_column($productsArray, "created_at"), SORT_DESC, $productsArray);

        }else if($condition == "newest"){

        }

        return response()->json([
            'products' => $productsArray
        ]);
    }

    public function submitRequestToRent($productID)
    {
        $user = Auth()->user();
        $userID = Auth()->user()->id;
        $product = Product::where('id', $productID)->first();
        $addresses = Address::where('userID', $user['id'])->get();
        $boutiques = Boutique::all();
        $page_title = "Request to Rent";
        $addresses = Address::where('userID', $userID)->get();
        $notifications;
        $notificationsCount;
        $this->getNotifications($notifications, $notificationsCount);
        $cart = Cart::where('userID', $user['id'])->where('status', 'Active')->first();
        if($cart != null){
            $cartCount = $cart->items->count();
        }else{
            $cartCount = 0;
        }

        $cities = City::all();
        $sp = Sharepercentage::where('id', '1')->first();
        $percentage = $sp['sharePercentage'] / 100;
        // dd($product->rentDetails['locationsAvailable']);
        
        // $totalPrice = $product['rentPrice'] + $product['deliveryFee'];

        return view('hinimo/requestToRent', compact('product', 'cart', 'cartCount', 'user', 'addresses', 'boutiques', 'page_title', 'notifications', 'notificationsCount', 'cities', 'percentage', 'addresses'));
    }

    public function requestToRent(Request $request)
    {
        $id = Auth()->user()->id;
        $user = User::find($id);

        $measurement = $request->input('measurement');
        $mName = json_encode($measurement);

        $dateuse = date('Y-m-d',strtotime($request->input('dateToUse')));
        $toadd = $request->input('limitOfDays');
        $dateToBeReturned = date('Y-m-d', strtotime($dateuse.'+'.$toadd.' days'));

        $deliveryAddress = $request->input('deliveryAddress');
        $addressID = $request->input('selectAddress');

        if($deliveryAddress != null && $addressID == "addAddress"){
            $address = Address::create([
                'userID' => $id, 
                'contactName' => $request->input('billingName'), 
                'phoneNumber' => $request->input('phoneNumber'),
                'completeAddress' => $request->input('deliveryAddress'),
                'lat' => $request->input('lat'), 
                'lng' => $request->input('lng'), 
                'status' => "Not Default"
            ]);
            $addressID = $address['id'];
        }elseif($deliveryAddress != null && $addressID != "addAddress"){
            //leave empty lang para mo exit na sa condition
        }

        $rent = Rent::create([
            'boutiqueID' => $request->input('boutiqueID'),
            'customerID' => $id, 
            'status' => "Pending", 
            'productID' => $request->input('productID'), 
            'dateToUse' => $dateuse, 
            'dateToBeReturned' => $dateToBeReturned, 
            'additionalNotes' => $request->input('additionalNotes')
        ]);

        $measurement = Measurement::create([
            'userID' => $id,
            'type' => 'rent',
            'typeID' => $rent['rentID'],
            'data' => $mName
        ]);

        // $order = Order::create([
        //     'userID' => $id,
        //     'rentID' => $rent['rentID'],
        //     'boutiqueID' => $request->input('boutiqueID'),
        //     'subtotal' => $request->input('subtotal'),
        //     'deliveryfee' => $request->input('deliveryfee'),
        //     'total' => $request->input('total'),
        //     'deliveryAddress' => $addressID,
        //     'status' => "Pending",
        //     'paymentStatus' => "Not Yet Paid",
        //     'billingName' => $request->input('billingName'), 
        //     'phoneNumber' => $request->input('phoneNumber'),
        //     'boutiqueShare' => $request->input('boutiqueShare'),
        //     'adminShare' => $request->input('adminShare'),
        //     'addressID' => $addressID
        // ]);

        $rent->update([
            // 'orderID' => $order['id'],
            'measurementID' => $measurement['id']
        ]);

        // Product::where('id', $rent['productID'])->update([
        //     'productStatus' => "Not Available"
        // ]);

        $boutique = Boutique::where('id', $rent['boutiqueID'])->first();
        $boutiqueseller = User::find($boutique['userID']);
        
        $boutiqueseller->notify(new RentRequest($rent));

        return redirect('/view-rent/'.$rent['rentID']);
    }

    public function receiveRent($rentID)
    {
        $rent = Rent::where('rentID', $rentID)->first();
        $rent->update([
            'status' => "On Rent"
        ]);
        $order = Order::where('rentID', $rentID)->update([
            'status' => "On Rent"
        ]);

        return redirect('/view-rent/'.$rent['rentID']);
    }

    public function showBiddings()
    {
        if(Auth()->user()->roles == "customer") {
        if (Auth::check()) {
        $userID = Auth()->user()->id;
        }

        $page_title = 'Biddings';
        $categories = Category::all();
        $boutiques = Boutique::all();
        $biddings = Bidding::where('status', 'Open')->get();
        $biddingsCount = $biddings->count();
        $notifications;
        $notificationsCount;
        $this->getNotifications($notifications, $notificationsCount);
        $cart = Cart::where('userID', $userID)->where('status', 'Active')->first();
        if($cart != null){
            $cartCount = $cart->items->count();
        }else{
            $cartCount = 0;
        }

        // $time = date();
        // dd(date("Y-m-d"));

        // foreach($biddings as $bidding){
        // $bids = array();
        // foreach($bidding->bids as $bid){
        //     array_push($bids, $bid['bidAmount']);
        // }
        // dd(min($bids));
            // dd($bidding->bids);
        // }


        return view('hinimo/biddings', compact('page_title', 'products', 'categories', 'cart', 'cartCount', 'userID', 'boutiques', 'biddings', 'biddingsCount', 'notificationsCount', 'notifications'));
        
        }else if(Auth()->user()->roles == "boutique") {
            return redirect('/dashboard');
        } else if(Auth()->user()->roles == "admin") {
            return redirect('/admin-dashboard');
        }
    }

    public function showStartNewBidding()
    {
        $page_title = "Start a New Bidding";
        $userID = Auth()->user()->id;
        $categories = Category::all();
        $boutiques = Boutique::all();
        // $tags = Tag::all();
        $notifications;
        $notificationsCount;
        $this->getNotifications($notifications, $notificationsCount);
        $cart = Cart::where('userID', $userID)->where('status', 'Active')->first();
        if($cart != null){
            $cartCount = $cart->items->count();
        }else{
            $cartCount = 0;
        }

        return view('hinimo/bidding-newBidding', compact('page_title', 'categories', 'cart', 'cartCount', 'userID', 'boutiques' , 'notifications', 'notificationsCount'));
    }

    public function savebidding(Request $request)
    {
        $userID = Auth()->user()->id;

        // $measurement = $request->input('measurement');
        // $mName = json_encode($measurement);
        $deadlineOfProduct = date('Y-m-d',strtotime($request->input('deadlineOfProduct')));
        $time = time();
        // dd(date("Y-m-d",$time));
        // dd($time);

        $bidding = Bidding::create([
            'userID' => $userID,
            'quotationPrice' => $request->input('quotationPrice'), 
            'endDate' => $request->input('endDate'), 
            'deadlineOfProduct' => $deadlineOfProduct,
            'quantity' => $request->input('quantity'), 
            'fabChoice' => $request->input('fabChoice'), 
            'notes' => $request->input('notes'), 
            'status' => "Open"
        ]);

        // $measurement = Measurement::create([
        //     'userID' => $userID,
        //     'type' => 'bidding',
        //     'typeID' => $bidding['id'],
        //     'data' => $mName
        // ]);

        // $bidding->update([
        //     'measurementID' => $measurement['id']
        // ]);

        $gallery = Gallery::create([
            'userID' => $userID
        ]);


        $upload = $request->file('file');
        if($request->hasFile('file')) {
        // foreach($uploads as $upload){
            $files = new File();
            $destinationPath = public_path('uploads');
            $name = substr(sha1(mt_rand().microtime()), mt_rand(0,35),7).$upload->getClientOriginalName();
            $filename = $destinationPath.'\\'. $name;
            $upload->move($destinationPath, $filename);

            $files->userID = $userID;
            $files->biddingID = $bidding['id'];
            $files->galleryID = $gallery['id'];
            $files->filename = "/".$name;
            $files->save();
            $filename = "/".$name;
        // }
      }

        $boutiques = Boutique::all();
        foreach($boutiques as $boutique)
        {
            $boutiqueseller = User::where('id', $boutique['userID'])->first();
            $boutiqueseller->notify(new NewBidding($bidding));
        }

        return redirect('/biddings');
    }

    public function viewBidding($biddingID)
    {
        if (Auth::check()) {
        $userID = Auth()->user()->id;
        $user = User::find($userID);
        $page_title = 'Biddings';
        $boutiques = Boutique::all();
        $notifications;
        $notificationsCount;
        $this->getNotifications($notifications, $notificationsCount);
        $cart = Cart::where('userID', $userID)->where('status', 'Active')->first();
        if($cart != null){
            $cartCount = $cart->items->count();
        }else{
            $cartCount = 0;
        }

        $bidding = Bidding::where('id', $biddingID)->first();
        $bids = Bid::where('biddingID', $bidding['id'])->get();
        $bidsCount = $bids->count();

        return view('hinimo/bidding-details', compact('user', 'userID', 'page_title', 'cart', 'cartCount', 'boutiques', 'bidding', 'bids', 'notificationsCount', 'notifications', 'bidsCount'));
        }
    }

    // public function viewBidder($biddingID)
    // {
    //     $page_title = "Notifications";
    //     $userID = Auth()->user()->id;
    //     $boutiques = Boutique::all();
    //     $user = User::find($userID);
    //     $notifications = $user->notifications;
    //     $notificationsCount = $user->unreadNotifications->count();
    //     $cart = Cart::where('userID', $userID)->where('status', 'Active')->first();
    //     if($cart != null){
    //         $cartCount = $cart->items->count();
    //     }else{
    //         $cartCount = 0;
    //     }
    //     $bidding = Bidding::where('id', $biddingID)->first();
    //     $bids = Bid::where('biddingID', $bidding['id'])->get();

    //     return view('hinimo/viewBidder', compact('page_title', 'userID', 'boutiques', 'notifications', 'notificationsCount', 'cart', 'cartCount', 'bidding', 'bids'));
    // }

    public function myBiddings()
    {
        $page_title = "My Biddings";
        $userID = Auth()->user()->id;
        $user = User::find($userID);
        $boutiques = Boutique::all();
        $notifications = $user->notifications;
        $notificationsCount = $user->unreadNotifications->count();
        $cart = Cart::where('userID', $userID)->where('status', 'Active')->first();
        if($cart != null){
            $cartCount = $cart->items->count();
        }else{
            $cartCount = 0;
        }

        $biddings = Bidding::where('userID', $userID)->get();
        $biddingsCount = $biddings->count();

        return view('hinimo/myBiddings', compact('page_title', 'userID', 'boutiques', 'notifications', 'notificationsCount', 'cart', 'cartCount', 'biddings', 'biddingsCount'));
    }

    public function reviewBidding($bidID)
    {
        $page_title = "Submit Address";
        $userID = Auth()->user()->id;
        $user = User::find($userID);
        $boutiques = Boutique::all();
        $notifications = $user->notifications;
        $notificationsCount = $user->unreadNotifications->count();
        $cart = Cart::where('userID', $userID)->where('status', 'Active')->first();
        if($cart != null){
            $cartCount = $cart->items->count();
        }else{
            $cartCount = 0;
        }

        $bid = Bid::where('id', $bidID)->first();

        return view('hinimo/reviewBidding', compact('page_title', 'userID', 'user', 'boutiques', 'notifications', 'notificationsCount', 'cart', 'cartCount', 'bid'));
    }

    // public function acceptBid($bidID)
    // {
    //     $bid = Bid::where('id', $bidID)->first();
    //     $bidding= Bidding::where('id', $bid['biddingID'])->first();
        
    //     $bidding->update([
    //         'bidID' => $bid['id'],
    //         'status' => "Closed"
    //     ]);

    //     return redirect('view-bidding/'.$bidding['id']);
    // }

    public function inputAddressforBiding($bidID)
    {
        $page_title = "Submit Address";
        $userID = Auth()->user()->id;
        $user = User::find($userID);
        $boutiques = Boutique::all();
        $addresses = Address::where('userID', $userID)->get();
        $notifications = $user->notifications;
        $notificationsCount = $user->unreadNotifications->count();
        $cart = Cart::where('userID', $userID)->where('status', 'Active')->first();
        if($cart != null){
            $cartCount = $cart->items->count();
        }else{
            $cartCount = 0;
        }

        $bid = Bid::where('id', $bidID)->first();
        $mto = null;
        $sp = Sharepercentage::where('id', '1')->first();
        $percentage = $sp['sharePercentage'] / 100;

        return view('hinimo/inputAddress', compact('page_title', 'userID', 'user', 'boutiques', 'notifications', 'notificationsCount', 'cart', 'cartCount', 'bid', 'mto', 'percentage', 'addresses'));
    }

    public function makeOrderforBidding(Request $request)
    {
        $userID = Auth()->user()->id;
        $bid = Bid::where('id', $request->input('bidID'))->first();
        $bidding = Bidding::where('id', $bid->bidding['id'])->first();
        $deliveryAddress = $request->input('deliveryAddress');
        $addressID = $request->input('selectAddress');
            // dd($addressID);

        if($deliveryAddress != null && $addressID == "addAddress"){
            $address = Address::create([
                'userID' => $userID, 
                'contactName' => $request->input('billingName'), 
                'phoneNumber' => $request->input('phoneNumber'),
                'completeAddress' => $request->input('deliveryAddress'),
                'lat' => $request->input('lat'), 
                'lng' => $request->input('lng'), 
                'status' => "Not Default"
            ]);
            $addressID = $address['id'];
            // dd($deliveryAddress);
        }elseif($deliveryAddress != null && $addressID != "addAddress"){
            //leave empty lang para mo exit na sa condition
        }

        // dd($bidding);
        $bidding->update([
            'bidID' => $request->input('bidID'),
            'status' => "Closed"
        ]);

        $order = Order::create([
            'userID' => $userID,
            'biddingID' => $bidding['id'],
            'boutiqueID' => $bid->owner['id'],
            'subtotal' => $request->input('subtotal'),
            'deliveryfee' => $request->input('deliveryfee'),
            'total' => $request->input('total'),
            'deliveryAddress' => $addressID,
            'status' => "Pending",
            'paymentStatus' => "Not Yet Paid",
            'billingName' => $request->input('billingName'),
            'phoneNumber' => $addressID,
            'boutiqueShare' => $request->input('boutiqueShare'),
            'adminShare' => $request->input('adminShare'),
            'addressID' => $addressID
        ]);

        $bidding->update([
            'orderID' => $order['id']
        ]);
        // dd($bidding->bid['id']);

        $boutique = Boutique::where('id', $bid->owner['id'])->first();
        $boutiqueseller = User::find($boutique['userID']);
        $boutiqueseller->notify(new CustomerAcceptsBid($bidding));

        $bids = Bid::where('biddingID', $bidding['id'])->get();
        foreach($bids as $deniedBid){
            if($deniedBid['id'] != $bid['id']){
                $deniedBoutique = Boutique::where('id', $deniedBid['boutiqueID'])->first();
                $deniedBoutiqueSeller = User::where('id', $deniedBoutique['userID'])->first();
                $deniedBoutiqueSeller->notify(new CustomerDoesntAcceptOffer($deniedBid));
            }
        }

        return redirect('/view-bidding-order/'.$bidding['id']);
    }

    public function submitMeasurementforBidding(Request $request)
    {
        $userID = Auth()->user()->id;
        $biddingID = $request->input('biddingID');
        $persons = $request->input('person');
        $mrequests = Measurementrequest::where('type', 'bidding')->where('typeID', $biddingID)->get();
        $data = array();
        $counter = 1;

        foreach($persons as $person){
            $measurementArray = array();
            array_push($measurementArray, $person);

            foreach($mrequests as $mrequest){
                $cmArray = array();
                $categoryName = $mrequest->category['categoryName'];
                $measurements = $request->input("$counter");

                // array_push($cmArray, $categoryName);
                array_push($cmArray, $measurements);
                // DD($measurements);
            }

            // $personJson = json_encode($measurementArray); wa ni gamit hahah
            array_push($measurementArray, $cmArray);
            array_push($data, $measurementArray);
            $counter++;
        }
            // dd($data);

        $dataJson = json_encode($data);

        $measurement = Measurement::create([
            'userID' => $userID,
            'type' => 'bidding',
            'typeID' => $biddingID,
            'data' => $dataJson
        ]);

        Bidding::where('id', $biddingID)->update([
            'measurementID' => $measurement['id']
        ]);
        
        return redirect('view-bidding-order/'.$biddingID);
    }

    public function notifications()
    {
        $page_title = "Notifications";
        $userID = Auth()->user()->id;
        $categories = Category::all();
        $products = Product::all();
        $boutiques = Boutique::all();
        $user = User::find($userID);
        $notifications = $user->notifications;
        $notificationsCount = $user->unreadNotifications->count();
        $cart = Cart::where('userID', $userID)->where('status', 'Active')->first();
        if($cart != null){
            $cartCount = $cart->items->count();
        }else{
            $cartCount = 0;
        }

        return view('hinimo/notifications', compact('categories', 'products', 'cart', 'cartCount', 'userID', 'boutiques', 'page_title', 'notifications', 'notificationsCount'));
    }

    public function viewNotification($notificationID)
    {
        $page_title = "View Notification";
        $userID = Auth()->user()->id;
        $boutiques = Boutique::all();
        $notifications;
        $notificationsCount;
        $this->getNotifications($notifications, $notificationsCount);

        foreach($notifications as $notification) {
            if($notification->id == $notificationID) {

                if($notification->type == 'App\Notifications\RentApproved'){
                    $notification->markAsRead();

                    return redirect('/view-rent/'.$notification->data['rentID']);
                    
                }elseif($notification->type == 'App\Notifications\RentUpdateForCustomer'){
                    $notification->markAsRead();

                    return redirect('/view-rent/'.$notification->data['rentID'].'#rent-details');

                }elseif($notification->type == 'App\Notifications\MtoUpdateForCustomer'){
                    $notification->markAsRead();

                    return redirect('/view-mto/'.$notification->data['mtoID'].'#mto-details');

                }elseif($notification->type == 'App\Notifications\ContactCustomer'){
                    // $notif = $notification;
                    $notification->markAsRead();

                    return redirect('/view-mto/'.$notification->data['mtoID'].'#mto-details');

                }elseif($notification->type == 'App\Notifications\BoutiqueDeclinesMto'){
                    $notification->markAsRead();

                    return redirect('/view-mto/'.$notification->data['mtoID'].'#mto-details');

                }elseif($notification->type == 'App\Notifications\NotifyForAlterations'){
                    $notification->markAsRead();

                    $order = Order::where('id', $notification->data['orderID'])->first();

                    if($order['mtoID'] != null){
                        return redirect('/view-mto/'.$order->mto['id']);

                    }elseif($order['cartID'] != null){
                        return redirect('/view-order/'.$order['id']);

                    }elseif($order['rentID'] != null){
                        return redirect('/view-rent/'.$order->rent['rentID']);

                    }elseif($order['biddingID'] != null){
                        return redirect('/view-bidding-order/'.$order->bidding['id']);
                    }

                }elseif($notification->type == 'App\Notifications\NewBid'){
                    $notification->markAsRead();

                    return redirect('/view-bidding/'.$notification->data['biddingID'].'#bidders');

                }elseif($notification->type == 'App\Notifications\NotifyForPickup'){
                    $notification->markAsRead();

                    $order = Order::where('id', $notification->data['orderID'])->first();

                    if($order['mtoID'] != null){
                        return redirect('/view-mto/'.$order->mto['id']);

                    }elseif($order['cartID'] != null){
                        return redirect('/view-order/'.$order['id']);

                    }elseif($order['rentID'] != null){
                        return redirect('/view-rent/'.$order->rent['rentID']);

                    }elseif($order['biddingID'] != null){
                        return redirect('/view-bidding-order/'.$order->bidding['id']);
                    }

                }elseif($notification->type == 'App\Notifications\MeasurementRequests'){
                    $notification->markAsRead();

                    if($notification->data['transactionType'] == "bidding"){
                        return redirect('/view-bidding-order/'.$notification->data['transactionID'].'#measurements');

                    }elseif($notification->data['transactionType'] == "mto"){
                        return redirect('/view-mto/'.$notification->data['transactionID'].'#measurements');

                    }
                }

            }
        }
    }

    public function getCategory($genderCategory)
    {
        $categories = Category::where('gender', $genderCategory)->get();

        return response()->json(['categories' => $categories]);
    }

    public function madeToOrder($boutiqueID)
    {
        $boutique = Boutique::where('id', $boutiqueID)->first();
        $page_title = "Made-to-order";
        $userID = Auth()->user()->id;
        $categories = Category::all();
        $boutiques = Boutique::all();
        $notAvailables = Product::where('boutiqueID', $boutique['id'])->where('productStatus', 'Not Available')->get();
        $measurements = Categorymeasurement::all();
        $cart = Cart::where('userID', $userID)->where('status', 'Active')->first();
        if($cart != null){
            $cartCount = $cart->items->count();
        }else{
            $cartCount = 0;
        }
        $notifications;
        $notificationsCount;
        $this->getNotifications($notifications, $notificationsCount);

        $fabrics = Fabric::where('boutiqueID', $boutiqueID)->get();
        $fabs = $fabrics->groupBy('name');
        // dd($fabs);
        
        return view('hinimo/madetoorder', compact('categories', 'cart', 'cartCount', 'userID', 'boutiques', 'boutique', 'notAvailables', 'page_title', 'notifications', 'notificationsCount', 'categoryArray', 'fabrics', 'fabs'));
    }

    // public function getFabricColor($boutiqueID, $type)
    // {
    //     $colors = Fabric::where('name', $type)->where('boutiqueID', $boutiqueID)->get();

    //     return response()->json(['colors' => $colors]);
    // }

    public function saveMadeToOrder(Request $request)
    {
        $deadlineOfProduct = date('Y-m-d',strtotime($request->input('deadlineOfProduct')));
        $userID = Auth()->user()->id;
        $boutiqueID = $request->input('boutiqueID');

        // dd($deadlineOfProduct);

        $mto = Mto::create([
            'userID' => $userID,
            'boutiqueID' => $boutiqueID,
            'deadlineOfProduct' => $deadlineOfProduct,
            'notes' => $request->input('notes'),
            'quantity' => $request->input('quantity'),
            'numOfPerson' => $request->input('numOfPerson'),
            'fabChoice' => $request->input('fabChoice'),
            'orderID' => $request->input('orderID'),
            'status' => "Active"
            ]);

        $gallery = Gallery::create([
            'userID' => $userID
        ]);

        $upload = $request->file('file');
        if($request->hasFile('file')) {
            $files = new File();
            // $name = $upload->getClientOriginalName();
            $destinationPath = public_path('uploads');
            $name = substr(sha1(mt_rand().microtime()), mt_rand(0,35),7).$upload->getClientOriginalName();
            $filename = $destinationPath.'\\'. $name;
            $upload->move($destinationPath, $filename);

            $files->userID = $userID;
            $files->mtoID = $mto['id'];
            $files->galleryID = $gallery['id'];
            $files->filename = "/".$name;
            $files->save();
            $filename = "/".$name;
        }

        $boutique = Boutique::where('id', $boutiqueID)->first();
        $boutiqueseller = User::find($boutique['userID']);
        $boutiqueseller->notify(new NewMTO($mto));

      return redirect('boutique/'.$boutiqueID);
    }

    public function submitMeasurementforMto(Request $request)
    {
        $userID = Auth()->user()->id;
        $mtoID = $request->input('mtoID');
        $persons = $request->input('person');
        $mrequests = Measurementrequest::where('type', 'mto')->where('typeID', $mtoID)->get();
        $data = array();
        $counter = 1;

        foreach($persons as $person){
            $measurementArray = array();
            array_push($measurementArray, $person);

            foreach($mrequests as $mrequest){
                $cmArray = array();
                $categoryName = $mrequest->category['categoryName'];
                $measurements = $request->input("$counter");

                // array_push($cmArray, $categoryName);
                array_push($cmArray, $measurements);
                // dd($cmArray);
            }

            // $personJson = json_encode($measurementArray); wa ni gamit hahah
            array_push($measurementArray, $cmArray);
            array_push($data, $measurementArray);
            $counter++;
        }
            // dd($data);

        $dataJson = json_encode($data);

        $measurement = Measurement::create([
            'userID' => $userID,
            'type' => 'mto',
            'typeID' => $mtoID,
            'data' => $dataJson
        ]);

        Mto::where('id', $mtoID)->update([
            'measurementID' => $measurement['id']
        ]);
        
        return redirect('view-mto/'.$mtoID);
    }

    public function getMeasurements($categoryID)
    {
        $measurements = Categorymeasurement::where('categoryID', $categoryID)->get();

        return response()->json(['measurements' => $measurements]);
    }

    public function usertransactions()
    {
        $page_title = "Transactions";
        $userID = Auth()->user()->id;
        $user = User::find($userID);
        $boutiques = Boutique::all();
        $notifications = $user->notifications;
        $notificationsCount = $user->unreadNotifications->count();
        $cart = Cart::where('userID', $userID)->where('status', 'Active')->first();
        if($cart != null){
            $cartCount = $cart->items->count();
        }else{
            $cartCount = 0;
        }

        $orders = Order::where('userID', $userID)->get();
        $rents = Rent::where('customerID', $userID)->get();
        $mtos = Mto::where('userID', $userID)->where('orderID', null)->where('status', 'Active')->get();
        $biddings = Bidding::where('userID', $userID)->where('orderID', '!=', null)->get();
        $declinedMtos = Mto::where('status', '!=', 'Cancelled')->get();
        // dd($orders);

        $transactions = array();
        array_push($transactions, $orders);
        array_push($transactions, $rents);
        array_push($transactions, $mtos);
        // dd($transactions);

        // $productsArray = $products->toArray();
        // array_multisort(array_column($transactions, "created_at"), SORT_DESC, $transactions);

        return view('hinimo/transactions', compact('cart', 'cartCount', 'boutiques', 'page_title', 'notifications', 'notificationsCount', 'mtos', 'orders', 'rents', 'biddings', 'declinedMtos'));
    }

    public function viewBiddingOrder($biddingID)
    {
        $page_title = "Order Details";
        $userID = Auth()->user()->id;
        $user = User::find($userID);
        $boutiques = Boutique::all();
        $notifications = $user->notifications;
        $notificationsCount = $user->unreadNotifications->count();
        $cart = Cart::where('userID', $userID)->where('status', 'Active')->first();
        if($cart != null){
            $cartCount = $cart->items->count();
        }else{
            $cartCount = 0;
        }

        $bidding = Bidding::find($biddingID);
        $mrequests = Measurementrequest::where('type', 'bidding')->where('typeID', $biddingID)->get();


        return view('hinimo/viewBidding', compact('page_title', 'userID', 'user', 'boutiques', 'notifications', 'notificationsCount', 'cart', 'cartCount', 'bidding', 'mrequests'));
    }

    public function viewOrder($orderID)
    {

        $page_title = "Order Details";
        $userID = Auth()->user()->id;
        $user = User::find($userID);
        $boutiques = Boutique::all();
        $notifications = $user->notifications;
        $notificationsCount = $user->unreadNotifications->count();
        $cart = Cart::where('userID', $userID)->where('status', 'Active')->first();
        if($cart != null){
            $cartCount = $cart->items->count();
        }else{
            $cartCount = 0;
        }

        $order = Order::find($orderID);
        // $boutiqueseller = User::where('id', $order->boutique->owner['id'])->first();
        // dd($boutiqueseller);

        return view('hinimo/viewOrder', compact('cart', 'cartCount', 'boutiques', 'page_title', 'mtos', 'orders', 'rents', 'notifications', 'notificationsCount', 'order'));
    }

    public function viewRent($rentID)
    {
        $page_title = "Rent Details";
        $userID = Auth()->user()->id;
        $user = User::find($userID);
        $boutiques = Boutique::all();
        $notifications = $user->notifications;
        $notificationsCount = $user->unreadNotifications->count();
        $cart = Cart::where('userID', $userID)->where('status', 'Active')->first();
        if($cart != null){
            $cartCount = $cart->items->count();
        }else{
            $cartCount = 0;
        }

        $rent = Rent::find($rentID);

        // $measurements = json_decode($rent->measurement->data);
        // dd($measurements);

        return view('hinimo/viewRent', compact('cart', 'cartCount', 'boutiques', 'page_title', 'mtos', 'orders', 'rents', 'notifications', 'notificationsCount', 'rent'));
    }

    public function viewMto($mtoID)
    {
        $page_title = "MTO Details";
        $userID = Auth()->user()->id;
        $user = User::find($userID);
        $boutiques = Boutique::all();
        $notifications = $user->notifications;
        $notificationsCount = $user->unreadNotifications->count();
        $cart = Cart::where('userID', $userID)->where('status', 'Active')->first();
        if($cart != null){
            $cartCount = $cart->items->count();
        }else{
            $cartCount = 0;
        }

        $mto = Mto::find($mtoID);
        // $measurement = json_decode($mto->measurement->data);
        $fabrics = Fabric::where('boutiqueID', $mto->boutique['id'])->get();
        $sp = Sharepercentage::where('id', '1')->first();
        $percentage = $sp['sharePercentage'] / 100;
        // dd($fabrics);
        $mrequests = Measurementrequest::where('type', 'mto')->where('typeID', $mtoID)->get();


        return view('hinimo/viewMto', compact('cart', 'cartCount', 'boutiques', 'page_title', 'mtos', 'orders', 'rents', 'notifications', 'notificationsCount', 'mto', 'fabrics', 'percentage', 'mrequests'));
    }

    public function inputAddress($mtoID, $type)
    {
        $page_title = "Submit Address";
        $userID = Auth()->user()->id;
        $user = User::find($userID);
        $boutiques = Boutique::all();
        $addresses = Address::where('userID', $userID)->get();
        $notifications = $user->notifications;
        $notificationsCount = $user->unreadNotifications->count();
        $cart = Cart::where('userID', $userID)->where('status', 'Active')->first();
        if($cart != null){
            $cartCount = $cart->items->count();
        }else{
            $cartCount = 0;
        }

        $mto = Mto::find($mtoID);  

        // if($type == "acceptFabricPrice"){
        //     $mtoPrice = $mto['price'];
        // }elseif($type == "acceptSuggestedFabricPrice"){ //suggestFabric
        //     $mtoPrice = $fabricSuggestion->price;
        // }
        $mtoPrice = $mto['price'];

        $sp = Sharepercentage::where('id', '1')->first();
        $percentage = $sp['sharePercentage'] / 100;
        
        return view('hinimo/inputAddress', compact('user', 'cart', 'cartCount', 'boutiques', 'page_title', 'mtos', 'orders', 'rents', 'notifications', 'notificationsCount', 'mto', 'mtoPrice', 'percentage', 'addresses'));

    }

    public function makeOrderforMTO(Request $request)
    {
        $userID = Auth()->user()->id;
        $mtoID = $request->input('mtoID');
        $mto = Mto::find($mtoID);
        $deliveryAddress = $request->input('deliveryAddress');
        $addressID = $request->input('selectAddress');

        if($deliveryAddress != null && $addressID == "addAddress"){
            $address = Address::create([
                'userID' => $userID, 
                'contactName' => $request->input('billingName'), 
                'phoneNumber' => $request->input('phoneNumber'),
                'completeAddress' => $request->input('deliveryAddress'),
                'lat' => $request->input('lat'), 
                'lng' => $request->input('lng'), 
                'status' => "Not Default"
            ]);
            $addressID = $address['id'];
        }elseif($deliveryAddress != null && $addressID != "addAddress"){
            //leave empty lang para mo exit na sa condition
        }

        $order = Order::create([
            'userID' => $userID,
            'mtoID' => $mtoID,
            'boutiqueID' => $mto->boutique['id'],
            'subtotal' => $request->input('subtotal'),
            'deliveryfee' => $request->input('deliveryfee'),
            'total' => $request->input('total'),
            'deliveryAddress' => $addressID, //remove ni
            'status' => "Pending",
            'paymentStatus' => "Not Yet Paid",
            'billingName' => $request->input('billingName'), //remove ni
            'phoneNumber' => $addressID, //remove ni
            'boutiqueShare' => $request->input('boutiqueShare'),
            'adminShare' => $request->input('adminShare'),
            'addressID' => $addressID
        ]);

        $mto->update([
            'orderID' => $order['id'],
            'price' => $order['subtotal']
        ]);

        $boutiqueseller = User::where('id', $mto->boutique->owner['id'])->first();
        $boutiqueseller->notify(new CustomerAcceptsOffer($mto));

        return redirect('/view-mto/'.$mtoID);
    }

    public function cancelMto($mtoID)
    {
        $mto = Mto::where('id', $mtoID)->first();
        Mto::where('id', $mtoID)->update([
            'status' => "Cancelled"
        ]);

        //send notif to boutiquedat u cencelledt
        $boutiqueseller = User::where('id', $mto->boutique->owner['id'])->first();
        $boutiqueseller->notify(new CustomerCancelMto($mto));

        return redirect('/user-transactions');
    }

    public function receiveOrder($orderID)
    {
        $order = Order::find($orderID);
        $order->update([
            'status' => 'Completed'
        ]);
        // dd($order);
        
        //add notification to boutique
        //informing that completed na nag transaction 
        //nya dapat diri sad nga part makuha na ni boutique iyang bayad

        if($order['cartID'] != null){
            return redirect('/view-order/'.$order['id']);
        }
        elseif($order['rentID'] != null){
            return redirect('/view-rent/'.$order->rent['rentID']);
        }
        elseif($order['mtoID'] != null){
            return redirect('/view-mto/'.$order->mto['id']);
        }
        elseif($order['biddingID'] != null){
            return redirect('/view-bidding-order/'.$order->bidding['id']);
        }
        
    }

    // public function submitAddress(Request $request)
    // {
    //     if($request->input('mtoID') != null){
    //         $mto = Mto::where('id', $request->input('mtoID'))->first();
    //         $mto->update([
    //             'subtotal' => $mto['finalPrice'],
    //             'deliveryFee' => 50, //dummy pa ni
    //             'total' => $mto['finalPrice'] + 50,
    //             'deliveryAddress' => $request->input('address')
    //         ]);

    //         return redirect('/view-mto/'.$mto['id']);
    //     }

    // }

    public function paypalTransactionComplete(Request $request)
    {
        // print_r($request->mtoOrderID);

        if($request->rentID != null){
            $rent = Rent::where('rentID', $request->rentID)->first();
            $order = Order::where('id', $request->rentOrderID)->first();
            $rent->update([
                'status' => 'In-Progress'
            ]);
            $order->update([
                'status' => 'In-Progress',
                'paymentStatus' => 'Paid',
                'paypalOrderID' => $request->paypalOrderID
            ]);

            $boutiqueseller = User::where('id', $rent->boutique->owner['id'])->first();
            $boutiqueseller->notify(new CustomerPaysOrder($order));

            return redirect('/view-rent/'.$rent['rentID']);


        }elseif($request->mtoOrderID != null){

            $mto = Mto::where('id', $request->mtoID)->first();
            $order = Order::where('id', $request->mtoOrderID)->first();
            $order->update([
                'status' => 'In-Progress',
                'paymentStatus' => 'Paid',
                'paypalOrderID' => $request->paypalOrderID
            ]);

            $boutiqueseller = User::where('id', $mto->boutique->owner['id'])->first();
            $boutiqueseller->notify(new CustomerPaysOrder($order));

            return redirect('/view-mto/'.$mto['id']);


        }elseif($request->orderTransactionID != null){

            // print_r($request->paypalOrderID);
            $order = Order::where('id', $request->orderTransactionID)->first();
            $order->update([
                'status' => 'In-Progress',
                'paymentStatus' => 'Paid',
                'paypalOrderID' => $request->paypalOrderID
            ]);

            $boutiqueseller = User::where('id', $order->boutique->owner['id'])->first();
            $boutiqueseller->notify(new CustomerPaysOrder($order));

            return redirect('view-order/'.$order['id']);
        }
        
    }

    public static function getPaypalOrder($orderId)
    {

        // 3. Call PayPal to get the transaction details
        $client = PayPalClient::client();
        $response = $client->execute(new OrdersGetRequest($orderId));
        /**
         *Enable the following line to print complete response as JSON.
         */
        print json_encode($response->result);
        print "Status Code: {$response->statusCode}\n";
        print "Status: {$response->result->status}\n";
        print "Order ID: {$response->result->id}\n";
        print "Intent: {$response->result->intent}\n";
        print "Links:\n";
        foreach($response->result->links as $link)
        {
          print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
        }
        // 4. Save the transaction in your database. Implement logic to save transaction to your database for future reference.
        print "Gross Amount: {$response->result->purchase_units[0]->amount->currency_code} {$response->result->purchase_units[0]->amount->value}\n";

        // To print the whole response body, uncomment the following line
        print_r(json_encode($response->result, JSON_PRETTY_PRINT));
    }

    public function mixnmatch($boutiqueID)
    {
        $userID = Auth()->user()->id;
        $categories = Category::all();
        $boutiques = Boutique::all();
        $boutique = Boutique::where('id', $boutiqueID)->first();
        $products = Product::where('boutiqueID', $boutique['id'])->get();
        $cart = Cart::where('userID', $userID)->where('status', 'Active')->first();
        if($cart != null){
            $cartCount = $cart->items->count();
        }else{
            $cartCount = 0;
        }
        $page_title = "Mix & Match by ".$boutique['boutiqueName'];

        $notifications;
        $notificationsCount;
        $this->getNotifications($notifications, $notificationsCount);
        // dd($products);
        return view('hinimo/mixnmatch', compact('page_title', 'userID', 'categories', 'products', 'cart', 'cartCount', 'boutiques', 'notifications', 'notificationsCount'));
    }

    public function getMProduct($productID)
    {
        $product = Product::where('id', $productID)->first();

        return response()->json(['product' => $product,
                                'files' => $product->productFile
                                    ]);
    }

    public function submitMixnmatch(Request $request)
    {
        $top = $request->input('top');
        $bottom = $request->input('bottom');

        
    }

    public function addmnmtoCart($top, $bottom)
    {
        $userID = Auth()->user()->id;
        $cart = Cart::where('userID', $userID)->orderBy('created_at', 'DESC')->first();
        $product = Product::where('id', $top)->first();
        $boutique = Boutique::where('id', $product['boutiqueID'])->first();

        if($cart == null){
            $cart = Cart::create([
                'userID' => $userID,
                'status' => "Active"
            ]);

        }else{
            if($cart['status'] == "Inactive"){
                $cart = Cart::create([
                    'userID' => $userID,
                    'status' => "Active"
                ]);
            }
        }

        Cartitem::create([
            'cartID' => $cart['id'],
            'productID' => $top
        ]);

        Cartitem::create([
            'cartID' => $cart['id'],
            'productID' => $bottom
        ]);
        

        return redirect('/'.$boutique['id'].'/mixnmatch');
    }

    public function editProfile(Request $request)
    {
        $userID = Auth()->user()->id;

        $user = User::where('id', $userID)->update([
            'fname' => ucwords($request->input('fname')),
            'lname' => ucwords($request->input('lname')),
            'username' => $request->input('username'),
            'email' => $request->input('email'),
        ]);

        return redirect('user-account');
    }

    public function gallery()
    {
        $page_title = "Gallery";
        $userID = Auth()->user()->id;
        $user = User::find($userID);
        $boutiques = Boutique::all();
        $notifications = $user->notifications;
        $notificationsCount = $user->unreadNotifications->count();
        $cart = Cart::where('userID', $userID)->where('status', 'Active')->first();
        if($cart != null){
            $cartCount = $cart->items->count();
        }else{
            $cartCount = 0;
        }

        $pictures = Gallery::where('userID', $userID)->get();

        return view('hinimo/gallery', compact('page_title', 'user', 'boutiques', 'notifications', 'notificationsCount', 'cart', 'cartCount', 'pictures'));

    }

    public function setDetails($setID)
    {
        $user = Auth()->user();
        $set = Set::where('id', $setID)->first();
        $addresses = Address::where('userID', $user['id'])->get();
        $boutiques = Boutique::all();
        $page_title = "Shop";
        $notifications;
        $notificationsCount;
        $this->getNotifications($notifications, $notificationsCount);
        $cart = Cart::where('userID', $user['id'])->where('status', 'Active')->first();
        if($cart != null){
            $cartCount = $cart->items->count();
        }else{
            $cartCount = 0;
        }

        $cities = City::all();
        $sp = Sharepercentage::where('id', '1')->first();
        $percentage = $sp['sharePercentage'] / 100;


        return view('hinimo/set-single-product-details', compact('set', 'cart', 'cartCount', 'user', 'addresses', 'boutiques', 'page_title', 'notifications', 'notificationsCount', 'cities', 'percentage'));

    }

    public function submitRequestToRentSet($setID)
    {
        $user = Auth()->user();
        $userID = Auth()->user()->id;
        $product = Set::where('id', $setID)->first();
        $addresses = Address::where('userID', $user['id'])->get();
        $boutiques = Boutique::all();
        $page_title = "Request to Rent";
        $addresses = Address::where('userID', $userID)->get();
        $notifications;
        $notificationsCount;
        $this->getNotifications($notifications, $notificationsCount);
        $cart = Cart::where('userID', $user['id'])->where('status', 'Active')->first();
        if($cart != null){
            $cartCount = $cart->items->count();
        }else{
            $cartCount = 0;
        }

        $cities = City::all();
        $sp = Sharepercentage::where('id', '1')->first();
        $percentage = $sp['sharePercentage'] / 100;
        // dd($product);
        
        // $totalPrice = $product['rentPrice'] + $product['deliveryFee'];

        return view('hinimo/requestToRentSet', compact('product', 'cart', 'cartCount', 'user', 'addresses', 'boutiques', 'page_title', 'notifications', 'notificationsCount', 'cities', 'percentage', 'addresses'));
    }

    public function requestToRentSet(Request $request)
    {
        $id = Auth()->user()->id;
        $user = User::find($id);

        // $measurement = $request->input('measurement');
        // $mName = json_encode($measurement);

        $dateuse = date('Y-m-d',strtotime($request->input('dateToUse')));
        $toadd = $request->input('limitOfDays');
        $dateToBeReturned = date('Y-m-d', strtotime($dateuse.'+'.$toadd.' days'));

        $deliveryAddress = $request->input('deliveryAddress');
        $addressID = $request->input('selectAddress');

        if($deliveryAddress != null && $addressID == "addAddress"){
            $address = Address::create([
                'userID' => $id, 
                'contactName' => $request->input('billingName'), 
                'phoneNumber' => $request->input('phoneNumber'),
                'completeAddress' => $request->input('deliveryAddress'),
                'lat' => $request->input('lat'), 
                'lng' => $request->input('lng'), 
                'status' => "Not Default"
            ]);
            $addressID = $address['id'];
        }elseif($deliveryAddress != null && $addressID != "addAddress"){
            //leave empty lang para mo exit na sa condition
        }

        $rent = Rent::create([
            'boutiqueID' => $request->input('boutiqueID'),
            'customerID' => $id, 
            'status' => "Pending", 
            'setID' => $request->input('setID'), 
            'dateToUse' => $dateuse, 
            'dateToBeReturned' => $dateToBeReturned, 
            'additionalNotes' => $request->input('additionalNotes')
        ]);

        $data = array();
        $cmArray = array();
        // $categoryName = $mrequest->category['categoryName'];
        $measurements = $request->input('measurement');

        // array_push($cmArray, $categoryName);
        array_push($cmArray, $measurements);

        array_push($data, $cmArray);

        $dataJson = json_encode($data);
        // DD($dataJson);

        // $measurement = Measurement::create([
        //     'userID' => $userID,
        //     'type' => 'bidding',
        //     'typeID' => $biddingID,
        //     'data' => $dataJson
        // ]);

        $measurement = Measurement::create([
            'userID' => $id,
            'type' => 'rent',
            'typeID' => $rent['rentID'],
            'data' => $dataJson
        ]);

        $order = Order::create([
            'userID' => $id,
            'rentID' => $rent['rentID'],
            'boutiqueID' => $request->input('boutiqueID'),
            'subtotal' => $request->input('subtotal'),
            'deliveryfee' => $request->input('deliveryfee'),
            'total' => $request->input('total'),
            'deliveryAddress' => $addressID,
            'status' => "Pending",
            'paymentStatus' => "Not Yet Paid",
            'billingName' => $request->input('billingName'), 
            'phoneNumber' => $addressID,
            'boutiqueShare' => $request->input('boutiqueShare'),
            'adminShare' => $request->input('adminShare'),
            'addressID' => $addressID
        ]);

        $rent->update([
            'orderID' => $order['id'],
            'measurementID' => $measurement['id']
        ]);

        // Product::where('id', $rent['productID'])->update([
        //     'productStatus' => "Not Available"
        // ]);

        $boutique = Boutique::where('id', $rent['boutiqueID'])->first();
        $boutiqueseller = User::find($boutique['userID']);
        
        $boutiqueseller->notify(new RentRequest($rent));

        return redirect('/view-rent/'.$rent['rentID']);
    }


}
