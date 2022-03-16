<?php
/**
 * Classes.php
 *
 * This file is part of InitPHP.
 *
 * @author     Muhammet ŞAFAK <info@muhammetsafak.com.tr>
 * @copyright  Copyright © 2022 InitPHP
 * @license    http://initphp.github.io/license.txt  MIT
 * @version    1.0
 * @link       https://www.muhammetsafak.com.tr
 */

declare(strict_types=1);

namespace InitPHP\Config;

use \InitPHP\Config\Interfaces\ConfigInterface;
use \InitPHP\ParameterBag\ParameterBag;

abstract class Classes implements ConfigInterface
{

    protected ParameterBag $_ParameterBag;

    public function __construct()
    {
        $data = get_class_vars(get_called_class());
        $this->_ParameterBag = new ParameterBag($data, [
            'isMulti'   => true,
            'separator' => '.'
        ]);
    }

    public function __destruct()
    {
        if(isset($this->_ParameterBag)){
            $this->_ParameterBag->close();
            unset($this->_ParameterBag);
        }
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, $value): self
    {
        $this->_ParameterBag->set($key, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function get(string $key, $default = null)
    {
        return $this->_ParameterBag->get($key, $default);
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        return $this->_ParameterBag->has($key);
    }

    /**
     * @inheritDoc
     */
    public function remove(string $key): self
    {
        $this->_ParameterBag->remove($key);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function all(): array
    {
        return $this->_ParameterBag->all();
    }

}
