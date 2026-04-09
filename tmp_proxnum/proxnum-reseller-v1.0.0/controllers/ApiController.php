<?php
/**
 * API Controller
 * Handles AJAX and API requests
 */

namespace Controllers;

use Core\Controller;
use Core\ProxnumApi;
use Core\Helper;

class ApiController extends Controller {
    
    /**
     * Get prices
     */
    public function prices() {
        $service = $_GET['service'] ?? null;
        $country = $_GET['country'] ?? null;
        
        if (!$service || !$country) {
            $this->json([
                'success' => false,
                'message' => 'Service and country are required'
            ]);
            return;
        }
        
        try {
            $api = new ProxnumApi();
            $result = $api->getPrices($country, $service);
            
            // Check if we got a valid response
            if (!is_array($result)) {
                $this->json([
                    'success' => false,
                    'message' => 'Invalid API response format'
                ]);
                return;
            }
            
            // Extract price from nested prices object if needed
            $price = null;
            
            if (isset($result['prices'][$country][$service]['sell_price'])) {
                $price = $result['prices'][$country][$service]['sell_price'];
            } elseif (isset($result['prices'][$country][$service]['base_price'])) {
                $price = $result['prices'][$country][$service]['base_price'];
            } elseif (isset($result['prices']['price'])) {
                $price = $result['prices']['price'];
            } elseif (isset($result['prices']) && is_array($result['prices'])) {
                // Try to find price in nested structure
                $prices = $result['prices'];
                
                // Try to iterate and find any sell_price or base_price
                foreach ($prices as $countryKey => $countryData) {
                    if (is_array($countryData)) {
                        foreach ($countryData as $serviceKey => $serviceData) {
                            if (isset($serviceData['sell_price'])) {
                                $price = $serviceData['sell_price'];
                                break 2;
                            } elseif (isset($serviceData['base_price'])) {
                                $price = $serviceData['base_price'];
                                break 2;
                            } elseif (is_numeric($serviceData)) {
                                $price = $serviceData;
                                break 2;
                            }
                        }
                    }
                }
            } elseif (isset($result['price'])) {
                $price = $result['price'];
            }
            
            // Apply price multiplier if price exists
            if ($price !== null && is_numeric($price)) {
                // Apply global multiplier
                $globalMultiplier = (float)Helper::getSetting('price_multiplier', '1');
                $priceWithGlobal = $price * $globalMultiplier;
                
                // Apply service/country specific multiplier if exists
                $specificMultiplier = (float)Helper::getSetting('multiplier_' . $service . '_' . $country, '1');
                $finalPrice = $priceWithGlobal * $specificMultiplier;
                
                // Return simplified response
                $this->json([
                    'success' => true,
                    'price' => $finalPrice,
                    'original_price' => $price,
                    'global_multiplier' => $globalMultiplier,
                    'specific_multiplier' => $specificMultiplier
                ]);
                return;
            }
            
            // No price found
            $this->json([
                'success' => false,
                'message' => 'Price not available for this service/country combination'
            ]);
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Error fetching price: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get availability
     */
    public function availability() {
        $service = $_GET['service'] ?? null;
        $country = $_GET['country'] ?? null;
        
        if (!$service || !$country) {
            $this->json(['success' => false, 'message' => 'Service and country required']);
            return;
        }
        
        $api = new ProxnumApi();
        $result = $api->getAvailability($country, $service);
        
        // Normalize response - API might return 'available' or 'count'
        if (isset($result['available']) && !isset($result['count'])) {
            $result['count'] = $result['available'];
        }
        
        $this->json($result);
    }
    
    /**
     * Get countries
     */
    public function countries() {
        $api = new ProxnumApi();
        $result = $api->getCountries();
        
        $this->json($result);
    }
    
    /**
     * Get services
     */
    public function services() {
        $api = new ProxnumApi();
        $result = $api->getServices();
        
        $this->json($result);
    }
    
    /**
     * Check activation status (AJAX)
     */
    public function checkStatus() {
        $this->requireAuth();
        
        $id = $_GET['id'] ?? 0;
        $user = $this->getUser();
        
        $activation = $this->db->fetch(
            'SELECT * FROM activations WHERE id = ? AND user_id = ?',
            [$id, $user['id']]
        );
        
        if (!$activation) {
            $this->json(['success' => false, 'message' => 'Not found']);
        }
        
        if ($activation['status'] !== 'pending') {
            $this->json([
                'success' => true,
                'status' => $activation['status'],
                'code' => $activation['code']
            ]);
        }
        
        // Check with API
        $api = new ProxnumApi();
        $result = $api->getVirtualStatus($activation['activation_id']);
        
        if ($result['success'] && isset($result['activation']['code']) && !empty($result['activation']['code'])) {
            // Update database
            $this->db->update('activations', [
                'code' => $result['activation']['code'],
                'status' => 'completed',
                'completed_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$id]);
            
            $this->json([
                'success' => true,
                'status' => 'completed',
                'code' => $result['activation']['code']
            ]);
        }
        
        $this->json(['success' => true, 'status' => 'pending']);
    }
}
