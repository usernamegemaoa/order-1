<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */

namespace Module\Order\Route;

use Pi\Mvc\Router\Http\Standard;

class Order extends Standard
{
    /**
     * Default values.
     * @var array
     */
    protected $defaults = array(
        'module' => 'order',
        'controller' => 'index',
        'action' => 'index'
    );

    protected $controllerList = array(
        'checkout', 'credit', 'detail', 'index', 'invoice', 'payment'
    );

    /**
     * {@inheritDoc}
     */
    protected $structureDelimiter = '/';

    /**
     * {@inheritDoc}
     */
    protected function parse($path)
    {
        $matches = array();
        $parts = array_filter(explode($this->structureDelimiter, $path));

        // Set controller
        $matches = array_merge($this->defaults, $matches);
        if (isset($parts[0]) && in_array($parts[0], $this->controllerList)) {
            $matches['controller'] = $this->decode($parts[0]);
        } elseif (isset($parts[0]) && in_array($parts[0], array('remove', 'error'))) {
            $matches['controller'] = 'index';
        } else {
            return false;
        }

        // Make Match
        if (isset($matches['controller'])) {
            switch ($matches['controller']) {
                case 'checkout':
                    if (isset($parts[1]) && $parts[1] == 'level') {
                        $matches['action'] = 'level';
                        $matches['process'] = $this->decode($parts[2]);
                        $matches['id'] = $this->decode($parts[3]);
                    } elseif (isset($parts[1]) && $parts[1] == 'installment') {
                        $matches['action'] = 'installment';
                    } elseif (isset($parts[1])) {
                        $matches['customer'] = $parts[1];
                    }  
                    break;

                case 'detail':
                    $matches['id'] = intval($parts[1]);
                    break;

                case 'index':
                    if (isset($parts[0]) && !empty($parts[0])) {
                        switch ($parts[0]) {
                            case 'index':
                                $matches['action'] = 'index';
                                break;

                            case 'error':
                                $matches['action'] = 'error';
                                break;

                            case 'remove':
                                $matches['action'] = 'remove';
                                $matches['id'] = intval($this->decode($parts[1]));
                                break;
                        }
                    }
                    break;

                case 'invoice':
                    if ($parts[1] == 'print') {
                        $matches['action'] = 'print';
                        $matches['id'] = intval($this->decode($parts[2]));
                    } else {
                        $matches['action'] = 'index';
                        $matches['id'] = intval($this->decode($parts[1]));
                    }
                    break;

                case 'payment':
                    $actionList = array('result', 'notify', 'cancel', 'finish', 'index', 'test', 'process');
                    if (in_array($parts[1], $actionList)) {
                        $matches['action'] = $this->decode($parts[1]);
                        if (isset($parts[2]) && is_numeric($parts[2])) {
                            $matches['id'] = intval($parts[2]);
                        }
                    } elseif (is_numeric($parts[1])) {
                        $matches['action'] = 'index';
                        $matches['id'] = intval($parts[1]);
                        if (isset($parts[2]) && $parts[2] == 'credit') {
                            $matches['credit'] = intval($parts[3]);
                        }
                    }
                    break;

                case 'credit':
                    $matches['action'] = 'index';
                    break;
            }
        }

        //print_r($parts);
        //print_r($matches);

        return $matches;
    }

    /**
     * assemble(): Defined by Route interface.
     *
     * @see    Route::assemble()
     * @param  array $params
     * @param  array $options
     * @return string
     */
    public function assemble(
        array $params = array(),
        array $options = array()
    )
    {
        $mergedParams = array_merge($this->defaults, $params);
        if (!$mergedParams) {
            return $this->prefix;
        }

        // Set module
        if (!empty($mergedParams['module'])) {
            $url['module'] = $mergedParams['module'];
        }

        // Set controller
        if (!empty($mergedParams['controller'])
            && $mergedParams['controller'] != 'index'
            && in_array($mergedParams['controller'], $this->controllerList)
        ) {
            $url['controller'] = $mergedParams['controller'];
        }

        // Set action
        if (!empty($mergedParams['action'])
            && $mergedParams['action'] != 'index'
        ) {
            $url['action'] = $mergedParams['action'];
        }

        // Set id
        if (!empty($mergedParams['process'])) {
            $url['process'] = $mergedParams['process'];
        }

        // Set id
        if (isset($mergedParams['id'])) {
            $url['id'] = $mergedParams['id'];
        }
        
        if (isset($mergedParams['customer'])) {
            $url['customer'] = $mergedParams['customer'];
        }

        // Set credit
        if (isset($mergedParams['credit'])) {
            $url['credit'] = 'credit' . $this->paramDelimiter . $mergedParams['credit'];
        }

        // Make url
        $url = implode($this->paramDelimiter, $url);

        if (empty($url)) {
            return $this->prefix;
        }

        $finalUrl = rtrim($this->paramDelimiter . $url, '/');

        return $finalUrl;
    }
}
