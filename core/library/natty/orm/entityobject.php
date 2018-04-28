<?php

namespace Natty\ORM;

class EntityObject
extends \Natty\StdClass {
    
    /**
     * Entity handler associated with this entity
     * @var EntityHandler
     */
    protected $handler;
    
    /**
     * Creates an Entity with the given state variables
     * @param array $data [optional] Entity state data
     */
    public function __construct( array $data = array () ) {
        if ( 0 !== sizeof($data) )
            $this->setState($data);
    }
    
    public function getHandler() {
        return $this->handler;
    }
    
    /**
     * Returns the unique identifier for the Entity
     * @return int|string
     */
    public function getId() {
        $id_key = $this->handler->getKey('id');
        return isset ($this->$id_key)
            ? $this->$id_key : FALSE;
    }
    
    /**
     * Returns the unique identifier for the Entity
     * @return int|string
     */
    public function getCode() {
        return $this->handler->getEntityCode('code');
    }
    
    /**
     * Returns a label for the Entity
     * @return string
     */
    public function getLabel() {
        return $this->handler->getEntityLabel($this);
    }
    
    /**
     * Returns a URI for viewing the entity
     * @return string
     */
    public function getUri() {
        return $this->handler->getUri($this);
    }
    
    public function getState() {
        $state = parent::getState();
        unset ($state['handler']);
        return $state;
    }
    
    public function getStorageDirname() {
        return $this->handler->getStorageDirname($this);
    }
    
    public function save() {
        $this->handler->save($this);
    }
    
    public function delete() {
        $this->handler->delete($this);
    }
    
    /**
     * Reads a fresh copy of the entity from the database as compared to the
     * ones stored in the static cache. This can be used to compare stored and
     * unstored entity states.
     * @param array $options
     * @return EntityObject Unchanged entity object.
     */
    public function readUnchanged(array $options = array ()) {
        
        if ( !isset ($options['nocache']) && isset ($this->unchangedEntity) )
            return $this->unchangedEntity;
        
        $options['nocache'] = TRUE;
        $this->unchangedEntity = $this->handler->readById($this->getId(), $options);
        
        return $this->unchangedEntity;
        
    }
    
    public function render(array $options = array ()) {
        return $this->handler->render($this, $options);
    }
    
    /**
     * Calls the relevant handler method on the entity. The first argument
     * passed to the handler method would be the entity object.
     * @param string $method
     * @param mixed $options One or more arguments to be passed to the handler
     * method. Refer to handler documentation for argument formats.
     * @return mixed
     */
    public function call($method, $options = NULL) {
        $arguments = func_get_args();
        $arguments[0] =& $this;
        return call_user_func_array(array ($this->handler, $method), $arguments);
    }
    
}