<?php

namespace TruckersMP\SteamSocialite\Contracts;

use Illuminate\Http\Request;

interface OpenID
{
    /**
     * Get the OpenID authorization URL.
     *
     * @param  string  $returnTo
     * @return string
     */
    public function getAuthUrl(string $returnTo): string;

    /**
     * Get the OpenID validation URL.
     *
     * @return string
     */
    public function getValidationUrl(): string;

    /**
     * Validate the request parameters.
     *
     * @param  Request  $request
     * @return bool
     */
    public function validate(Request $request): bool;
}
