<?php

namespace UmigameTech\Catapult\Datatypes;

use ArrayAccess;
use IteratorAggregate;
use InvalidArgumentException;
use ArrayIterator;
use Countable;

class DataList implements IteratorAggregate, Countable, ArrayAccess
{
    private string $type;
    private array $items = [];

    public function __construct(string $type, array $items = [])
    {
        $this->type = $type;
        foreach ($items as $item) {
            if ($item instanceof $this->type === false) {
                try {
                    $item = new $this->type($item);
                } catch (InvalidArgumentException $e) {
                    throw new InvalidArgumentException("Item must be of type {$this->type}");
                }
            }

            $this->items[] = $item;
        }
    }

    public function add($item)
    {
        if ($item instanceof $this->type === false) {
            try {
                $item = new $this->type($item);
            } catch (InvalidArgumentException $e) {
                throw new InvalidArgumentException("Item must be of type {$this->type}");
            }
        }

        $this->items[] = $item;
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return $this->items[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($value instanceof $this->type === false) {
            try {
                $value = new $this->type($value);
            } catch (InvalidArgumentException $e) {
                throw new InvalidArgumentException("Item must be of type {$this->type}");
            }
        }

        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }

    public function filter(callable $callback): DataList
    {
        return new DataList($this->type, array_filter($this->items, $callback));
    }

    public function map(callable $callback): array
    {
        return array_map($callback, $this->items);
    }

    public function mapWithType(string $type, callable $callback): DataList
    {
        return new DataList($type, array_map($callback, $this->items));
    }

    public function mapWithSameType(callable $callback): DataList
    {
        return $this->mapWithType($this->type, $callback);
    }
}
