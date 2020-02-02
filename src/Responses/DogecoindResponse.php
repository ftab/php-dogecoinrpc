<?php

declare(strict_types=1);

namespace ftab\Dogecoin\Responses;

use ftab\Dogecoin\Traits\Collection;
use ftab\Dogecoin\Traits\ImmutableArray;
use ftab\Dogecoin\Traits\SerializableContainer;

class DogecoindResponse extends Response implements
    \ArrayAccess,
    \Countable,
    \Serializable,
    \JsonSerializable
{
    use Collection, ImmutableArray, SerializableContainer;

    /**
     * Gets array representation of response object.
     *
     * @return array
     */
    public function toArray() : array
    {
        return (array) $this->result();
    }

    /**
     * Gets root container of response object.
     *
     * @return array
     */
    public function toContainer() : array
    {
        return $this->container;
    }
}
