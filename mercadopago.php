<?php



include_once("mercadopago/lib.php");



function mercadopago_config()

{

    global $CONFIG;

    $configarray = array(

        "FriendlyName" => array(

            "Type" => "System",

            "Value" => "MercadoPago"

        ),

        "links" => array(

            "FriendlyName" => "Informa&ccedil;&otilde;es",

            "Description" => "| Produto para uso pessoal. N&atilde;o revenda. | ( Vers&atilde;o: 2.2 ) |"

        ),

	"client_id" => array(

            "FriendlyName" => "Client Id",

            "Type" => "text",

            "Size" => "40",

            "Description" => "Voc&ecirc; pode obter esta informa&ccedil;&atilde;o atrav&eacute;s do MercadoPago da <a href='https://www.mercadopago.com/mla/herramientas/aplicaciones' target='_blank'>Argentina</a>, <a href='https://www.mercadopago.com/mlb/ferramentas/aplicacoes' target='_blank'>Brasil</a>, <a href='https://www.mercadopago.com/mco/herramientas/aplicaciones' target='_blank'>Col&ocirc;mbia</a>, <a href='https://www.mercadopago.com/mlm/herramientas/aplicaciones' target='_blank'>M&eacute;xico</a> ou <a href='https://www.mercadopago.com/mlv/herramientas/aplicaciones' target='_blank'>Venezuela</a>."

        ),

	"client_secret" => array(

            "FriendlyName" => "Client Secret",

            "Type" => "text",

            "Size" => "40",

            "Description" => "Voc&ecirc; pode obter esta informa&ccedil;&atilde;o atrav&eacute;s do MercadoPago da <a href='https://www.mercadopago.com/mla/herramientas/aplicaciones' target='_blank'>Argentina</a>, <a href='https://www.mercadopago.com/mlb/ferramentas/aplicacoes' target='_blank'>Brasil</a>, <a href='https://www.mercadopago.com/mco/herramientas/aplicaciones' target='_blank'>Col&ocirc;mbia</a>, <a href='https://www.mercadopago.com/mlm/herramientas/aplicaciones' target='_blank'>M&eacute;xico</a> ou <a href='https://www.mercadopago.com/mlv/herramientas/aplicaciones' target='_blank'>Venezuela</a>."

        ),

	"mp-mode" => array(

            "FriendlyName" => "Modo de Abertura",

            "Type" => "dropdown",

            "Options" => "Mesma Janela,Nova Janela,Janela Lightbox,Janela Pop-up",

            "Size" => "30",

            "Description" => "Defina o modo para abrir o processo de pagamento conforme o tipo de janela que prefira para o seu site."

        ),

	"auto_window" => array(

            "FriendlyName" => "Abrir Janela de Pagamento",

            "Type" => "yesno",

            "Description" => "Abrir janela de pagamento automaticamente ao acessar a fatura."

        ),

	"btn_pg_norec" => array(

            "FriendlyName" => "Texto do Bot&atilde;o de Pagamento",

            "Type" => "text",

            "Size" => "30",

            "Default" => "Pagar agora"

        ),

	"taxa_percentual" => array(

            "FriendlyName" => "Taxa Percentual (%)",

            "Type" => "text",

            "Size" => "10",

            "Description" => "Taxa para adicionar &agrave; fatura. Ex: 5 (igual a 5%). O total ser&aacute; somando com a taxa auxiliar, se houver."

        ),

	"taxa_auxiliar" => array(

            "FriendlyName" => "Taxa Auxiliar",

            "Type" => "text",

            "Size" => "10",

            "Description" => "Valor fixo adicional para a fatura. Ex: 0.50 ou 1.00"

        ),

	"estilo" => array(

            "FriendlyName" => "-- Op&ccedil;&otilde;es de CSS",

            "Description" => "(n&atilde;o altere se n&atilde;o tiver certeza.) --"

        ),

	"btn_css" => array(

            "FriendlyName" => "Classe CSS do Bot&atilde;o de Pagamento",

            "Type" => "text",

            "Size" => "30",

            "Default" => "blue-s-rn-tr"

        ),

	"custom_css" => array(

            "FriendlyName" => "CSS Personalizado",

            "Type" => "textarea",

            "Rows" => "5"

        ),

	"UsageNotes" => array(

            "Type" => "System",

            "Value" => "URL para notifica&ccedil;&atilde;o: <b>".$CONFIG["SystemURL"]."/modules/gateways/callback/mercadopago.php</b> - Insira a URL em sua conta MercadoPago da <a href='https://www.mercadopago.com/mla/herramientas/notificaciones' target='_blank'>Argentina</a>, <a href='https://www.mercadopago.com/mlb/ferramentas/notificacoes' target='_blank'>Brasil</a>, <a href='https://www.mercadopago.com/mco/herramientas/notificaciones' target='_blank'>Col&ocirc;mbia</a>, <a href='https://www.mercadopago.com/mlm/herramientas/notificaciones' target='_blank'>M&eacute;xico</a> ou <a href='https://www.mercadopago.com/mlv/herramientas/notificaciones' target='_blank'>Venezuela</a>."

        )

    );

    return $configarray;

}



