<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Service;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Platform;

/**
 * Service for detecting and managing allowed hosts for the proxy
 */
class HostDetectionService
{
    /**
     * Get the current host from the server environment
     * 
     * @return string
     */
    public function getCurrentHost(): string
    {
        return $_SERVER['HTTP_HOST'] ?? '';
    }
    
    /**
     * Get all allowed hosts including the current host
     * 
     * @return array
     */
    public function getAllowedHosts(): array
    {
        // Get base allowed hosts from config
        $baseHosts = Pdk::get('allowedProxyHosts');
        
        // Get the current host
        $currentHost = $this->getCurrentHost();
        
        // Add current host if it exists
        if ($currentHost && !in_array($currentHost, $baseHosts)) {
            $baseHosts[] = $currentHost;
            
            // Update allowed hosts in platform-specific storage if needed
            $this->updateStoredAllowedHosts($baseHosts);
        }
        
        return $baseHosts;
    }
    
    /**
     * Update stored hosts in the platform specific way
     * 
     * @param array $hosts
     * @return void
     */
    private function updateStoredAllowedHosts(array $hosts): void
    {
        $platform = Platform::getPlatform();
        
        switch ($platform) {
            case 'woocommerce':
                // WooCommerce implementation
                if (function_exists('update_option')) {
                    update_option('myparcel_pdk_allowed_hosts', $hosts);
                }
                break;
                
            case 'prestashop':
                // PrestaShop implementation
                if (class_exists('Configuration')) {
                    \Configuration::updateValue('MYPARCEL_PDK_ALLOWED_HOSTS', json_encode($hosts));
                }
                break;
                
            default:
                // Other platforms might have different storage methods
                break;
        }
    }
} 