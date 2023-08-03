<?php

namespace UmigameTech\Catapult\Datatypes;
use Newnakashima\TypedArray\TypedArray;

class Project
{
    public string $projectName = '';
    public TypedArray $entities;

    public function __construct($data) {
        if (empty($data['project_name'])) {
            throw new \Exception('Project name is required');
        }

        $this->projectName = $data['project_name'];
        $this->entities = new TypedArray(Entity::class, $data['entities'] ?? []);

        $this->prepareBelongsTo();
    }

    private function prepareBelongsTo()
    {
        $this->entities->each(function (Entity $entity) {
            $entity->belongsTo->each(function (string $belongsTo) use ($entity) {
                /** @var Entity */
                $havingEntity = $this->entities->find(fn ($e) => $e->name === $belongsTo);
                if (empty($havingEntity)) {
                    throw new \Exception("Entity {$entity->name} has a belongsTo relationship with {$belongsTo} but {$belongsTo} does not exist");
                }

                $entity->attributes->push(new Attribute([
                    'name' => $havingEntity->foreignIdName(),
                    'type' => AttributeType::ForeignId->value,
                    'nullable' => true,
                    'default' => null,
                ]));

                $havingEntity->hasManyEntities->push($entity);
            });

            $entity->belongsToEntities = new TypedArray(
                Entity::class,
                $entity->belongsTo->map(function (string $belongsTo) {
                    return $this->entities->find(fn ($e) => $e->name === $belongsTo);
                })
            );
        });
    }
}
