
![enter image description here](https://s3-us-west-2.amazonaws.com/virtualpos/media/api_images/image4.png)
# API de Integración REST

Esto es una guía para integrarse con virtualpos.cl, para realizar esto, virtualpos disponibiliza una API REST con los métodos necesarios para generar un pago a través de la plataforma.

Como requisito, para poder hacer uso de las herramientas de virtualpos.cl a través de su api de integración, el usuario debe contar con lo siguiente.

 1. Ser usuario de virtualpos.cl, esto significa tener una cuenta válida y activa en la plataforma.
 2. Contar con una secret_key activa y válida con la cual poder realizar la integración.  
 3. Contar con una plataforma de software que implemente el estándar JWT (RFC 7519)

La API REST de virtualpos proporciona las siguientes operaciones

 1. Realizar el pago de una transacción (payment). 

**Credenciales de la Cuenta VirtualPOS**

Para obtener la **Api Key** y **Secret Key** de su cuenta virtualpos.cl, debes ingresar a la siguiente sección.

> **www.virtualpos.cl -> Perfil ->Configuración de cuenta VirtualPOS ->    Integración, seleccionar API REST**

![enter image description here](https://s3-us-west-2.amazonaws.com/virtualpos/media/api_images/image5.png)

    Copiar los parámetros API KEY y SECRET KEY

![enter image description here](https://s3-us-west-2.amazonaws.com/virtualpos/media/api_images/image1.png)

**Acceso a la API**
SI tienes una cuenta en Virtualpos, puedes acceder al API REST mediante los siguientes endpoints:

|Ambiente|Base URL  |
|--|--|
|  **Producción**|https://api.virtualpos.cl/v2 |
|**Sandbox**|https://api.virtualpos-sandbox.com/v2

El endpoint de **Producción** proporciona acceso directo para generar transacciones reales. El endpoint **Sandbox** permite probar su integración sin afectar los datos reales.

**I.Integración API REST.**
La integración de VirtualPOS se basa en la utilización de Json Web Token(JWT) como mecanismo de seguridad para autenticar las invocaciones a la API, el proceso resumido es el siguiente.

 1. Obtener una secret_key desde VirtualPOS, esta “clave compartida” solo debe ser conocida por VirtualPOS y por la parte que está realizando la integración.
 2. Obtener una api_key desde virtualpos, este es un identificador único de la cuenta que realizará la invocación de la api VirtualPOS.
 3. Invocar el servicio REST de virtualpos, usando la secret_key para firmar el mensaje(payload).
 4. Invocar el servicio REST de virtualpos, usando la secret_key para firmar el mensaje(payload).
Procesar el resultado de la invocación al servicio REST.

Tanto su ApiKey como su SecretKey se obtienen desde su cuenta de VirtualPos:

**Ambientes:**

| **Producción** | https://www.virtualpos.cl/admin/index.php?controller=pjAdmin&action=pjActionOwner&tab=8 |
|--|--|
| **Sandbox** | **https://www.virtualpos-sandbox.com/admin/index.php?controller=pjAdmin&action=pjActionOwner&tab=8** |


**Tarjetas de pruebas Sandbox**
 
Para las transacciones de pruebas en estos ambientes se deben usar estas tarjetas:

VISA 4051885600446623, CVV 123, cualquier fecha de expiración. Esta tarjeta genera transacciones aprobadas.
MASTERCARD 5186059559590568, CVV 123, cualquier fecha de expiración. Esta tarjeta genera transacciones rechazadas.

Cuando aparece un formulario de autenticación con RUT y clave, se debe usar el RUT 11.111.111-1 y la clave 123.

**Firmando con la secret_key**

Para integrarse con VirtualPOS es necesario firmar el mensaje con la secret_key asociada a la cuenta VirtualPOS.

En el siguiente recuadro se ejemplifica, en lenguaje **php**, el código necesario para la firma de parámetros utilizando la secret key de su cuenta virtualpos.

En este ejemplo, el servicio requiere 2 parámetros(**api_key, uuid**), los cuales deben incluirse como parámetros en el **querystring**(GET) y además, una vez concatenados, deben ser firmados digitalmente con la secret_key utilizando el estándar JWT, una vez obtenida la firma, esta debe ser incluida en el **querystring** como un parámetro más(“s”).

Los recursos necesarios para la implementación los puedes encontrar en [https://jwt.io/](https://jwt.io/)

**Ejemplo de firma en PHP**

	$secret_key = TU_SECRET_KEY;  // TU SECRET KEY VIRTUALPOS
	$api_key = TU_API_KEY;  // TU API KEY VIRTUALPOS 

	$token_payload = array();    
	$token_payload['api_key'] = $api_key;
	$token_payload['uuid'] = $uuid;

	// FIRMA DE LOS PARAMETROS QUE SE DEBEN INCLUIR EN EL REQUEST HACIA VIRTUALPOS
	$jwt = JWT::encode($token_payload, $secret_key);

	$apiKey = "api_key=".$api_key;
	$uuid = "uuid=".$uuid;
		
	// FIRMA
	$s = "s=".$jwt;
		
	// URL HACIA VIRTUALPOS
	$url = "https://api.virtualpos.cl/v2/payment/getstatus?".$apiKey."&".$uuid."&".$s;


**Realizar el pago de una transacción (payment).** 

Endpoint Producción: 

 1. https://api.virtualpos.cl/v2/payment/request: Inicia una transacción en virtualpos.cl ambiente de producción, retorna una url y un uuid para redireccionar el navegador su cliente.
 2. https://api.virtualpos.cl/v2/payment/getstatus: Retorna el status de la transacción, se debe invocar una vez que virtualpos.cl retorna el control a la pagina del su comercio. 
 
Para efectuar el pago de una transacción por medio de la API de VirtualPOS, es necesario seguir el siguiente procedimiento.
 
![enter image description here](https://s3-us-west-2.amazonaws.com/virtualpos/media/api_images/flujo_api2.png)


**1.- https://api.virtualpos.cl/v2/payment/request**: Inicia una transacción en Virtualpos.cl ambiente de producción, retorna una **url** y un **uuid** para redireccionar el navegador su cliente.

**Parámetros de entrada:**

|  Parámetro| Descripción|
|--|--|
| api_key |  código único asociado a la cuenta que se está integrando a VirtualPOS a través de la API , Tipo: String|
|email|Correo electrónico del cliente, Tipo: String (255)|
|social_id|Rut del cliente, Tipo: Rut válido sin puntos ni guion|
|first_name|Nombre del cliente, Tipo: String (255)|
|last_name|Apellido del cliente, Tipo: String (255)|
|url_retorno|URL a la cual se retornará una vez que se haya finalizado el proceso de pago en VirtualPOS, La URL debe ser codificada en Base64, Tipo: String (512)|
|monto|Monto de la venta en pesos chilenos (CLP), Tipo: Int (12) |
|buy_order|Orden de compra, representa el producto/servicio que se está pagando. Este identificador debe ser único, Tipo: Long (255)|
|detalle|Detalle del producto o Servicio que se requiere ser pagado por el cliente, Tipo: String (255)|
|metodo_pago|Identificador del medio de pago. Si se envía el identificador, el pagador será redireccionado directamente al medio de pago que se indique, de lo contrario VirtualPOS le presentará una página para seleccionarlo. Los medios de pago disponibles son: **1 Webpay**, Tipo: Int (2)|
|url_confirmacion(**opcional**)|URL a la cual se realizará un callback Asincrono una vez que se haya finalizado el proceso de pago en VirtualPOS, La URL debe ser codificada en Base64, Tipo: String (1024)|
|merchant_internal_code(**opcional**)|Codigo interno del comercio, lo puede utilizar para identificar una venta posteriormente, Tipo: String (255)|
|s|La firma de los parámetros efectuada con su secret_key|

**Parámetros de salida:**

| Parámetro | Descripción |
|--|--|
| response |  200|
|  message| ok |
| cliente | Cliente creado y asociado a la solicitud de pago |
| email |  Correo electrónico del cliente creado y asociado a la solicitud de pago|
| first_name | Nombre del cliente creado y asociado a la solicitud de pago |
| last_name |Apellido del cliente creado y asociado a la solicitud de pago  |
| order | Solicitud de pago creada para esta solicitud |
|uuid|Código único que representa la solicitud de pago de una transacción. Se recomienda almacenar este token para posteriormente consultar el resultado del registro.|
|status|Estado de la solicitud de pago: existen los siguientes 2 estados: **pendiente, pagado**|
|created|Fecha de creación de la solicitud de pago, **Formato: yyyy-mm-dd hh:mm:ss**|
|url_redirect|URL de VirtualPOS  a la cual debe ser redirigido el cliente para proceder al pago en forma segura.|

**Ejemplo:** 

{"status":"OK","code":200,"cliente":{"email":"johndoe@gmail.com","first_name":"John","last_name":"Doe"},"order":{"uuid":"34125c784ee5e520","status":"pendiente","created":"2019-02-28 18:13:09"},"url_redirect":"https:\/\/www.virtualpos.cl\/admin\/index.php?controller=apiPublic&action=pjActionDoPay"}


**Códigos de respuesta:**

| Código |  Descripción|
|--|--|
|200  | Solicitud de pago creada, se devuelven datos para continuar con el proceso de pago en VirtualPOS. |
|401|Ocurrió un problema al crear el registro.|



**2.-https://api.virtualpos.cl/v2/payment/getstatus:** Retorna el estado de la transacción, se debe invocar una vez que Virtualpos.cl retorna el control a la página del su comercio.

**Parámetros de entrada:**

| Parámetro |  Descripción|
|--|--|
| api_key | código único asociado a la cuenta que se está integrando a VirtualPOS a través de la API, Tipo: String |
|uuid|identificador único de la transacción en virtualpos, Tipo: String (255)|
|s|La firma de los parámetros efectuada con su secret_key|

**Parámetros de salida:**


| Parámetro | Descripción |
|--|--|
| response |  200|
|  message| ok |
| cliente | Cliente creado y asociado a la solicitud de pago |
| email |  Correo electrónico del cliente creado y asociado a la solicitud de pago|
| first_name | Nombre del cliente creado y asociado a la solicitud de pago |
| last_name |Apellido del cliente creado y asociado a la solicitud de pago  |
| order | Objeto orden de pago creada para esta solicitud |
|  uuid|Código único que representa la solicitud de pago de una transacción. Se recomienda almacenar este token para posteriormente consultar el resultado del registro.|
|  status|Estado de la solicitud de pago: existen los siguientes 2 estados: **pendiente, pagado**|
|monto|Monto de la venta en pesos chilenos (CLP), Tipo: Int|
|  created|Fecha de creación de la solicitud de pago, **Formato: yyyy-mm-dd hh:mm:ss**|
|  buyOrder|Orden de compra de la solicitud.|
|  card_number|Ultimos 4 digitos de la tarjeta con la que se realizó el pago|
|  transaction_date|Fecha de la transacción, **Formato: yyyy-mm-dd hh:mm:ss**|
|  auth_code|Código de autorizacion de la transacción|
|  payment_date|Fecha de abono de la transacción al comercio, **Formato: yyyy-mm-dd**|
|  amount_to_pay|Monto de abono de la transaccion al comercio, Tipo: Int| 
|  shares_amount|Monto de la cuota a pagar por el tarjeta habiente, Tipo: Int|
|  shares_number|Cuotas a pagar por el tarjeta habiente, Tipo: Int|
|  payment_type_code|tipo de tarjeta,  VD = Venta Débito. VN = Venta Normal. VC = Venta en cuotas. SI = 3 cuotas sin interés. S2 = 2 cuotas sin interés. NC = N Cuotas sin interés. VP = Venta Prepago.|
|merchant_internal_code| Codigo interno del comercio, lo puede utilizar para identificar una venta, campo opcional que retorna vacio si no es incluido al iniciar la transaccion, Tipo: String (255)|


**Ejemplo:** 

{"status":"OK","code":200,"cliente":{"email":"johndoe@gmail.com","first_name":"John","last_name":"Doe","social_id":"123123"},"order":{"uuid":"0315ea9aa823172f","status":"pagado","created":"2020-04-29 12:25:38","buyOrder":"OCx000031x0315e349aa823172f","card_number":"7611","transaction_date":"2020-04-29 12:25","auth_code":"009235","payment_date":"2020-05-05","amount_to_pay":"967","shares_amount":"0","shares_number":"0","payment_type_code":"VD"}}

**Códigos de respuesta:**

| Código | Descripción |
|--|--|
| 200 | Solicitud de pago existente, se devuelven datos del estado actual de la solicitud de pago |
|401|Ocurrió un problema al recuperar el registro.|