function mercadopago_link($params)

{



    $taxa_percentual = ( $params['amount'] / 100) * $params['taxa_percentual'];

    $taxa_total = $taxa_percentual + $params['taxa_auxiliar'];

    $valor_total = $params['amount'] + $taxa_total;

    $valor_total = number_format($valor_total, 2, '.', '');



    $dados = array( "sponsor_id" => "131701457", "external_reference" => $params["invoiceid"], "currency" => $params["currency"], "title" => $params["description"], "description" => $params["description"], "quantity" => 1, "image" => "https://www.mercadopago.com/org-img/MP3/home/logomp3.gif", "amount" => (double) $valor_total, "payment_firstname" => $params["clientdetails"]["firstname"], "payment_lastname" => $params["clientdetails"]["lastname"], "email" => $params["clientdetails"]["email"], "pending" => $params["systemurl"] . "/viewinvoice.php?id=" . $params["invoiceid"] . "&pending=true", "approved" => $params["systemurl"] . "/viewinvoice.php?id=" . $params["invoiceid"] . "&success=true" );

    $exclude = "";

    $type = "initpoint";

    $pagamento = new mpCore($params["client_id"], $params["client_secret"]);

    $retorno1 = $pagamento->GetCheckout($dados, $exclude, $type);



    if( $params["mp-mode"] == "Janela Lightbox" ) $mp_mode = "modal";

    if( $params["mp-mode"] == "Janela Pop-up" ) $mp_mode = "popup";

    if( $params["mp-mode"] == "Nova Janela" ) $mp_mode = "blank";

    if( $params["mp-mode"] == "Mesma Janela" ) $mp_mode = "redirect";



    $recurrings = getRecurringBillingValues($params["invoiceid"]);

    if( $recurrings && !isset($recurrings["firstpaymentamount"]) && $params["assinatura"] ) 

    {

        if( $recurrings["recurringcycleunits"] == "Years" ) 

        {

            $recurrings["recurringcycleunits"] = "months";

            if( $recurrings["recurringcycleperiod"] == 1 ) 

            {

                $recurrings["recurringcycleperiod"] = 12;

            }

            else

            {

                if( $recurrings["recurringcycleperiod"] == 2 ) 

                {

                    $recurrings["recurringcycleperiod"] = 24;

                }

                else

                {

                    if( $recurrings["recurringcycleperiod"] == 3 ) 

                    {

                        $recurrings["recurringcycleperiod"] = 36;

                    }



                }



            }



        }



        $valor_recorrente = $recurrings["recurringamount"] + $taxa_total;

        $valor_recorrente = number_format($valor_recorrente, 2, '.', '');



        $preapproval_data = array( "sponsor_id" => "131701457", "payer_email" => $params["clientdetails"]["email"], "back_url" => $params["systemurl"] . "/viewinvoice.php?id=" . $params["invoiceid"] . "&success=true", "reason" => $params["description"], "external_reference" => $params["invoiceid"], "auto_recurring" => array( "frequency" => (int) $recurrings["recurringcycleperiod"], "frequency_type" => $recurrings["recurringcycleunits"], "transaction_amount" => (double) $valor_recorrente, "currency_id" => $params["currency"] ) );

        $pagamento = new mpCore($params["client_id"], $params["client_secret"]);

        $retorno2 = $pagamento->create_preapproval_payment($preapproval_data, "initpoint");



        $code = "<a href=\"" . $retorno1 . "\" name=\"MP-payButton\" id=\"MP-payButton\" class=\"".$params["btn_css"]."\" mp-mode=\"".$mp_mode."\">".$params["btn_pg_norec"]."</a><a href=\"" . $retorno2 . "\" name=\"MP-payButton\" class=\"".$params["btn_css"]."\" mp-mode=\"".$mp_mode."\">".$params["btn_pg_rec"]."</a><script type=\"text/javascript\" src=\"https://www.mercadopago.com/org-img/jsapi/mptools/buttons/render.js\"></script><style>".$params["custom_css"]."</style>";

        if ( $params['auto_window'] && !$_GET["pending"] && !$_GET["success"] ) {

            $code .= "<script type=\"text/javascript\">document.getElementById(\"MP-payButton\").click();</script>";

        }

        if ( $params['taxa_percentual'] || $params['taxa_auxiliar'] ) {

            $code .= "<p>Taxa adicional: " . formatCurrency($taxa_total) . "</p>";

            $code .= "<p>Valor total &agrave; pagar: " . formatCurrency($valor_total) . "</p>";

        }

        return $code;

    }



    $code = "<a href=\"" . $retorno1 . "\" name=\"MP-payButton\" id=\"MP-payButton\" class=\"".$params["btn_css"]."\" mp-mode=\"".$mp_mode."\">".$params["btn_pg_norec"]."</a><script type=\"text/javascript\" src=\"https://www.mercadopago.com/org-img/jsapi/mptools/buttons/render.js\"></script><style>".$params["custom_css"]."</style>";

    if ( $params['auto_window'] && !$_GET["pending"] && !$_GET["success"] ) {

        $code .= "<script type=\"text/javascript\">document.getElementById(\"MP-payButton\").click();</script>";

    }

    if ( $params['taxa_percentual'] || $params['taxa_auxiliar'] ) {

        $code .= "<p>Taxa adicional: " . formatCurrency($taxa_total) . "</p>";

        $code .= "<p>Valor total &agrave; pagar: " . formatCurrency($valor_total) . "</p>";

    }

    return $code;

}





