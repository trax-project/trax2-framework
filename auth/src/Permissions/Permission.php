<?php

namespace Trax\Auth\Permissions;

use Trax\Auth\Contracts\PermissionContract;
use Trax\Auth\Contracts\HasPermissionsContract;

abstract class Permission implements PermissionContract
{
    /**
     * The permission lang key: you MUST override this property.
     * key_name and key_description MUST be defined in lang files.
     *
     * @var string
     */
    protected $langKey;

    /**
     * The permission capabilities: you MUST override this property.
     *
     * @var array
     */
    protected $capabilities = [];  // e.g. ['client.read.all', 'client.write.mine'];

    /**
     * Is the permission for users: you SHOULD override this property.
     *
     * @var array
     */
    protected $supportedConsumerTypes = ['user', 'app'];

    /**
     * The permission name.
     *
     * @return string
     */
    public function name(): string
    {
        return __($this->langKey . '_name');
    }
    
    /**
     * The permission description.
     *
     * @return string
     */
    public function description(): string
    {
        return __($this->langKey . '_description');
    }
    
    /**
     * The capabilities provided by this permission (ex. ['read', 'write:mine', 'delete:mine']).
     *
     * @return array
     */
    public function capabilities(): array
    {
        return $this->capabilities;
    }
    
    /**
     * Get the supported consumer types.
     *
     * @return array
     */
    public function supportedConsumerTypes(): array
    {
        return $this->supportedConsumerTypes;
    }
    
    /**
     * Check if a consumer can get this permission.
     *
     * @param \Trax\Auth\Contracts\HasPermissionsContract  $consumer
     * @return bool
     */
    public function isConsumerSupported(HasPermissionsContract $consumer): bool
    {
        return in_array($consumer->consumerType(), $this->supportedConsumerTypes);
    }
}
