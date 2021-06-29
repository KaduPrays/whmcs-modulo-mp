<?php 



class mpAuth extends mpCall

{

    public $client_id = NULL;

    public $client_secret = NULL;

    public $refresh = NULL;

    public $newrefresh = null;

    public $accesstoken = NULL;

    public $error = NULL;

    protected $date = NULL;

    protected $expired = NULL;

    protected $b2b2c = false;



    public function GeAuthCore($url)

    {

        $link = "https://auth.mercadolibre.com.ar/authorization?client_id=" . $this->client_id . "&response_type=code&platform_id=mp&redirect_uri=" . $url;

        return $link;

    }



    public function GetRefreshToken($auth, $redict_url)

    {

        $url = "https://api.mercadolibre.com/oauth/token";

        $header = array( "Accept: application/json" );

        $post = array( "grant_type" => "authorization_code", "client_id" => $this->client_id, "client_secret" => $this->client_secret, "code" => $auth, "redirect_uri" => $redict_url );

        $dados = $this->DoPost($post, $url, $header, "200", "post", "post");

        return $dados;

    }



    public function getAccessToken()

    {

        $data = getdate();

        $time = $data[0];

        if( isset($this->accesstoken) && isset($this->date) ) 

        {

            $timedifference = $time - $this->date;

            if( $timedifference < $this->expired ) 

            {

                return $this->accesstoken;

            }



        }



        if( $this->refresh != null ) 

        {

            $post = array( "client_id" => $this->client_id, "client_secret" => $this->client_secret, "grant_type" => "refresh_token", "refresh_token" => $this->refresh );

            $this->b2b2c = true;

            $header = array( "Accept: application/json", "Content-Type: application/x-www-form-urlencoded" );

            $url = "https://api.mercadolibre.com/oauth/token";

            $dados = $this->DoPost($post, $url, $header, "200", "post", "post");

            $this->accesstoken = $dados["access_token"];

            $this->newrefresh = $dados["refresh_token"];

            $this->date = $time;

            $this->expired = $dados["expires_in"];

            return $dados["access_token"];

        }



        $post = array( "client_id" => $this->client_id, "client_secret" => $this->client_secret, "grant_type" => "client_credentials" );

        $header = array( "Accept: application/json", "Content-Type: application/x-www-form-urlencoded" );

        $url = "https://api.mercadolibre.com/oauth/token";

        $dados = $this->DoPost($post, $url, $header, "200", "post", "post");

        $this->accesstoken = $dados["access_token"];

        $this->date = $time;

        $this->expired = $dados["expires_in"];

        return $dados["access_token"];

    }



}





class mpCore extends mpAuth

{

    public function __construct($client_id, $client_secret)

    {

        $this->client_id = $client_id;

        $this->client_secret = $client_secret;

    }



    public function GetMethods($country_id)

    {

        $url = "https://api.mercadolibre.com/sites/" . $country_id . "/payment_methods";

        $header = array( "Content-Type:application/json" );

        $methods = $this->DoPost($opt = null, $url, $header, "200", "none", "get");

        return $methods;

    }



    public function GetCheckout($data, $excludes, $method = "lightbox")

