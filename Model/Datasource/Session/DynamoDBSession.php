<?php
/**
 * DynamoDB Session
 *
 * This is a simple wrapper implementing the CakePHP session handling
 * interface. It deviates from the provided session handler only where
 * necessary to provide compatiblity with the CakePHP framework.
 *
 * Copyright (c) Stephen Cuppett (http://stephencuppett.com)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Stephen Cuppett (http://stephencuppett.com)
 * @package       Model.Datasource.Session
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
App::uses('CakeSessionHandlerInterface', 'Model/Datasource/Session');

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Session\SessionHandler;

class DynamoDBSession implements CakeSessionHandlerInterface
{

    const DEFAULT_TABLE_NAME = 'sessions';

    const DEFAULT_SESSION_NAME = 'awsutils';

    private $_sessionName = DynamoDBSession::DEFAULT_SESSION_NAME;

    private $_sessionHandler;

    public function __construct()
    {
        $aws = Configure::read('Session.handler.aws');
        $dynamoDB = $aws->get('dynamodb');

        $config = array(
            'dynamodb_client' => $dynamoDB,
            'session_lifetime' => Configure::read('Session.timeout') * 60,
            'table_name' => DynamoDBSession::DEFAULT_TABLE_NAME,
            'locking_strategy' => 'pessimistic'
        );

        if (Configure::check('Session.handler.table_name'))
            $config['table_name'] = Configure::read('Session.handler.table_name');

        if (Configure::check('Session.handler.locking_strategy'))
            $config['locking_strategy'] = Configure::read('Session.handler.locking_strategy');

        $this->_sessionHandler = SessionHandler::factory($config);

        if (Configure::check('Session.handler.session_name'))
            $this->_sessionName = Configure::read('Session.handler.session_name');
    }

    /**
     *
     * @return SessionHandler The inner DynamoDB session handler
     *         object
     */
    public function getSessionHandler()
    {
        return $this->_sessionHandler;
    }

    /**
     * (non-PHPdoc)
     *
     * @see CakeSessionHandlerInterface::open()
     */
    public function open()
    {
        $this->_sessionHandler->open('/', $this->_sessionName);
        return true;
    }

    /**
     * (non-PHPdoc)
     *
     * @see CakeSessionHandlerInterface::close()
     */
    public function close()
    {
        return $this->_sessionHandler->close();
    }

    /**
     * (non-PHPdoc)
     *
     * @see CakeSessionHandlerInterface::read()
     */
    public function read($id)
    {
        $contents = $this->_sessionHandler->read($id);
        if ($contents == '') {
            return false;
        }
        return $contents;
    }

    /**
     * (non-PHPdoc)
     *
     * @see CakeSessionHandlerInterface::write()
     */
    public function write($id, $data)
    {
        return $this->_sessionHandler->write($id, $data);
    }

    /**
     * (non-PHPdoc)
     *
     * @see CakeSessionHandlerInterface::destroy()
     */
    public function destroy($id)
    {
        return $this->_sessionHandler->destroy($id);
    }

    /**
     * (non-PHPdoc)
     *
     * @see CakeSessionHandlerInterface::gc()
     */
    public function gc($expires = null)
    {
        return $this->_sessionHandler->gc($expires);
    }
}
