<?php

namespace andy87\dnk\source\resources;

/**
 *  class EventResource
 *
 * @property int $event some <EVENT> ( RESOURCE_INIT, RESOURCE_CREATED )
 * @property int $action some <action>  ( LIST, CREATE, READ, UPDATE )
 * @property callable $func some function ( updateObject( SomeResource $r ){ $r->obj = new Obj(); } )
 */
class MutationResource
{
    // Property

    /** @var mixed|callable func for mutation object */
    public mixed $func;



    // Methods

    /**
     * Construct
     *
     * @param int $event
     * @param int $action
     * @param callable $func
     */
    public function __construct(public int $event, public int $action, callable $func)
    {
        $this->func = $func;
    }
}