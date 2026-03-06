<?php

function transformuotiRaktoIlgi($raktas, $ilgis)
{
    $hash = hash('sha256', $raktas, true);

    if ($ilgis == 128)
        return substr($hash, 0, 16);

    if ($ilgis == 192)
        return substr($hash, 0, 24);

    if ($ilgis == 256)
        return substr($hash, 0, 32);
}


$operacija = $_POST['operation'];
$veiksena = $_POST['mode'];
$rakto_ilgis = $_POST['key_length'];
$ivestas_raktas = $_POST['key'];


if (empty($ivestas_raktas)) {
    die("Klaida: slaptas raktas negali būti tuščias.");
}


$raktas = transformuotiRaktoIlgi($ivestas_raktas, $rakto_ilgis);

$sifravimo_algoritmas = "AES-" . $rakto_ilgis . "-" . $veiksena;


$tekstas = $_POST['text'];


if (isset($_FILES['file']) && $_FILES['file']['size'] > 0) {
    $tekstas = file_get_contents($_FILES['file']['tmp_name']);
}


$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($sifravimo_algoritmas));


if ($operacija == "encrypt") {

    $uzsifruotas = openssl_encrypt($tekstas, $sifravimo_algoritmas, $raktas, 0, $iv);

    $rezultatas = base64_encode($iv . $uzsifruotas);

    file_put_contents("rezultatas.txt", $rezultatas);

    echo "<h2>Užšifruotas tekstas</h2>";
    echo "<textarea rows='10' cols='70'>" . htmlspecialchars($rezultatas) . "</textarea>";
    echo "<br><br>";
    echo "<a href='rezultatas.txt' download>Atsisiųsti .txt failą</a>";

}
else {

    $duomenys = base64_decode($tekstas);

    $iv_ilgis = openssl_cipher_iv_length($sifravimo_algoritmas);

    $iv = substr($duomenys, 0, $iv_ilgis);

    $sifrotekstas = substr($duomenys, $iv_ilgis);

    $desifruotas = openssl_decrypt($sifrotekstas, $sifravimo_algoritmas, $raktas, 0, $iv);

    echo "<h2>Iššifruotas tekstas</h2>";
    echo "<textarea rows='10' cols='70'>" . htmlspecialchars($desifruotas) . "</textarea>";
}

?>