<?php

namespace Trax\Auth\Test\Utils;

use Trax\XapiStore\Stores\Statements\StatementRepository;

class StatementFactory extends Factory
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
        // Generate only the statement, and not the full DB record!
        if (isset($model)) {
            $required = [
                'actor' => $model->actor,
                'verb' => $model->verb,
                'object' => $model->object,
            ];
        } else {
            $required = [
                'actor' => [
                    'mbox' => 'mailto:' . $this->faker->email,
                ],
                'verb' => [
                    'id' => $this->faker->url,
                ],
                'object' => [
                    'id' => $this->faker->url,
                ],
            ];
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
        // This time, data is not the Statement data, but the record data.
        $data = array_merge(['data' => $this->data()], $data);

        // Entity.
        if (isset($data['entity'])) {
            $entityData = $data['entity'];
            $entity = $this->entityFactory->make($entityData);
            $data['entity_id'] = $entity->id;
        }
        unset($data['entity']);

        // Owner.
        if (isset($data['owner'])) {
            $ownerData = $data['owner'];
            $owner = $this->ownerFactory->make($ownerData);
            $data['owner_id'] = $owner->id;
        }
        unset($data['owner']);

        return $this->app->make(StatementRepository::class)->create($data);
    }
}
