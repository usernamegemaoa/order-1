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

namespace Module\Order\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

class DetailController extends IndexController
{
    public function indexAction()
    {
        // Check user
        $this->checkUser();
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Get order
        $id = $this->params('id');
        $order = Pi::api('order', 'order')->getOrder($id);
        // Check order
        if (empty($order)) {
            $this->jump(array('', 'controller' => 'index', 'action' => 'index'), __('The order not found.'));
        }
        // Check order is for this user
        if ($order['uid'] != Pi::user()->getId()) {
            $this->jump(array('', 'controller' => 'index', 'action' => 'index'), __('This is not your order.'));
        }
        // Check order is for this user
        if (!in_array($order['status_order'], array(1, 2, 3, 7))) {
            $this->jump(array('', 'controller' => 'index', 'action' => 'index'), __('This order not active.'));
        }
        // set Products
        $order['products'] = Pi::api('order', 'order')->listProduct($order['id'], $order['module_name']);
        // set Products
        $order['invoices'] = Pi::api('invoice', 'order')->getInvoiceFromOrder($order);
        // set delivery information
        $order['deliveryInformation'] = '';
        if ($order['delivery'] > 0 && $order['location'] > 0) {
            $order['deliveryInformation'] = Pi::api('delivery', 'order')->getDeliveryInformation($order['location'], $order['delivery']);
        }
        // Get credit
        if ($config['credit_active'] && $order['uid'] > 0) {
            $credit = Pi::api('credit', 'order')->getCredit($order['uid']);
            $this->view()->assign('credit', $credit);
        }
        // set view
        $this->view()->setTemplate('detail');
        $this->view()->assign('order', $order);
        $this->view()->assign('config', $config);
    }
}