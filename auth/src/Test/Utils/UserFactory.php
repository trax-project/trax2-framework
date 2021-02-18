<?php

namespace Trax\Auth\Test\Utils;

use Trax\Auth\Stores\Users\UserRepository;

class UserFactory extends Factory
{
    /**
     * @var \Trax\Auth\Test\Utils\OwnerFactory
     */
    protected $ownerFactory;

    /**
     * @var \Trax\Auth\Test\Utils\EntityFactory
     */
    protected $entityFactory;

    /**
     * @var \Trax\Auth\Test\Utils\RoleFactory
     */
    protected $roleFactory;

    /**
     * Set the owner factory because it is needed.
     *
     * @param \Trax\Auth\Test\Utils\OwnerFactory  $ownerFactory
     * @return void
     */
    public function setOwnerFactory(OwnerFactory $ownerFactory): void
    {
        $this->ownerFactory = $ownerFactory;
    }

    /**
     * Set the entity factory because it is needed.
     *
     * @param \Trax\Auth\Test\Utils\EntityFactory  $entityFactory
     * @return void
     */
    public function setEntityFactory(EntityFactory $entityFactory): void
    {
        $this->entityFactory = $entityFactory;
    }

    /**
     * Set the role factory because it is needed.
     *
     * @param \Trax\Auth\Test\Utils\RoleFactory  $roleFactory
     * @return void
     */
    public function setRoleFactory(RoleFactory $roleFactory): void
    {
        $this->roleFactory = $roleFactory;
    }

    /**
     * Generate the required data to make a resource.
     *
     * @param array  $data
     * @param mixed  $model
     * @return array
     */
    public function requiredData(array $data = [], $model = null): array
    {
        if (isset($model)) {
            $required = [
                'email' => $model->email,
                'username' => $model->username,
                'firstname' => $model->firstname,
                'lastname' => $model->lastname,
                'role_id' => $model->role_id,
                'owner_id' => $model->owner_id,
            ];
        } else {
            $required = [
                'email' => $this->faker->email,
                'username' => $this->faker->username,
                'firstname' => $this->faker->firstName,
                'lastname' => $this->faker->lastName,
            ];

            // Owner.
            if (isset($data['owner'])) {
                $ownerData = $data['owner'];
                $owner = $this->ownerFactory->make($ownerData);
                $data['owner_id'] = $owner->id;
            }
            unset($data['owner']);

            // Do we need a role?
            if (isset($data['role'])) {
                $roleData = $data['role'];
                if (isset($data['owner_id'])) {
                    $roleData['owner_id'] = $data['owner_id'];
                }
                $role = $this->roleFactory->make($roleData);
                $required['role_id'] = $role->id;
            }
            unset($data['role']);

            // Entity.
            if (isset($data['entity'])) {
                $entityData = $data['entity'];
                $entity = $this->entityFactory->make($entityData);
                $data['entity_id'] = $entity->id;
            }
            unset($data['entity']);
        }
        return array_merge($required, $data);
    }

    /**
     * Make a resource instance.
     *
     * @param array  $data
     */
    public function make(array $data = [])
    {
        return $this->app->make(UserRepository::class)->create($this->data($data));
    }
}
