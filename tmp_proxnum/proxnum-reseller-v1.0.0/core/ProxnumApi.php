<?php
/**
 * Proxnum API Integration Class
 * Handles all communication with Proxnum API
 */

namespace Core;

class ProxnumApi {
    private $apiUrl;
    private $apiKey;
    private $timeout = 30;
    
    public function __construct() {
        $this->apiUrl = defined('PROXNUM_API_URL') ? PROXNUM_API_URL : 'https://proxnum.com/api/v1';
        $this->apiKey = defined('PROXNUM_API_KEY') ? PROXNUM_API_KEY : '';
    }
    
    /**
     * Make API request
     */
    private function request($method, $endpoint, $data = [], $authenticated = true) {
        $url = $this->apiUrl . $endpoint;
        
        $ch = curl_init();
        
        if ($method === 'GET' && !empty($data)) {
            $url .= '?' . http_build_query($data);
        }
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        
        $headers = ['Content-Type: application/json'];
        
        if ($authenticated) {
            $headers[] = 'Authorization: Bearer ' . $this->apiKey;
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'E9999',
                    'key' => 'connection_error',
                    'message' => 'Failed to connect to Proxnum API: ' . $error
                ]
            ];
        }
        
        $result = json_decode($response, true);
        
        if ($httpCode !== 200 && $httpCode !== 201) {
            if (!$result || !isset($result['error'])) {
                return [
                    'success' => false,
                    'error' => [
                        'code' => 'E' . $httpCode,
                        'key' => 'http_error',
                        'message' => 'HTTP Error: ' . $httpCode
                    ]
                ];
            }
        }
        
        return $result;
    }
    
    /**
     * Get countries
     */
    public function getCountries() {
        return $this->request('GET', '/countries', [], false);
    }
    
    /**
     * Get services
     */
    public function getServices() {
        return $this->request('GET', '/services', [], false);
    }
    
    /**
     * Get prices
     */
    public function getPrices($country = null, $service = null) {
        $params = [];
        if ($country) $params['country'] = $country;
        if ($service) $params['service'] = $service;
        
        return $this->request('GET', '/prices', $params, false);
    }
    
    /**
     * Get availability
     */
    public function getAvailability($country, $service) {
        return $this->request('GET', '/availability', [
            'country' => $country,
            'service' => $service
        ], false);
    }
    
    /**
     * Buy virtual number
     */
    public function buyVirtual($service, $country) {
        return $this->request('POST', '/virtual/buy', [
            'service' => $service,
            'country' => $country
        ]);
    }
    
    /**
     * Get virtual number status
     */
    public function getVirtualStatus($id) {
        return $this->request('GET', '/virtual/' . $id . '/status');
    }
    
    /**
     * Cancel virtual number
     */
    public function cancelVirtual($id) {
        return $this->request('POST', '/virtual/' . $id . '/cancel');
    }
    
    /**
     * Get user balance
     */
    public function getUserBalance() {
        return $this->request('GET', '/user/balance');
    }
    
    /**
     * Buy rental
     */
    public function buyRental($service, $country, $days = 7) {
        return $this->request('POST', '/rental/buy', [
            'service' => $service,
            'country' => $country,
            'days' => $days
        ]);
    }
    
    /**
     * Get rental status
     */
    public function getRentalStatus($id) {
        return $this->request('GET', '/rental/' . $id . '/status');
    }
    
    /**
     * Cancel rental
     */
    public function cancelRental($id) {
        return $this->request('POST', '/rental/cancel', ['id' => $id]);
    }
    
    /**
     * Get rental messages
     */
    public function getRentalMessages($rentalId) {
        return $this->request('GET', '/rentals/' . $rentalId . '/messages');
    }
    
    /**
     * List activations
     */
    public function listActivations($limit = 50, $offset = 0) {
        $page = floor($offset / $limit) + 1;
        return $this->request('GET', '/resell/activations', [
            'per_page' => $limit,
            'page' => $page
        ]);
    }
    
    /**
     * List rentals
     */
    public function listRentals($limit = 50, $offset = 0) {
        $page = floor($offset / $limit) + 1;
        return $this->request('GET', '/resell/rentals', [
            'per_page' => $limit,
            'page' => $page
        ]);
    }
    
    /**
     * Get operators
     */
    public function getOperators($country) {
        return $this->request('GET', '/operators', [
            'country' => $country
        ], false);
    }
    
    /**
     * Get rental availability
     */
    public function getRentalAvailability($country = null, $service = null) {
        $params = [];
        if ($country) $params['country'] = $country;
        if ($service) $params['service'] = $service;
        
        return $this->request('GET', '/rental/availability', $params, false);
    }
    
    /**
     * Get services by country
     */
    public function getServicesByCountry($country) {
        return $this->request('GET', '/countries/' . $country . '/services', [], false);
    }
    
    /**
     * Get rental prices
     */
    public function getRentalPrices($service, $country) {
        return $this->request('GET', '/rental/prices', [
            'service' => $service,
            'country' => $country
        ], false);
    }
}
