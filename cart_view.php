<?php include 'includes/session.php'; ?>
<?php include 'includes/config.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">

	<?php include 'includes/navbar.php'; ?>
	 
	  <div class="content-wrapper">
	    <div class="container">

	      <!-- Main content -->
	      <section class="content">
	        <div class="row">
	        	<div class="col-sm-9">
	        		<h1 class="page-header">YOUR CART</h1>
	        		<div class="box box-solid">
	        			<div class="box-body">
		        		<table class="table table-bordered">
		        			<thead>
		        				<th></th>
		        				<th>Photo</th>
		        				<th>Name</th>
		        				<th>Price</th>
		        				<th width="20%">Quantity</th>
		        				<th>Subtotal</th>
		        			</thead>
		        			<tbody id="tbody">
		        			</tbody>
		        		</table>
	        			</div>
	        		</div>
	        		<?php
	        			if(isset($_SESSION['user'])){
	        				echo "
	        					<div id='paypal-button'></div>
	        				";
	        			}
	        			else{
	        				echo "
	        					<h4>You need to <a href='login.php'>Login</a> to checkout.</h4>
	        				";
	        			}
	        		?>
	        	</div>
	        	<div class="col-sm-3">
	        		<?php include 'includes/sidebar.php'; ?>
	        	</div>
	        </div>
	      </section>
	     
	    </div>
	  </div>
  	<?php $pdo->close(); ?>
  	<?php include 'includes/footer.php'; ?>
</div>

<?php include 'includes/scripts.php'; ?>
<script>
var total = 0;
$(function(){
	$(document).on('click', '.cart_delete', function(e){
		e.preventDefault();
		var id = $(this).data('id');
		$.ajax({
			type: 'POST',
			url: 'cart_delete.php',
			data: {id:id},
			dataType: 'json',
			success: function(response){
				if(!response.error){
					getDetails();
					getCart();
					getTotal();
				}
			}
		});
	});

	$(document).on('click', '.minus', function(e){
		e.preventDefault();
		var id = $(this).data('id');
		var qty = $('#qty_'+id).val();
		if(qty>1){
			qty--;
		}
		$('#qty_'+id).val(qty);
		$.ajax({
			type: 'POST',
			url: 'cart_update.php',
			data: {
				id: id,
				qty: qty,
			},
			dataType: 'json',
			success: function(response){
				if(!response.error){
					getDetails();
					getCart();
					getTotal();
				}
			}
		});
	});

	$(document).on('click', '.add', function(e){
		e.preventDefault();
		var id = $(this).data('id');
		var qty = $('#qty_'+id).val();
		qty++;
		$('#qty_'+id).val(qty);
		$.ajax({
			type: 'POST',
			url: 'cart_update.php',
			data: {
				id: id,
				qty: qty,
			},
			dataType: 'json',
			success: function(response){
				if(!response.error){
					getDetails();
					getCart();
					getTotal();
				}
			}
		});
	});

	getDetails();
	getTotal();

});

function getDetails(){
	$.ajax({
		type: 'POST',
		url: 'cart_details.php',
		dataType: 'json',
		success: function(response){
			$('#tbody').html(response);
			getCart();
		}
	});
}

function getTotal(){
	$.ajax({
		type: 'POST',
		url: 'cart_total.php',
		dataType: 'json',
		success:function(response){
			total = response;
		}
	});
}

function getCart(){
	$.ajax({
		type: 'POST',
		url: 'cart_fetch.php',
		dataType: 'json',
		success: function(response){
			$('#cart_menu').html(response.list);
		}
	});
}

</script>
<!-- Razorpay Payment Gateway -->
<script>
console.log('Razorpay SDK loading check...');

// Razorpay payment function
function payNow() {
    if (total <= 0) {
        alert('Your cart is empty!');
        return;
    }

    // Check if Razorpay credentials are configured
    var razorpayKey = '<?php echo RAZORPAY_KEY_ID; ?>';
    if (razorpayKey === 'YOUR_RAZORPAY_KEY_ID') {
        alert('Razorpay payment is not configured yet. Please set up your credentials in includes/config.php');
        return;
    }

    var options = {
        "key": razorpayKey,
        "amount": total * 100, // Amount in paisa (multiply by 100)
        "currency": "INR",
        "name": "ShopEasy",
        "description": "Purchase Payment",
        "image": "images/logo.png", // Optional
        "handler": function (response) {
            // Payment successful
            console.log('Payment successful:', response);
            window.location = 'sales.php?pay=' + response.razorpay_payment_id;
        },
        "prefill": {
            "name": "<?php echo isset($_SESSION['user']) ? $_SESSION['user'] : ''; ?>",
            "email": "<?php echo isset($_SESSION['email']) ? $_SESSION['email'] : ''; ?>"
        },
        "theme": {
            "color": "#007bff"
        },
        "modal": {
            "ondismiss": function() {
                console.log('Payment modal dismissed');
            }
        }
    };

    var rzp = new Razorpay(options);

    rzp.on('payment.failed', function (response) {
        console.error('Payment failed:', response.error);
        alert('Payment failed: ' + response.error.description);
    });

    rzp.open();
}

// Check if Razorpay SDK is loaded
if (typeof Razorpay !== 'undefined') {
    console.log('Razorpay SDK loaded successfully');

    // Replace PayPal button with Razorpay button
    document.getElementById('paypal-button').innerHTML =
        '<button onclick="payNow()" class="btn btn-primary btn-lg" style="width: 100%; background: linear-gradient(45deg, #007bff, #0056b3); border: none; padding: 15px; font-size: 18px; border-radius: 8px;">' +
        '<i class="fa fa-credit-card"></i> Pay with Razorpay' +
        '</button>';

} else {
    console.error('Razorpay SDK not loaded');

    // Check if credentials are configured
    var razorpayKey = '<?php echo RAZORPAY_KEY_ID; ?>';
    if (razorpayKey === 'YOUR_RAZORPAY_KEY_ID') {
        document.getElementById('paypal-button').innerHTML = '<div style="color: orange; padding: 10px; border: 1px solid orange; border-radius: 5px; background: #fff3cd;"><strong>Razorpay Setup Required:</strong><br>Please configure your Razorpay credentials in <code>includes/config.php</code><br>Get credentials from <a href="https://dashboard.razorpay.com/" target="_blank">Razorpay Dashboard</a></div>';
    } else {
        document.getElementById('paypal-button').innerHTML = '<div style="color: red; padding: 10px; border: 1px solid red; border-radius: 5px; background: #ffe6e6;"><strong>Razorpay Error:</strong><br>Payment system unavailable. Razorpay SDK failed to load.<br>Please check your internet connection and try again.</div>';
    }
}
</script>
</body>
</html>