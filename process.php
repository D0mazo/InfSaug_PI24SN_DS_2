<?php

function transformKey($key,$length)
{
    $hash = hash('sha256',$key,true);

    if($length == 128)
        return substr($hash,0,16);

    if($length == 192)
        return substr($hash,0,24);

    if($length == 256)
        return substr($hash,0,32);
}

$operation = $_POST['operation'];
$mode = $_POST['mode'];
$key_length = $_POST['key_length'];
$key_input = $_POST['key'];

if(empty($key_input))
{
    die("Key cannot be empty");
}

$key = transformKey($key_input,$key_length);

$cipher = "AES-".$key_length."-".$mode;

$text = $_POST['text'];

if(isset($_FILES['file']) && $_FILES['file']['size'] > 0)
{
    $text = file_get_contents($_FILES['file']['tmp_name']);
}

$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));

if($operation == "encrypt")
{

    $encrypted = openssl_encrypt($text,$cipher,$key,0,$iv);

    $result = base64_encode($iv.$encrypted);

    file_put_contents("result.txt",$result);

    echo "<h2>Encrypted Text</h2>";
    echo "<textarea rows='10' cols='70'>$result</textarea>";
    echo "<br><br>";
    echo "<a href='result.txt' download>Download .txt file</a>";

}

else
{

    $data = base64_decode($text);

    $iv_length = openssl_cipher_iv_length($cipher);

    $iv = substr($data,0,$iv_length);

    $ciphertext = substr($data,$iv_length);

    $decrypted = openssl_decrypt($ciphertext,$cipher,$key,0,$iv);

    echo "<h2>Decrypted Text</h2>";
    echo "<textarea rows='10' cols='70'>$decrypted</textarea>";

}

?>