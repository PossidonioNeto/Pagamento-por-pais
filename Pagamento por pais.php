function custom_payment_gateway_countries($available_gateways) {
    if (is_admin()) {
        return $available_gateways;
    }

    // Verifica se é um ambiente de desenvolvimento local
    if (in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'))) {
        // Define o país como Brasil para testes locais
        $customer_country = 'BR';
    } else {
        // Obtém o endereço IP do cliente
        $ip_address = $_SERVER['REMOTE_ADDR'];

        // Consulta a API GeolocationDB para obter o país do cliente
        $api_url = 'https://geolocation-db.com/json/' . $ip_address;
        $response = wp_remote_get($api_url);

        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            // Obtém o país do cliente a partir da resposta da API
            $customer_country = $data['country_code'];
        } else {
            // Caso ocorra um erro ao obter a resposta da API, defina o país padrão como Brasil
            $customer_country = 'BR';
        }
    }

    // Define os métodos de pagamento permitidos para o Brasil (BR)
    if ($customer_country === 'BR') {
        // Habilita apenas o PagSeguro
        if (isset($available_gateways['pagseguro'])) {
            return array('pagseguro' => $available_gateways['pagseguro'], 'paypal' => $available_gateways['paypal']);
        }
    } else {
        // Mantém ambos os métodos de pagamento (PagSeguro e PayPal) disponíveis
        return $available_gateways;
    }

    // Retorna apenas os métodos de pagamento disponíveis para o Brasil
    return $available_gateways;
}
add_filter('woocommerce_available_payment_gateways', 'custom_payment_gateway_countries');
