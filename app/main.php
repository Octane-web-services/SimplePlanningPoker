<?php
// Create or join a session
$API_URL = 'http://api:8000/poker';

if (isset($_POST['action']) && $_POST['action'] == 'create') {
    $cardValues = [];
    $min = $_POST['min'];
    $max = $_POST['max'];
    $step = $_POST['step'];
    for ($i = $min; $i <= $max; $i += $step) {
        $cardValues[] = $i;
    }
    $curl = curl_init($API_URL . '/create?sessionName=' . $_POST['sessionName'] . '&cardValues=' . implode(',', $cardValues));
    //curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(['sessionName' => $_POST['sessionName'], 'cardValues' => $_POST['cardValues']]));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    $response = json_decode($response, true);
    header('Location: /session.php?sessionID=' . $response['sessionID']);
    exit;
}else if (isset($_POST['action']) && $_POST['action'] == 'join') {
    if (isset($_POST['sessionID'])){
        header('Location: /session.php?sessionID=' . $_POST['sessionID']);
    }
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
    .selectors{
        display: flex;
        flex-direction: row;
    }
    .selectors p{
        margin: 0 10px;
        cursor: pointer;
        width: 150px;
        text-align: center;
        padding: 5px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }
    form{
        display: flex;
        flex-direction: column;
        margin: 10px;
        align-items: center;
    }
    form.hidden{
        display: none;
    }
    input{
        margin: 5px;
        padding: 5px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }
    input[type="submit"]{
        width: 100px;
        height: 30px;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
    input[type="submit"]:hover{
        background-color: #45a049;
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
        function toggleForms(form){
            if(form == 'create'){
                document.querySelector('.create').classList.remove('hidden');
                document.querySelector('.join').classList.add('hidden');
            }else{
                document.querySelector('.create').classList.add('hidden');
                document.querySelector('.join').classList.remove('hidden');
            }
        }

    </script>
    <h1>Planning poker</h1>
    <div class="selectors">
        <p onclick="toggleForms('create')">Create session</p>
        <p onclick="toggleForms('join')">Join session</p>
    </div>
    <form method="post" class="hidden create">
        <input type="hidden" name="action" value="create">
        <input type="text" name="sessionName" placeholder="Session name" required>
        <input type="text" name="min" placeholder="Min" required>
        <input type="text" name="max" placeholder="Max" required>
        <input type="text" name="step" placeholder="Step" required>
        <input type="submit" value="Create session">
    </form>
    <form method="post" class="join">
        <input type="hidden" name="action" value="join">
        <input type="text" name="sessionID" placeholder="Session ID" required>
        <input type="submit" value="Join session">
    </form>
</body>
