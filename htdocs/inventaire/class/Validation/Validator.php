<?php

/*
 * Copyright (C) 2017-2018  <dev2a> contact@dev2a.pro
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Validation;

use Illuminate\Contracts\Container\Container;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Validation\DatabasePresenceVerifier;
use Illuminate\Validation\Factory;

class Validator
{
    /**
     * @var \Illuminate\Validation\Factory
     */
    protected $factory;

    /**
     * The current globally used instance.
     *
     * @var object
     */
    protected static $instance;

    /**
     * Register the validator.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     * @SuppressWarnings(PHPMD)
     */
    public function __construct($db, $langs)
    {
        $this->factory = new Factory(new Translator($langs), $container);
        $this->setConnection($db);
    }

    /**
     * Set the database instance used by the presence verifier.
     *
     * @param  \Illuminate\Database\ConnectionResolverInterface $db
     * @return void
     * @SuppressWarnings(PHPMD)
     */
    public function setConnection(ConnectionResolverInterface $db)
    {
        $this->factory->setPresenceVerifier(new DatabasePresenceVerifier($db));
    }

    /**
     * Create a class alias.
     *
     * @param  string $alias
     * @return void
     */
    public function classAlias($alias = 'Validator')
    {
        class_alias(get_class($this), $alias);
    }

    /**
     * Get the validation factory instance.
     *
     * @return \Illuminate\Contracts\Validation\Factory
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * Make this instance available globally.
     *
     * @return void
     */
    public function setAsGlobal()
    {
        static::$instance = $this;
    }

    /**
     * Call validator methods dynamically.
     *
     * @param  string $method
     * @param  array  $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array([$this->factory, $method], $arguments);
    }

    /**
     * Call static validator methods dynamically.
     *
     * @param  string $method
     * @param  array  $arguments
     * @return mixed
     */
    public static function __callStatic($method, $arguments)
    {
        $factory = static::$instance->getFactory();

        return call_user_func_array([$factory, $method], $arguments);
    }
}
