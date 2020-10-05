<?php

namespace Trax\Auth\Contracts;

interface PermissionContract
{
    /**
     * The permission name.
     *
     * @return string
     */
    public function name(): string;
    
    /**
     * The permission description.
     *
     * @return string
     */
    public function description(): string;
    
    /**
     * The capabilities provided by this permission (ex. ['read', 'write:mine', 'delete:mine']).
     *
     * @return array
     */
    public function capabilities(): array;
    
    /**
     * Get the supported consumer types.
     *
     * @return array
     */
    public function supportedConsumerTypes(): array;

    /**
     * Check if a consumer can get this permission.
     *
     * @param \Trax\Auth\Contracts\HasPermissionsContract  $consumer
     * @return bool
     */
    public function isConsumerSupported(HasPermissionsContract $consumer): bool;
}
