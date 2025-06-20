<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class MixpanelService
{
    private string $token;
    private string $apiSecret;
    private bool $enabled;

    public function __construct()
    {
        $this->token = config('services.mixpanel.token', '');
        $this->apiSecret = config('services.mixpanel.api_secret', '');
        $this->enabled = config('services.mixpanel.enabled', false);
    }

    /**
     * Track event asynchronously
     */
    public function trackEvent(string $eventName, array $properties = []): void
    {
        if (!$this->enabled) {
            return;
        }

        try {
            $this->sendEvent($eventName, $properties);
        } catch (\Exception $e) {
            Log::error('Mixpanel tracking failed', [
                'event' => $eventName,
                'properties' => $properties,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Track event with user profile
     */
    public function trackEventWithProfile(string $eventName, array $properties = [], array $profileProperties = []): void
    {
        if (!$this->enabled) {
            return;
        }

        try {
            $this->sendEvent($eventName, $properties);

            if (!empty($profileProperties)) {
                $this->updateProfile($profileProperties);
            }
        } catch (\Exception $e) {
            Log::error('Mixpanel tracking with profile failed', [
                'event' => $eventName,
                'properties' => $properties,
                'profile_properties' => $profileProperties,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile(array $properties): void
    {
        if (!$this->enabled) {
            return;
        }

        try {
            $distinctId = $properties['distinct_id'] ?? null;
            if (!$distinctId) {
                throw new \InvalidArgumentException('distinct_id is required for profile update');
            }

            $data = [
                '$token' => $this->token,
                '$distinct_id' => $distinctId,
                '$set' => $properties
            ];

            Http::post('https://api.mixpanel.com/engage', [
                'data' => base64_encode(json_encode($data))
            ]);

        } catch (\Exception $e) {
            Log::error('Mixpanel profile update failed', [
                'properties' => $properties,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send event to Mixpanel
     */
    private function sendEvent(string $eventName, array $properties): void
    {
        $data = [
            'event' => $eventName,
            'properties' => array_merge([
                'token' => $this->token,
                'time' => time(),
            ], $properties)
        ];

        Http::post('https://api.mixpanel.com/track', [
            'data' => base64_encode(json_encode($data))
        ]);

        Log::info('Mixpanel event tracked', [
            'event' => $eventName,
            'distinct_id' => $properties['distinct_id'] ?? null
        ]);
    }
}
