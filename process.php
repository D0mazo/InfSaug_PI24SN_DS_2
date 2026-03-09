<?php

function transformuotiRaktoIlgi($raktas,$ilgis)
{
    $hash = hash('sha256',$raktas,true);

    if($ilgis==128) return substr($hash,0,16);
    if($ilgis==192) return substr($hash,0,24);
    if($ilgis==256) return substr($hash,0,32);
}

$operacija=$_POST['operation'];
$veiksena=$_POST['mode'];
$rakto_ilgis=$_POST['key_length'];
$raktas_input=$_POST['key'];

if(empty($raktas_input)){
    echo "Klaida: slaptas raktas negali būti tuščias.";
    exit;
}

$raktas=transformuotiRaktoIlgi($raktas_input,$rakto_ilgis);

$cipher="AES-$rakto_ilgis-$veiksena";

$tekstas=$_POST['text'] ?? "";

if(isset($_FILES['file']) && $_FILES['file']['size']>0){
    $tekstas=file_get_contents($_FILES['file']['tmp_name']);
}

if(empty($tekstas)){
    echo "Klaida: tekstas negali būti tuščias.";
    exit;
}

$iv_ilgis=openssl_cipher_iv_length($cipher);



if($operacija=="encrypt"){

    $iv=$iv_ilgis>0 ? random_bytes($iv_ilgis) : "";

    $encrypted=openssl_encrypt($tekstas,$cipher,$raktas,OPENSSL_RAW_DATA,$iv);

    if($encrypted===false){
        echo "Klaida: nepavyko užšifruoti teksto.";
        exit;
    }

    $rezultatas=$iv_ilgis>0
        ? base64_encode($iv.$encrypted)
        : base64_encode($encrypted);

    echo $rezultatas;
}



else{

    $data=base64_decode($tekstas,true);

    if($data===false){
        echo "Klaida: įvestas tekstas nėra teisingas šifruotas (Base64) tekstas.";
        exit;
    }

    if(strlen($data) < $iv_ilgis){
        echo "Klaida: šifruotas tekstas per trumpas arba sugadintas.";
        exit;
    }

    if($iv_ilgis>0){
        $iv=substr($data,0,$iv_ilgis);
        $ciphertext=substr($data,$iv_ilgis);
    }else{
        $iv="";
        $ciphertext=$data;
    }

    $decrypted=openssl_decrypt($ciphertext,$cipher,$raktas,OPENSSL_RAW_DATA,$iv);

    if($decrypted===false){
        echo "Klaida: nepavyko iššifruoti teksto. Patikrinkite raktą, šifravimo modą arba rakto ilgį.";
        exit;
    }

    echo $decrypted;
}

?>