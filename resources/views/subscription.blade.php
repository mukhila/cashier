@extends('layouts.app')
    
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    You will be charged ${{ number_format($plan->price, 2) }} for {{ $plan->name }} Plan
                </div>
  
                <div class="card-body">
  
                    <form id="subscribe-form" action="{{ route('subscription.create') }}" method="POST">
                        @csrf
                        <input type="hidden" name="plan" id="plan" value="{{ $plan->id }}">
                        <input type="hidden" name="amount" id = "amount" value = "{{ $plan->price }}" />
                        <div class="row">
                            <div class="col-xl-4 col-lg-4">
                                <div class="form-group">
                                    <label for="card-holder-name">Name</label>
                                    <input type="text" name="card-holder-name" id="card-holder-name" class="form-control" value="" placeholder="Name on the card">
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="stripe_key" name = "stripe_key" value="{{ env('STRIPE_KEY') }}"/>
                        <br>
                        <div class="row">
                             <div class="col-xl-4 col-lg-4">
                                <div class="form-group">
                            <label for="card-element">Credit or debit card</label>
                            <div id="card-element" class="form-control">
                            </div>
                            <!-- Used to display form errors. -->
                            <div id="card-errors" role="alert"></div>
								</div>
								</div>
                        </div>
                      
						
						<div class="col-xl-12 col-lg-12">
                            <hr>
                                

                                <button  type = "button" id="card-button" name="card-button" data-secret="{{ $intent->client_secret }}" class="btn btn-lg btn-success btn-block">Purchase</button>
                         </div>
  
                    </form>
  
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://js.stripe.com/v3/"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>

<script  type="application/javascript">


$(document).ready(function(){

    //const stripe = Stripe('pk_test_51NFuj0SHUnJwHMxF4KSR6Mlv9cmqFG5zjWf2vDFtuzpTVkuCcG7MJzUTpucFjBpjJvvV0fE1lX0eJt7mjMhtHocr00hFXPtKK9')
  

    var stripe = Stripe('{{ env('STRIPE_KEY') }}');
    var elements = stripe.elements();
    var style = {
        base: {
            color: '#32325d',
            fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
            fontSmoothing: 'antialiased',
            fontSize: '16px',
            '::placeholder': {
                color: '#aab7c4'
            }
        },
        invalid: {
            color: '#fa755a',
            iconColor: '#fa755a'
        }
    };
    var card = elements.create('card', {hidePostalCode: true,
        style: style});
    card.mount('#card-element');
    card.addEventListener('change', function(event) {
        var displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });
    const cardHolderName = document.getElementById('card-holder-name').value;
    //alert("hai");
    const cardButton = document.getElementById('card-button');
    const amount = document.getElementById('amount').value;
    const clientSecret = cardButton.dataset.secret;
    cardButton.addEventListener('click', async (e) => {
        e.preventDefault();
        console.log("attempting");
		//alert("Working");
        const { setupIntent, error } = await stripe.confirmCardSetup(
            clientSecret, {
                payment_method: {
                    card: card,
                    billing_details: { name: cardHolderName.value }                    
                }
            }
            );
        if (error) {
            var errorElement = document.getElementById('card-errors');
            errorElement.textContent = error.message;
        } else {
            paymentMethodHandler(setupIntent.payment_method);
        }
    });
    function paymentMethodHandler(payment_method) {
        var form = document.getElementById('subscribe-form');
        var hiddenInput = document.createElement('input');
        hiddenInput.setAttribute('type', 'hidden');
        hiddenInput.setAttribute('name', 'payment_method');
        hiddenInput.setAttribute('value', payment_method);
        form.appendChild(hiddenInput);
		//alert("Form Submitting");
		//alert(payment_method);
        form.submit();
    }
});   
</script>
@endsection