function template_refund($params)

{

    $gatewayusername = $params["username"];

    $gatewaytestmode = $params["testmode"];

    $transid = $params["transid"];

    $amount = $params["amount"];

    $currency = $params["currency"];

    $firstname = $params["clientdetails"]["firstname"];

    $lastname = $params["clientdetails"]["lastname"];

    $email = $params["clientdetails"]["email"];

    $address1 = $params["clientdetails"]["address1"];

    $address2 = $params["clientdetails"]["address2"];

    $city = $params["clientdetails"]["city"];

    $state = $params["clientdetails"]["state"];

    $postcode = $params["clientdetails"]["postcode"];

    $country = $params["clientdetails"]["country"];

    $phone = $params["clientdetails"]["phonenumber"];

    $cardtype = $params["cardtype"];

    $cardnumber = $params["cardnum"];

    $cardexpiry = $params["cardexp"];

    $cardstart = $params["cardstart"];

    $cardissuenum = $params["cardissuenum"];

    $results = array(  );

    $results["status"] = "success";

    $results["transid"] = "12345";

    if( $results["status"] == "success" ) 

    {

        return array( "status" => "success", "transid" => $results["transid"], "rawdata" => $results );

    }



    if( $gatewayresult == "declined" ) 

    {

        return array( "status" => "declined", "rawdata" => $results );

    }



    return array( "status" => "error", "rawdata" => $results );

}
