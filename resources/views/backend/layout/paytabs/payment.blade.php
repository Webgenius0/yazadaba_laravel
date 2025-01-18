<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PayTabs Payment</title>
    <script type="text/javascript" src="https://www.paytabs.com/apiv2/pt.js"></script>
</head>
<body>
<h1>Proceed with Payment</h1>
<form action="https://www.paytabs.com/checkout" method="POST" id="paytabs-form">
    <input type="hidden" name="amount" value="{{ $amount }}">
    <input type="hidden" name="order_id" value="{{ $orderId }}">
    <input type="hidden" name="email" value="{{ $email }}">
    <input type="hidden" name="callback_url" value="{{ route('paytabs.callback') }}">

    <button type="submit">Pay with PayTabs</button>
</form>

<script>
    // Add your custom logic here if needed (e.g., show loading spinner)
</script>
</body>
</html>
