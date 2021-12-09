<!DOCTYPE html>
<html>
<head>
    <title>Simple Blog</title>
</head>
<body>
    <h1>{{ $details['title'] }}</h1>
    <p>Hello {{ $details['first_name'] }}, <br><br>

    Welcome to Simple Blog. It is nice to have you on board. <br>

    Please verify your account to proceed with us. <br><br>

    Your Verification code is <b> {{ $details['verification_code'] }} </b>
    
    <br><br>

    THANK YOU.
   
    <p>Simple Blog</p>
</body>
</html>