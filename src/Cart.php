<?php
    class Cart
    {
        private $order_date;
        private $order_cost;
        private $autoship;
        private $confirmation;
        private $id;

        function __construct($order_date, $order_cost = 0, $autoship = 0, $id = null)
        {
            $this->order_date = $order_date;
            $this->order_cost = $order_cost;
            $this->autoship = $autoship;
            $this->id = $id;
            $this->confirmation = 0;
        }

        function getOrderDate()
        {
            return $this->order_date;
        }

        function setOrderDate($new_order_date)
        {
            $this->order_date = $new_order_date;
        }

        function getOrderCost()
        {
            return $this->order_cost;
        }

        function setOrderCost($new_order_cost)
        {
            $this->order_cost = $new_order_cost;
        }

        function getAutoship()
        {
            return $this->autoship;
        }

        function setAutoship($new_autoship)
        {
            $this->autoship = $new_autoship;
        }

        function getID()
        {
            return $this->id;
        }

        function save()
        {
            $executed = $GLOBALS['DB']->exec("INSERT INTO carts (order_date, order_cost, autoship) VALUES ('{$this->getOrderDate()}', {$this->getOrderCost()}, {$this->getAutoship()});");
            if ($executed) {
                $this->id = $GLOBALS['DB']->lastInsertId();
                return true;
            } else {
                return false;
            }
        }

        static function deleteAll()
        {
            $executed = $GLOBALS['DB']->exec("DELETE FROM carts;");
            if ($executed) {
                return true;
            } else {
                return false;
            }
        }

        static function getAll()
        {
            $carts = array();
            $returned_carts = $GLOBALS['DB']->query("SELECT * FROM carts;");
            foreach ($returned_carts as $cart) {
                $order_date = $cart['order_date'];
                $order_cost = $cart['order_cost'];
                $autoship = $cart['autoship'];
                $id = $cart['id'];
                $new_cart = new Cart($order_date, $order_cost, $autoship, $id);
                array_push($carts, $new_cart);
            }
            return $carts;
        }

        function delete()
        {
            $executed = $GLOBALS['DB']->exec("DELETE FROM carts WHERE id = {$this->getID()};");
            if ($executed) {
                return true;
            } else {
                return false;
            }
        }

        function updateAutoship($new_autoship)
        {
            $executed = $GLOBALS['DB']->exec("UPDATE carts SET autoship = {$new_autoship} WHERE id = {$this->getID()};");
            if ($executed) {
                $this->setAutoship($new_autoship);
                return true;
            } else {
                return false;
            }
        }

        function updateOrderDate($new_order_date)
        {
            $executed = $GLOBALS['DB']->exec("UPDATE carts SET order_date = {$new_order_date} WHERE id = {$this->getID()};");
            if ($executed) {
                $this->setOrderDate($new_order_date);
                return true;
            } else {
                return false;
            }
        }

        function calculateOrderCost()
        {
            $new_order_cost = number_format(0.00, 2);
            $cart_products = $this->getProducts();
            foreach($cart_products as $product) {
                $new_order_cost += $product->getPrice();
            }
            return $new_order_cost;
        }

        function updateOrderCost($new_order_cost)
        {
            $new_order_cost = $this->calculateOrderCost();
            $executed = $GLOBALS['DB']->exec("UPDATE carts SET order_cost = {$new_order_cost} WHERE id = {$this->getID()};");
            if ($executed) {
                $this->setOrderCost($new_order_cost);
                return true;
            } else {
                return false;
            }
        }

        static function findByID($search_id)
        {
            $found_cart = null;
            $returned_carts = $GLOBALS['DB']->prepare("SELECT * FROM carts WHERE id = :id;");
            $returned_carts->bindPARAM(':id', $search_id, PDO::PARAM_STR);
            $returned_carts->execute();

            foreach ($returned_carts as $cart) {
                $order_date = $cart['order_date'];
                $order_cost = $cart['order_cost'];
                $autoship = $cart['autoship'];
                $id = $cart['id'];
                if ($id == $search_id) {
                    $found_cart = new Cart($order_date, $order_cost, $autoship, $id);
                }
            }
            return $found_cart;
        }

        static function findByOrderDate($search_order_date)
        {
            $found_cart = null;
            $returned_carts = $GLOBALS['DB']->prepare("SELECT * FROM carts WHERE order_date = :order_date;");
            $returned_carts->bindPARAM(':order_date', $search_order_date, PDO::PARAM_STR);
            $returned_carts->execute();

            foreach ($returned_carts as $cart) {
                $order_date = $cart['order_date'];
                $order_cost = $cart['order_cost'];
                $autoship = $cart['autoship'];
                $id = $cart['id'];
                if ($order_date == $search_order_date) {
                    $found_cart = new Cart($order_date, $order_cost, $autoship, $id);
                }
            }
            return $found_cart;
        }

        function addProduct($product)
        {
            $executed = $GLOBALS['DB']->exec("INSERT INTO carts_products (cart_id, product_id) VALUES ({$this->getID()}, {$product->getID()});");
            if ($executed) {
                return true;
            } else {
                return false;
            }
        }

        function getProducts()
        {
            $returned_products = $GLOBALS['DB']->query("SELECT products.* FROM carts
            JOIN carts_products ON (carts_products.cart_id = carts.id)
            JOIN products ON (products.id = carts_products.product_id)
            WHERE carts.id = {$this->getID()};");
            // var_dump($returned_products);
            $products = array();
            // var_dump($products);
            foreach ($returned_products as $product) {
                $name = $product['name'];
                $price = $product['price'];
                $id = $product['id'];
                $new_product = new Product($name, $price, $id);
                array_push($products, $new_product);
            }
            return $products;
        }

        function confirmOrder()
        {
            $this->confirmation = 1;
        }

        function getConfirmation()
        {
            return $this->confirmation;
        }

        function setConfirmation($confirm)
        {
            $this->confirmation = $confirm;
        }
    }
?>
