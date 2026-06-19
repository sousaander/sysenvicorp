<?php
// app/core/IAClient.php

namespace App\Core;

class IAClient {
    private $apiUrl;
    private $timeout;
    private $retries;
    private $ch;

    public function __construct() {
        $config = require __DIR__ . '/../config/ia_config.php';
        $this->apiUrl = $config['python_api']['url'];
        $this->timeout = $config['python_api']['timeout'];
        $this->retries = $config['python_api']['retries'];
        $this->ch = curl_init();
    }

    public function __destruct() {
        curl_close($this->ch);
    }

    private function request($endpoint, $method = 'GET', $data = null) {
        $url = $this->apiUrl . $endpoint;
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->timeout);
        
        if ($method === 'POST') {
            curl_setopt($this->ch, CURLOPT_POST, true);
            if ($data) {
                $jsonData = json_encode($data);
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $jsonData);
                curl_setopt($this->ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($jsonData)
                ]);
            }
        } elseif ($method === 'PUT') {
            curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            if ($data) {
                $jsonData = json_encode($data);
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $jsonData);
                curl_setopt($this->ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json'
                ]);
            }
        }

        $attempt = 0;
        while ($attempt < $this->retries) {
            $response = curl_exec($this->ch);
            $httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
            
            if ($httpCode === 200 || $httpCode === 201) {
                return json_decode($response, true);
            }
            
            $attempt++;
            if ($attempt < $this->retries) {
                sleep(1);
            }
        }
        
        return null;
    }

    public function getConfig() {
        return $this->request('/config');
    }

    public function updateConfig($config) {
        return $this->request('/config', 'POST', ['config' => $config]);
    }

    public function getStatus() {
        return $this->request('/status');
    }

    public function runNow() {
        return $this->request('/run-now', 'POST');
    }

    public function healthCheck() {
        return $this->request('/health');
    }

    public function isAlive() {
        $result = $this->healthCheck();
        return $result && isset($result['status']) && $result['status'] === 'ok';
    }
}