    {

        if( $excludes != "" ) 

        {

            $methods_excludes = preg_split("/[\\s,]+/", $excludes);

            foreach( $methods_excludes as $exclude ) 

            {

                $excludemethods[] = array( "id" => $exclude );

            }

            if( !isset($data["mkfee"]) ) 

            {

                $data["mkfee"] = "";

            }



            $data["mkfee"] = "";

            $opt = array( "external_reference" => $data["external_reference"], "items" => array( array( "id" => $data["external_reference"], "title" => $data["title"], "description" => $data["quantity"] . " x " . $data["title"], "quantity" => $data["quantity"], "unit_price" => round($data["amount"], 2), "currency_id" => $data["currency"], "picture_url" => $data["image"] ) ), "payer" => array( "name" => $data["payment_firstname"], "surname" => $data["payment_lastname"], "email" => $data["email"] ), "back_urls" => array( "pending" => $data["pending"], "success" => $data["approved"] ), "payment_methods" => array( "excluded_payment_methods" => $excludemethods ) );

        }

        else

        {

            $opt = array( "external_reference" => $data["external_reference"], "items" => array( array( "id" => $data["external_reference"], "title" => $data["title"], "description" => $data["quantity"] . " x " . $data["title"], "quantity" => $data["quantity"], "unit_price" => round($data["amount"], 2), "currency_id" => $data["currency"], "picture_url" => $data["image"] ) ), "payer" => array( "name" => $data["payment_firstname"], "surname" => $data["payment_lastname"], "email" => $data["email"] ), "back_urls" => array( "pending" => $data["pending"], "success" => $data["approved"] ) );

        }



        $this->getAccessToken();

        $url = "https://api.mercadolibre.com/checkout/preferences?access_token=" . $this->accesstoken;

        $header = array( "Content-Type:application/json", "Accept: application/json" );

        $dados = $this->DoPost($opt, $url, $header, "201", "json", "post");

        $link = $dados["init_point"];

        switch( $method ) 

        {

            case "lightbox":

                $bt = "<a href=\"" . $link . "\" name=\"MP-payButton\" class=\"blue-l-rn-ar\">Comprar</a>\n    <script type=\"text/javascript\" src=\"https://www.mercadopago.com/org-img/jsapi/mptools/buttons/render.js\"></script>";

                break;

            case "Iframe":

                $bt = "<iframe id=\"MP-Checkout-IFrame\" frameborder=\"0\" style=\"width: 740px; height: 480px;\" src=\"" . $link . "\">";

                break;

            case "initpoint":

                $bt = $link;

                break;

            default:

                $bt = "<a href=\"" . $link . "\" name=\"MP-payButton\" class=\"blue-l-rn-ar\">Comprar</a>\n    <script type=\"text/javascript\" src=\"https://www.mercadopago.com/org-img/jsapi/mptools/buttons/render.js\"></script>";

                break;

        }

        if( $this->b2b2c ) 

        {

            $return = array( $this->newrefresh, $bt );

            return $return;

        }



        return $bt;

    }



    public function GetStatus($id)

    {

        $this->getAccessToken();

        $url = "https://api.mercadolibre.com/collections/notifications/" . $id . "?access_token=" . $this->accesstoken;

        $header = array( "Accept: application/json", "Content-Type: application/x-www-form-urlencoded" );

        $retorno = $this->DoPost($opt = null, $url, $header, "200", "none", "get");

        return $retorno;

    }



    public function SearchPayment($dados = array(  ), $limit = null, $offset = null, $sort = null, $order = null)

    {

        $field = "";

        foreach( $dados as $key => $value ) 

        {

            if( $key == "date_created" || $key == "date_approved" || $key == "last_modified" ) 

            {

                $field .= "range=" . $key . "&";

                foreach( $value as $keys => $dates ) 

                {

                    if( $keys == "start" ) 

                    {

                        $field .= "begin_date=" . $dates . "&";

                    }

                    else

                    {

                        if( $keys == "end" ) 

                        {

                            $field .= "end_date=" . $dates . "&";

                        }



                    }



                }

            }

            else

            {

                $field .= $key . "=" . $value . "&";

            }



        }

        $this->getAccessToken();

        if( $limit != null ) 

        {

            $field .= "limit=" . $limit . "&";

        }



        if( $offset != null ) 

        {

            $field .= "offset=" . $offset . "&";

        }



        if( $sort != null ) 

        {

            $field .= "sort=" . $sort . "&";

        }



        if( $order != null ) 

        {

            $field .= "order=" . $order . "&";

        }



        $header = array( "Accept: application/json", "Content-Type: application/x-www-form-urlencoded" );

        $url = "https://api.mercadolibre.com/collections/search?access_token=" . $this->accesstoken . "&" . $field;

        var_dump($url);

        $retorno = $this->DoPost($opt = null, $url, $header, "none", "none", "get");

        return $retorno;

    }



    public function RefundPayment($id)

    {

        $this->getAccessToken();

        $header = array( "Content-Type:application/json", "Accept: application/json" );

        $opt = "{\"status\":\"cancelled\"}";

        $url = "https://api.mercadolibre.com/collections/" . $id . "?access_token=" . $this->accesstoken;

        $teste = $this->DoPost($opt, $url, $header, "200", "none", "put");

        return $teste;

    }



    public function create_preapproval_payment($preapproval_payment, $method = "lightbox")

