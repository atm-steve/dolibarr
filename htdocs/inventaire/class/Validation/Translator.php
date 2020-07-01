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

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use \Illuminate\Contracts\Translation\Translator as TranslatorInterface;

class Translator implements TranslatorInterface
{
    /**
     * The language lines used by the translator.
     *
     * @var \Translate
     */
    protected $langs;



    /**
     * Create a new translator instance.
     *
     * @param array $lines
     */
    public function __construct($langs)
    {
        $this->langs = $langs;
        $this->langs->load('validation@inventaire');
    }

    /**
     * Get the translation for a given key.
     *
     * @param  string $id
     * @param  array  $parameters
     * @param  string $domain
     * @param  string $locale
     * @return string
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function trans($id, array $parameters = [], $domain = 'messages', $locale = null)
    {
      return $this->langs->trans($id);
    }

    /**
     * @inheritDoc
     */
    public function transChoice($id, $number, array $parameters = [], $domain = 'messages', $locale = null)
    {

    }

    /**
     * @inheritDoc
     */
    public function setLocale($locale)
    {

    }

    /**
     * @inheritDoc
     */
    public function getLocale()
    {

    }
}
