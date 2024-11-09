<?php
namespace HUchatbots\Api;

class DifyApi {
    private $api_key;
    private $api_url;

    const DEFAULT_TIMEOUT = 30;
    const LONG_TIMEOUT = 60;

    public function __construct($api_key, $api_url) {
        if (empty($api_key) || empty($api_url)) {
            throw new \InvalidArgumentException('A chave da API e a URL são obrigatórias');
        }
        $this->api_key = $api_key;
        $this->api_url = rtrim($api_url, '/');
        error_log("DifyApi: Inicializado com a URL da API: " . $this->api_url);
    }

    public function send_message($message, $conversation_id = null, $user = "edu.ibrahim@humana.team") {
        $endpoint = $this->get_full_endpoint('/v1/chat-messages');
        error_log("DifyApi: Preparando para enviar mensagem. Endpoint: $endpoint");

        $body = [
            'inputs' => [],
            'query' => $message,
            'response_mode' => 'streaming',
            'conversation_id' => $conversation_id,
            'user' => $user
        ];
        error_log("DifyApi: Corpo da solicitação: " . json_encode($body));

        $response = $this->make_streaming_request('POST', $endpoint, $body, self::LONG_TIMEOUT);
        error_log("DifyApi: Mensagem enviada. Resposta: " . json_encode($response));

        return $response;
    }

    public function get_conversation_history($conversation_id) {
        if (empty($conversation_id)) {
            throw new \InvalidArgumentException('O ID da conversa é obrigatório');
        }

        $endpoint = $this->get_full_endpoint('/v1/messages');
        $params = ['conversation_id' => $conversation_id];

        return $this->make_request('GET', $endpoint, $params);
    }

    public function create_conversation($user = null) {
        $endpoint = $this->get_full_endpoint('/v1/conversations');
        $body = ['user' => $user];

        return $this->make_request('POST', $endpoint, $body);
    }

    public function rename_conversation($conversation_id, $name) {
        if (empty($conversation_id) || empty($name)) {
            throw new \InvalidArgumentException('ID da conversa e nome são obrigatórios');
        }

        $endpoint = $this->get_full_endpoint('/v1/conversations/' . urlencode($conversation_id));
        $body = ['name' => $name];

        return $this->make_request('PATCH', $endpoint, $body);
    }

    private function get_full_endpoint($path) {
        return $this->api_url . $path;
    }

    private function make_streaming_request($method, $endpoint, $data, $timeout = self::LONG_TIMEOUT) {
        $args = [
            'method' => $method,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
            ],
            'timeout' => $timeout,
            'body' => wp_json_encode($data),
            'stream' => true,
            'filename' => null,
        ];

        error_log("DifyApi: Fazendo solicitação de streaming para " . $endpoint);

        $response = wp_remote_request($endpoint, $args);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            error_log("DifyApi Erro: " . $error_message);
            throw new \Exception('Erro ao conectar com a API Dify: ' . $error_message);
        }

        $body = wp_remote_retrieve_body($response);
        $status_code = wp_remote_retrieve_response_code($response);

        if ($status_code >= 400) {
            error_log("DifyApi Erro: Resposta com erro - " . $body);
            throw new \Exception('A API Dify retornou erro: ' . $body);
        }

        $events = explode("\n", trim($body));
        $thoughts = [];

        foreach ($events as $event) {
            if (empty($event)) continue;
            $data = json_decode($event, true);
            if (isset($data['event']) && $data['event'] === 'agent_thought' && isset($data['thought'])) {
                $thoughts[] = $data['thought'];
            }
        }

        $response = ['answer' => implode("\n", $thoughts)];
        error_log("DifyApi: Resposta processada: " . json_encode($response));
        return $response;
    }

    private function make_request($method, $endpoint, $data = null, $timeout = self::DEFAULT_TIMEOUT) {
        $args = [
            'method' => $method,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
            ],
            'timeout' => $timeout,
        ];

        if ($method === 'GET' && !empty($data)) {
            $endpoint .= '?' . http_build_query($data);
        } elseif ($data !== null) {
            $args['body'] = wp_json_encode($data);
        }

        $response = wp_remote_request($endpoint, $args);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            error_log("DifyApi Erro: " . $error_message);
            throw new \Exception('Erro ao conectar com a API Dify: ' . $error_message);
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($status_code >= 400) {
            error_log("DifyApi Erro: Resposta com erro - " . $body);
            throw new \Exception('A API Dify retornou erro: ' . $body);
        }

        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $json_error = json_last_error_msg();
            error_log("DifyApi Erro: Decodificação JSON falhou - " . $json_error);
            throw new \Exception('Erro ao decodificar resposta da API: ' . $json_error);
        }

        return $data;
    }

    public function validate_credentials() {
        try {
            $endpoint = $this->get_full_endpoint('/v1/parameters');
            $this->make_request('GET', $endpoint);
            return true;
        } catch (\Exception $e) {
            error_log("DifyApi: Validação de credenciais falhou - " . $e->getMessage());
            return false;
        }
    }

    public function check_api_health() {
        try {
            $endpoint = $this->get_full_endpoint('/v1/health');
            $response = $this->make_request('GET', $endpoint);
            return isset($response['status']) && $response['status'] === 'ok';
        } catch (\Exception $e) {
            error_log("DifyApi: Verificação de saúde falhou - " . $e->getMessage());
            return false;
        }
    }
}