    {

        $this->getAccessToken();

        $header = array( "Content-Type:application/json", "Accept: application/json" );

        $url = "https://api.mercadolibre.com/preapproval?access_token=" . $this->accesstoken;

        $preapproval_payment_result = $this->DoPost($preapproval_payment, $url, $header, "201", "json", "post");

        $link = $preapproval_payment_result["init_point"];

        switch( $method ) 

        {

            case "lightbox":

                $bt = "<a href=\"" . $link . "\" name=\"MP-payButton\" class=\"blue-l-rn-ar\">Comprar</a>\n<script type=\"text/javascript\" src=\"https://www.mercadopago.com/org-img/jsapi/mptools/buttons/render.js\"></script>";

                break;

            case "Iframe":

                $bt = "<iframe id=\"MP-Checkout-IFrame\" frameborder=\"0\" style=\"width: 740px; height: 480px;\" src=\"" . $link . "\">";

                break;

            case "initpoint":

                $bt = $link;

                break;

            default:

                $bt = "<a href=\"" . $link . "\" name=\"MP-payButton\" class=\"blue-l-rn-ar\">Comprar</a>\n    <script type=\"text/javascript\" src=\"https://www.mercadopago.com/org-img/jsapi/mptools/buttons/render.js\"></script>";

                break;

        }

        if( $this->b2b2c ) 

        {

            $return = array( $this->newrefresh, $bt );

            return $return;

        }



        return $bt;

    }



}





class mpCall

{

    public static function debug($error)

    {

        echo "<br>Retorno error<br><pre>";

        print_r($error);

        echo "</pre><br><br>";

    }



    public function DoPost($fields, $url, $heads, $codeexpect, $type, $method)

    {

        if( $type == "json" ) 

        {

            $posts = json_encode($fields);

        }

        else

        {

            if( $type == "none" ) 

            {

                $posts = $fields;

            }

            else

            {

                $posts = http_build_query($fields);

            }



        }



        switch( $method ) 

        {

            case "get":

                $options = array( CURLOPT_RETURNTRANSFER => "1", CURLOPT_HTTPHEADER => $heads, CURLOPT_SSL_VERIFYPEER => "false", CURLOPT_URL => $url, CURLOPT_POSTFIELDS => $posts, CURLOPT_CUSTOMREQUEST => "GET", CURLOPT_CONNECTTIMEOUT => 0 );

                break;

            case "put":

                $options = array( CURLOPT_RETURNTRANSFER => 1, CURLOPT_HTTPHEADER => $heads, CURLOPT_SSL_VERIFYPEER => "false", CURLOPT_URL => $url, CURLOPT_POSTFIELDS => $posts, CURLOPT_CUSTOMREQUEST => "PUT", CURLOPT_HEADER => 1, CURLOPT_CONNECTTIMEOUT => 0 );

                break;

            case "post":

                $options = array( CURLOPT_RETURNTRANSFER => "1", CURLOPT_HTTPHEADER => $heads, CURLOPT_SSL_VERIFYPEER => "false", CURLOPT_URL => $url, CURLOPT_POSTFIELDS => $posts, CURLOPT_CUSTOMREQUEST => "POST", CURLOPT_CONNECTTIMEOUT => 0 );

                break;

            case "delete":

                $options = array( CURLOPT_RETURNTRANSFER => "1", CURLOPT_HTTPHEADER => $heads, CURLOPT_SSL_VERIFYPEER => "false", CURLOPT_URL => $url, CURLOPT_POSTFIELDS => $posts, CURLOPT_CUSTOMREQUEST => "DELETE", CURLOPT_CONNECTTIMEOUT => 0 );

                break;

            default:

                $options = array( CURLOPT_RETURNTRANSFER => "1", CURLOPT_HTTPHEADER => $heads, CURLOPT_SSL_VERIFYPEER => "false", CURLOPT_URL => $url, CURLOPT_POSTFIELDS => $posts, CURLOPT_CUSTOMREQUEST => "GET", CURLOPT_CONNECTTIMEOUT => 0 );

                break;

        }

        $options[CURLOPT_SSLVERSION] = TLSv1;

        $call = curl_init();

        curl_setopt_array($call, $options);

        $dados = curl_exec($call);

        $status = curl_getinfo($call);

        curl_close($call);

        if( $status["http_code"] != $codeexpect && $codeexpect != "none" ) 

        {

            $this->debug($dados);

            return false;

        }



        return json_decode($dados, true);

    }



}



