<?php
    
    require( dirname(__FILE__) . '/jwt/vendor/autoload.php' );
    use \Firebase\JWT\JWT;

    $secret_key = TU_SECRET_KEY;  // TU SECRET KEY VIRTUALPOS
    $api_key = TU_API_KEY;  // TU API KEY VIRTUALPOS
    
    $email = "contacto@andestecnologia.cl.com"; 
    $social_id = "111111111";  // RUT DE TU CLIENTE
    $first_name = "John";
    $last_name = "Doe";
    $url_retorno =  base64_encode("https://TU_DOMINIO_DE_CALLBACK/response.php");
    $monto = "50";	 //MONTO A COBRAR EN PESOS
    $buy_order = "OC09100202";  // ORDEN DE COMPRA, DEBE SER UNICA PARA CADA SOLICITUD DE PAGO
    $detalle = urlencode("Pago en ambiente de produccion por 50 pesos"); // DETALLE DEL PAGO
    $metodo_pago = "1";

	// COMPLETITUD DE LOS PARAMETROS QUE SE INCLUIRAN EN LA FIRMA
    $token_payload = array();   
    $token_payload['api_key'] = $api_key;
    $token_payload['email'] = $email;
    $token_payload['social_id'] = $social_id;
    $token_payload['first_name'] = $first_name;
    $token_payload['last_name'] = $last_name;
    $token_payload['url_retorno'] = $url_retorno;
    $token_payload['monto' ] = $monto;
    $token_payload['buy_order'] = $buy_order;
    $token_payload['detalle' ] = $detalle;
    $token_payload['metodo_pago'] = $metodo_pago;
    
    // FIRMA DE LOS PARAMETROS QUE SE DEBEN INCLUIR EN EL REQUEST HACIA VIRTUALPOS
    $jwt = JWT::encode($token_payload, base64_decode(strtr($secret_key, '-_', '+/')));
    
    $apiKey = "api_key=".$api_key;
    $email = "email=".$email;
    $social_id = "social_id=".$social_id;
    $first_name = "first_name=".$first_name;
    $last_name = "last_name=".$last_name;
    $url_retorno = "url_retorno=".$url_retorno;
    $monto = "monto=".$monto;
    $buy_order = "buy_order=".$buy_order;       
    $detalle = "detalle=".$detalle;    
    $metodo_pago = "metodo_pago=".$metodo_pago;       
	
	// FIRMA
    $s = "s=".$jwt;
    
    // URL HACIA VIRTUALPOS
    $url = "https://api.virtualpos.cl/v1/payment/request?".$apiKey."&".$email."&".$social_id."&".$first_name."&".$last_name."&".$url_retorno."&".$monto."&".$buy_order."&".$detalle."&".$metodo_pago."&".$s;	   

    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

    curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

    $headers = array();
    $headers[] = "Connection: keep-alive";
    $headers[] = "Pragma: no-cache";
    $headers[] = "Cache-Control: no-cache";
    $headers[] = "Upgrade-Insecure-Requests: 1";
    $headers[] = "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36";
    $headers[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8";
    $headers[] = "Accept-Encoding: gzip, deflate, br";
    $headers[] = "Accept-Language: es-ES,es;q=0.9,en;q=0.8,und;q=0.7,la;q=0.6";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	
	// REQUEST HACIA VIRTUALPOS POR MEDIO DE CURL
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close ($ch);
    
    // DECODIFICACION DEL RESULTADO A UN OBJETO
    $request =  json_decode($result, TRUE);
    
    // REDIRECT HACIA VIRTUALPOS CON LA URL Y EL UUID OBTENIDO PREVIAMENTE
    $redirect = $request['url_redirect'] . '&' . "uuid=". $request['order']['uuid'];
    
    header("Status: 301 Moved Permanently");
    header("Location: " . $redirect);
    exit;
    
    
?>
