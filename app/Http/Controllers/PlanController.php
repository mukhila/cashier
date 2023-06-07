<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plan;
use Auth;
use Stripe\Charge;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Stripe\PaymentIntent;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::get();
  
        return view("plans", compact("plans"));
    }  
  
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function show(Request $request)
    {
      
        $planname = collect(request()->segments())->last();

        $plan = Plan::where('slug',$planname)->first();
        
        $intent = auth()->user()->createSetupIntent();       


        return view("subscription", compact("plan", "intent"));
    }

    
   
    public function subscription(Request $request)
    {       

        $plan = Plan::find($request->plan);
        $plan = json_decode($plan);      
        $amount = $request->amount;      
        $paymentMethod = $request->payment_method;
        $user = auth()->user();   

        $paiuser = $user->createOrGetStripeCustomer();       
                
        if ($paymentMethod != null) {
            $paymentMethod = $user->addPaymentMethod($paymentMethod);
        }
        $paymentId = $paymentMethod->id;
        $stripe = new \Stripe\StripeClient('sk_test_51NFuj0SHUnJwHMxFEUsvAR28qEzpJPeMepvnSFkUoMXexwa1hZHJRwqXVwCnW4L4qxcqdZ0L9HyCWKj4QVTYW0Av00xy4OFdR2');
    
               
            $subscription = $stripe->subscriptions->create([
                'customer' => $paymentMethod->customer,
                'items' => [[
                    'price' => $plan->stripe_plan,
                ]],
                'payment_behavior' => 'default_incomplete',
                'payment_settings' => ['save_default_payment_method' => 'on_subscription'],
                'expand' => ['latest_invoice.payment_intent'],
            ]);
      
                       
                $subscriptionId = $subscription->id;
                $clientSecret = $subscription->latest_invoice->payment_intent->client_secret;
            
           $request->session()->flash('alert-success', 'You are subscribed to this plan');
        return view("subscription_success");
    }
}
