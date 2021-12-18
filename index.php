<?php

function scrp($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $out = curl_exec($ch);

    curl_close($ch);

    return substr(str_replace('searchData(', '', $out), 0, -1);
}


if(!isset($_GET['q'])||!isset($_GET['limit'])){
    $status = 404;
    $data = "Query q= and limit= not be null";
}else{
    $q = $_GET['q'];
    $limit = $_GET['limit']+1;
    $url = "https://minecraft.novaskin.me/search?q=" . $q . "&callback=searchData&json=true";
    $next = true;
    $id = 1;
    $data = array();
    $status;
    while($next){
        $sc = json_decode(scrp($url));
    
        if(isset($sc->pagination->next)){
            $pgn = $sc->pagination->next;
            foreach($sc->skins as $sk){
                array_push($data, [
                     'id' => $id,
                     'name' => $sk->title,
                     'view_skin' => $sk->screenshot,
                     'install_skin' => $sk->url_direct
                 ]);
                 $id++;
         
                 if($id == $limit){
                     $next = false;
                     break;
                 }
             }
         
             if($pgn == false){
                 $next = false;
                 break;
             }else{
                 $url = "https://minecraft.novaskin.me/search?q=" . $q . "&callback=searchData&json=true&next=" . $pgn;
             }
             $status = 200;
    
        }else{
    
            $status = 404;
            $data = "Not Found";
            $next = false;
            break;
    
        }
    }
}

header("Content-type: application/json; charset=utf-8");
echo json_encode([
    'status' => $status,
    'data' => $data
], JSON_PRETTY_PRINT);

?>