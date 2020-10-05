<?php

namespace Trax\Auth\Test\Utils;

use Trax\Auth\Stores\Clients\ClientRepository;

class ClientFactory extends Factory
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
                'name' => $model->name,
                'owner_id' => $model->owner_id,
            ];
        } else {
            $required = [
                'name' => $this->faker->name,
            ];

            // Owner.
            if (isset($data['owner'])) {
                $ownerData = $data['owner'];
                $owner = $this->ownerFactory->make($ownerData);
                $data['owner_id'] = $owner->id;
            }
            unset($data['owner']);

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
        return $this->app->make(ClientRepository::class)->create($this->data($data));
    }
}
