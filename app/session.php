<?php

if(!isset($_GET['sessionID'])){
    header('Location: /main.php');
    exit;
}

$API_URL = 'http://api:8000/poker';

if($_POST['action'] == 'login'){
    if(!isset($_POST['username'])){
        header('Location: /main.php');
        exit;
    }
    $curl = curl_init($API_URL.'?Username='.$_POST['username']);
    //curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(['username' => $_POST['username']]));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    $response = json_decode($response, true);
    $userSessionID = $response['sessionID'];
    // join session
    $curl = curl_init($API_URL . '/join?sessionID=' . $_GET['sessionID'] . '&userSession=' . $userSessionID);
    //curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(['sessionID' => $_GET['sessionID'], 'userSessionID' => $userSessionID]));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    if($response != 'Joined session'){
        $response = urlencode($response);
        header('Location: /main.php?error='.$response);
        exit;
    }
    header('Location: /game.php?userSessionID=' . $userSessionID);
}
?>

<!DOCTYPE html>
<style>
    body{
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        font-family: 'Montserrat', sans-serif;
    }
    form{
        display: flex;
        flex-direction: column;
        margin: 10px;
        align-items: center;
    }
    input{
        margin: 5px;
        padding: 5px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }
</style>
<html>
<head>
    <title>Planning poker</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300&display=swap" rel="stylesheet">
</head>
<body>
    <h1>Planning poker</h1>
    <form method="post">
        <input type="hidden" name="action" value="login">
        <input type="text" name="username" placeholder="Username" required>
        <input type="submit" value="Login">
    </form>
</body>
