<?php

if (!isset($_GET['userSessionID'])) {
    header('Location: /main.php');
    exit;
}

$API_URL = 'http://api:8000/poker';

if (isset($_POST['action']) && $_POST['action'] == 'vote') {
    $curl = curl_init($API_URL . '/submitVote?sessionID=' . $_GET['sessionID'] . '&userSessionID=' . $_GET['userSessionID'] . '&vote=' . $_POST['vote']);
    //curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(['sessionID' => $_GET['sessionID'], 'userSessionID' => $_GET['userSessionID'], 'vote' => $_POST['vote']]));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    if ($response == 'Session not found') {
        header('Location: /main.php?error=session_not_found');
        exit;
    }
}
if(isset($_POST['action']) && $_POST['action'] == 'EndVote'){
    $curl = curl_init($API_URL . '/finishVoting?userSessionID=' . $_GET['userSessionID']);
    //curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(['sessionID' => $_GET['sessionID'], 'userSessionID' => $_GET['userSessionID']]));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    if($response == 'Session not found'){
        header('Location: /main.php?error=session_not_found');
        exit;
    }
}

$curl = curl_init($API_URL . '/getUpdates?userSessionID=' . $_GET['userSessionID']);
//curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(['sessionID' => $_GET['sessionID']]));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($curl);
$response = json_decode($response, true);
if(isset($response['error']) && $response['error'] == 'Session not found'){
    header('Location: /main.php?error=session_not_found');
    exit;
}
?>
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
    .selectors p{
        cursor: pointer;
    }
    .selectors{
        display: flex;
        flex-direction: column;

    }
    select{
        margin: 5px;
        padding: 5px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }
    .voting-form{
        display: flex;
        flex-direction: row;
        align-items: center;
    }
</style>
<!DOCTYPE html>
<html>
<head>
    <title>Planning poker</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300&display=swap" rel="stylesheet">
</head>
<body>
    <script>
        function copySession(){
            const sessionID = '<?php echo $response['SessionID']; ?>';
            navigator.clipboard.writeText(sessionID);
        }
    </script>
    <h1>Planning poker</h1>
    <h2>Session name: <?php echo $response['Session']; ?></h2>
    <div class="selectors">
        <p onclick="copySession()">Session ID: <?php echo $response['SessionID']; ?> (click to copy)</p>
        <h2>Last vote: <?php echo $response['Last Vote']; ?></h2>
        <h2>Players voted: <?php echo $response['Players voted']; ?></h2>
    </div>
    <progress value="<?php echo explode('/', $response['Players voted'])[0]; ?>" max="<?php echo explode('/', $response['Players voted'])[1]; ?>"></progress>
    <form method="post" class="voting-form">
        <input type="hidden" name="action" value="vote">
        <select name="vote">
            <?php foreach ($response['Cards'] as $cardValue): ?>
                <option value="<?php echo $cardValue; ?>"><?php echo $cardValue; ?></option>
            <?php endforeach; ?>
        </select>
        <input type="submit" value="Vote">
    </form>
    <form method="post">
    <input type="hidden" name="action" value="EndVote">
        <input type="submit" value="End Vote">
    </form>
    <script>
        setInterval(() => {
            const API_URL = 'http://localhost:8000/poker';
            const userSessionID = '<?php echo $_GET['userSessionID']; ?>';
            const http = new XMLHttpRequest();
            http.open('GET', API_URL + '/getUpdates?userSessionID=' + userSessionID);
            http.send();
            http.onreadystatechange = (e) => {
                if (http.readyState === 4 && http.status === 200) {
                    const response = JSON.parse(http.responseText);
                    document.querySelectorAll('h2')[1].innerText = 'Last vote: ' + response['Last Vote'];
                    document.querySelectorAll('h2')[2].innerText = 'Players voted: ' + response['Players voted'];
                    $progress = response['Players voted'].split('/')[0];
                    $max = response['Players voted'].split('/')[1];
                    document.querySelector('progress').setAttribute('value', $progress);
                    document.querySelector('progress').setAttribute('max', $max);
                }
            }
        }, 2000);
    </script>
</body>
