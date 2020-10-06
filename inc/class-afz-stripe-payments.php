<?php

if(!class_exists('AFZ_Stripe_Payments')){
    
    class AFZ_Stripe_Payments{

        // Save Stripe keys into array
        public $stripe_keys = array(
            'secret_key' => STRIPE_SECRET,       // Put your own secret key here
            'publishable_key' => STRIPE_PUBLIC,  // Put your own public key here
            'webhook_secret' => STRIPE_WEBHOOK   // Put your own webhook key here
        );

        public $stripe_client;

        // Fire up the plugin
        public function __construct(){
            $this->includes();
            // Start Stripe
            \Stripe\Stripe::setApiKey($this->stripe_keys['secret_key']);
            $this->stripe_client = new \Stripe\StripeClient($this->stripe_keys['secret_key']);
        }

        // Include the required files
        public function includes(){
            
            // Include Stripe
            require_once plugin_dir_path( __FILE__ ) . 'stripe-php-master/init.php';

            if(is_admin()){
                //require_once WPFEP_PATH.'/admin/class-wpfep-admin-installer.php';
            }else{
                //require_once WPFEP_PATH.'/inc/class-wpfep-registration.php';
            }
        }


        // Display payment form
        public function display_payment_form(){

            // Get the payment amount and metadata coming from POST
            $payment_amount = intval($_POST['amount']) * 100;
            $payment_description = $_POST['description'];

            // Metadata
            $payment_metadata = array();
            $payment_metadata_display_info = array();

            // Loop through alphabet looking for metadata
            foreach(range('A', 'Z') as $char){

                if(isset($_POST['metadata_'.$char.'_name']) and isset($_POST['metadata_'.$char.'_value'])){
                    // Add to $payment_metadata array if it exists
                    $payment_metadata[$_POST['metadata_'.$char.'_name']] = $_POST['metadata_'.$char.'_value'];

                    // Add to $payment_metadata_display_info array if it exists
                    if(isset($_POST['metadata_'.$char.'_display_info'])){
                        $payment_metadata_display_info[] = $_POST['metadata_'.$char.'_display_info'];
                    
                    // Or display the plain metadata value with a customized nice name
                    }elseif(isset($_POST['metadata_'.$char.'_display_name'])){
                        if(!empty($_POST['metadata_'.$char.'_value'])){
                            $payment_metadata_display_info[] = $_POST['metadata_'.$char.'_display_name'].' '.$_POST['metadata_'.$char.'_value'];
                        }
                    }

                }else{
                break;
                }
            }
            
            // Start a paymentIntent
            $intent = \Stripe\PaymentIntent::create([
                'amount' => $payment_amount,
                'currency' => 'eur',
                'metadata' => $payment_metadata,
            ]);

            // Display payment form inside #doing_payment (we will hide it after payment)
            ?>

            <div class="container-fluid" style="padding:2rem;">
                
                <div id="doing_payment" class="row">
                    <div class="col-md-6 col-lg-7" style="padding:2rem;">
                        <form id="payment-form">
                        <input type="text" id="buyer_name" placeholder="Name">

                        <?php
                        if(is_user_logged_in()){
                            $current_user = wp_get_current_user();
                            echo '<input type="hidden" id="buyer_email" value="'.$current_user->user_email.'">';
                        }else{
                        ?>
                            <input type="email" id="buyer_email" placeholder="E-mail">
                        <?php
                        }
                        ?>

                        <div id="card-element" style="border: 1px solid #555;margin:1rem 0;padding:0.8rem;"></div>
                        <div id="card-errors" role="alert"></div>
                        <button id="card-button" data-secret="<?php echo $intent->client_secret; ?>">
                            Pay
                        </button>
                        </form>
                    </div>
                    <div class="col-md-6 col-lg-5" style="padding:2rem;background:linear-gradient(347deg, rgba(240,240,240,1) 0%, rgba(249,249,249,1) 100%);">
                        <h1>Payment details</h1>
                        <p><strong>Amount</strong>: <?php echo $payment_amount/100; ?> €</p>
                        <p><strong>Description</strong>: <?php echo $payment_description; ?></p>
                        <?php
                            if(!empty($payment_metadata_display_info)){
                                foreach($payment_metadata_display_info as $display_info){
                                    echo '<p>'.html_entity_decode($display_info).'</p>';
                                }
                            }
                        ?>
                    </div>
                </div>

                <div id="processing_payment" class="row text-center" style="display:none;">
                    Processing...
                </div>

                <div id="finished_payment" class="row text-center" style="display:none;">
                    <h1>Great!</h1>
                    <p>Your payment has been processed.</p>
                </div>

            </div>


            <script>
                // Load Stripe JS
                var stripe = Stripe('<?php echo $this->stripe['publishable_key']; ?>');
                var elements = stripe.elements();

                // Create card element
                var style = {
                    base: {
                    color: '#32325d',
                    }
                };
                var card = elements.create('card', { style: style });
                card.mount('#card-element');

                // Listen to card changes
                card.on('change', function(event){
                    var displayError = document.getElementById('card-errors');
                    if (event.error) {
                    displayError.textContent = event.error.message;
                    } else {
                    displayError.textContent = '';
                    }
                });

                // Listen to form submit
                var form = document.getElementById('payment-form');

                form.addEventListener('submit', function(ev){
                ev.preventDefault();

                // Change to "processing payment" view
                document.getElementById('doing_payment').style.display='none';
                document.getElementById('processing_payment').style.display='block';

                // Fire confirmCardPayment method
                stripe.confirmCardPayment('<?php echo $intent->client_secret; ?>', {
                    payment_method: {
                    card: card,
                    billing_details: {
                        name: document.getElementById('buyer_name').value,
                        email: document.getElementById('buyer_email').value
                    }
                    }
                }).then(function(result){
                    if(result.error){
                    // Go back to "doing payment" view
                    document.getElementById('processing_payment').style.display='none';
                    document.getElementById('doing_payment').style.display='block';
                    // Show error to your customer (e.g., insufficient funds)
                    alert(result.error.message);
                    }else{
                        // The payment has been processed!
                        if(result.paymentIntent.status === 'succeeded'){
                            document.getElementById('processing_payment').style.display='none';
                            document.getElementById('finished_payment').style.display='block';
                        }
                    }
                });
                });
            </script>

            <?php

        }
   

    }

}