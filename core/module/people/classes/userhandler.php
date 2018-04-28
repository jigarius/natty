<?php

namespace Module\People\Classes;

class UserHandler
extends \Natty\ORM\BasicDatabaseEntityHandler {
    
    /**
     * Authentication successful
     */
    const AUTH_ERR_OK = 0;
    
    /**
     * No user could be found with the specified identifiers.
     */
    const AUTH_ERR_NOTFOUND = 1;
    
    /**
     * The user's password did not match with our records.
     */
    const AUTH_ERR_PASSWORD = 2;
    
    /**
     * The user's account was blocked / disabled by the system.
     */
    const AUTH_ERR_DISABLED = 3;
    
    /**
     * The user's account was blocked / disabled by the system.
     */
    const AUTH_ERR_PENDING = 4;
    
    /**
     * Cannot login due to multiple failed login attempts
     */
    const AUTH_ERR_MULTIFAIL = 5;
    
    public function __construct( array $options = array () ) {
        parent::__construct(array (
            'etid' => 'people--user',
            'tableName' => '%__people_user',
            'entityObjectClass' => '\\Module\\People\\Classes\\UserObject',
            'keys' => array (
                'id' => 'uid',
                'label' => 'name'
            ),
            'modelName' => array ('user', 'users'),
            'properties' => array (
                'uid' => array (),
                'name' => array ('nullable' => TRUE),
                'email' => array ('nullable' => TRUE),
                'alias' => array ('nullable' => TRUE),
                'hash' => array ('nullable' => TRUE),
                'tzid' => array ('nullable' => TRUE),
                'idLanguage' => array ('nullable' => TRUE),
                'idCurrency' => array ('nullable' => TRUE),
                'gtp' => array ('nullable' => TRUE),
                'dtCreated' => array ('default' => NULL),
                'dtModified' => array ('default' => NULL),
                'dtAccessed' => array ('default' => NULL),
                'dtPasswordChanged' => array ('default' => NULL),
                'status' => array ('default' => 1)
            )
        ));
    }
    
    public function create(array $data = array()) {
        
        if ( !isset ($data['dtCreated']) )
            $data['dtCreated'] = date('Y-m-d H:i:s');
        
        return parent::create($data);
        
    }
    
    public function buildBackendLinks(&$entity, array $options = array()) {
        
        $user = \Natty::getUser();
        $output = array ();
        
        if ( $user->can('people--manage user entities') ):
            $output['edit'] = '<a href="' . \Natty::url('backend/people/user/' . $entity->uid) . '">Edit</a>';
        endif;
        
        $output += parent::buildBackendLinks($entity, $options);
        return $output;
        
    }
    
    /**
     * Tells whether a particular action can be performed by a particular user.
     * @param string $aid The action to test for
     * @param int $uid [optional] ID of the user to conduct the test for;
     * defaults to the signed in user
     * @param boolean $throw_exception
     * @return boolean True for allowed and False for denied
     * @throws \InvalidArgumentException
     */
    public static function canUser($aid = NULL, $uid = NULL, $throw_exception = FALSE) {
        
        static $cache;
        if ( !is_array($cache) )
            $cache = array ();
        
        // If no user is specified, take the auth user
        if ( is_null($uid) )
            $uid = \Natty::getUser()->uid;
        
        // Always return true for the "root" user with uid 1
        if ( 1 == $uid )
            return TRUE;
        
        // Not cached? Read it!
        if ( !isset ($cache[$uid]) ):

            // Retrieve user permissions based on his roles
            $cache[$uid] = \Natty::getDbo()
                    ->getQuery('SELECT', '%__people_role_permission rp')
                    ->addColumns(array ('aid'), 'rp')
                    ->addColumn(':rprule rule')
                    ->addJoin('INNER', '%__people_user_role_map urm', "{urm}.{rid} = {rp}.{rid}")
                    ->addComplexCondition("{urm}.{uid} = :uid")
                    ->execute(array (
                        'uid' => $uid,
                        'rprule' => 1,
                    ))
                    ->fetchAll(\PDO::FETCH_KEY_PAIR);
            
        endif;
        
        // If action is not in marked as allowed, assume it denied
        if ( !isset ($cache[$uid][$aid]) )
            $cache[$uid][$aid] = FALSE;
        
        // Raise exception, if required
        if ( !$cache[$uid][$aid] && $throw_exception )
            throw new \Natty\Core\ControllerException(403);
        
        return $cache[$uid][$aid];
        
    }
    
    public function onBeforeSave(&$entity, array $options = array ()) {
        
        // Password update request?
        if ( isset ($entity->password) && !empty ($entity->password) ):
            
            // Generate password hash
            $entity->hash = md5($entity->password);
            
            // Generate guess the password - gtp
            $gtp = str_split($entity->password);
            foreach ( $gtp as $index => &$char ):
                $char = ( $index == (sizeof($gtp)-2) )
                    ? $char : '*';
            endforeach;
            $entity->gtp = implode('', $gtp);
            
            // Update password change time
            $entity->dtPasswordChanged = date('Y-m-d H:i:s');
            
        endif;
        
        // Add modification time
        if ( !$entity->isNew )
            $entity->dtModified = date('Y-m-d H:i:s');
        
        parent::onBeforeSave($entity, $options);
        
    }
    
    /**
     * Returns the ID of the authenticated user or 0 if no user is signed in.
     * @return integer
     */
    public static function getAuthUserId() {
        return isset($_SESSION['people.auth.uid']) && $_SESSION['people.auth.uid']
            ? $_SESSION['people.auth.uid'] : 0;
    }
    
    /**
     * Sets the user with the given ID as signed in.
     * @param integer $uid User ID.
     */
    public static function setAuthUserId($uid) {
        if ( $uid > 0 ) {
            $_SESSION['people.auth.uid'] = $uid;
            $_SESSION['people.auth.time'] = time();
        }
        else {
            self::unauthenticate();
        }
    }
    
    /**
     * Attempt to authenticate a user and man the Session accordingly
     * @param array $options Arguments for authentication
     * @return boolean|varchar Boolean true on successful authentication.
     * One of self::AUTH_ERR_* constants on failure.
     */
    public static function authenticate(array $options) {
        
        $incident_handler = self::getInstance('system--incident');
        $user_handler = self::getInstance('people--user');
        $token_handler = self::getInstance('people--token');
        
        // Alias lookup
        $lookup_key = array ();
        if ( isset ($options['alias']) ) {
            $lookup_key['alias'] = $options['alias'];
        }
        // Email lookup
        elseif ( isset ($options['email']) ) {
            $lookup_key['email'] = $options['email'];
        }
        else {
            throw new \InvalidArgumentException('Missing required index for user identifiers.');
        }
        
        // Load user data
        $user = $user_handler->read(array ('key' => $lookup_key, 'unique' => TRUE));
        if ( !$user )
            return self::AUTH_ERR_NOTFOUND;
        
        // Look for failed login attempts
        $incidents = $incident_handler->read(array (
            'key' => array (
                'idCreator' => $user->uid,
                'type' => 'people--auth failure',
            ),
            'conditions' => array (
                array ('AND', '{tsExpired} > ' . time()),
            ),
            'ordering' => array ('tsCreated' => 'desc'),
            'limit' => 5,
        ));
        
        // After 5 failed attempts, do nothing
        if ( sizeof($incidents) >= 5 ):
            return self::AUTH_ERR_MULTIFAIL;
        endif;
        
        // Verify account status
        if ( !$user->status )
            return self::AUTH_ERR_DISABLED;
        
        // Verify email validation
        $token = $token_handler->read(array (
            'key' => array (
                'uid' => $user->uid,
                'purpose' => 'email validation',
            ),
            'unique' => 1,
        ));
        if ( $token )
            return self::AUTH_ERR_PENDING;
        
        // Verify password
        if ( !isset ($options['force']) && $user->hash != md5($options['password']) ):
            
            // Record the incident
            $latest_incident = $incident_handler->createAndSave(array (
                'idCreator' => $user->uid,
                'type' => 'people--auth failure',
                'description' => 'Failed login attempt.',
                'tsExpired' => strtotime('+ 6 hours'),
            ));
            $incidents[$latest_incident->iid] = $latest_incident;
            
            return ( sizeof($incidents) >= 5 )
                ? self::AUTH_ERR_MULTIFAIL : self::AUTH_ERR_PASSWORD;
        
        endif;
        
        // Forget old incidents
        foreach ( $incidents as $incident ):
            $incident->delete();
        endforeach;
        
        // Update last access time of the user
        $user->dtAccessed = date('Y-m-d H:i:s');
        $user_handler->getDbo()->update($user_handler->tableName, array (
            'uid' => $user->uid,
            'dtAccessed' => $user->dtAccessed,
        ), array (
            'key' => array (
                'uid' => $user->uid,
            ),
        ));
        
        // Mark the session as manned
        self::setAuthUserId($user->uid);
        return self::AUTH_ERR_OK;
        
    }
    
    public static function unauthenticate() {
        
        unset ($_SESSION['people.auth.uid']);
        unset ($_SESSION['people.auth.time']);
        
        // Unset all session data
        foreach ( $_SESSION as $key => $value ):
            if ( 0 === strpos($key, 'user.') )
                unset ($_SESSION[$key]);
        endforeach;
        
    }
    
}