<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Contact Email</title>
</head>

<body style="font-family: Arial, Helvetica, sans-serif;font-size: 16px;">
    <h1>You have recieved a Contact Mail</h1>
    <h2>Details are given below:</h2>
    <strong>Name:</strong>
    <p>{{ $mailData['name'] }}</p><br>
    <strong>Email:</strong>
    <p>{{ $mailData['email'] }}</p><br>
    <strong>Subject:</strong>
    <p>{{ $mailData['subject'] }}</p><br>
    <strong>Message:</strong>
    <p>{{ $mailData['message'] }}</p>

</body>

</html>