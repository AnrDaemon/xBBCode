<?php

/******************************************************************************
 *                                                                            *
 *   Copyright (C) 2006-2007  Dmitriy Skorobogatov  dima@pc.uz                *
 *                                                                            *
 *   This program is free software; you can redistribute it and/or modify     *
 *   it under the terms of the GNU General Public License as published by     *
 *   the Free Software Foundation; either version 2 of the License, or        *
 *   (at your option) any later version.                                      *
 *                                                                            *
 *   This program is distributed in the hope that it will be useful,          *
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of           *
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            *
 *   GNU General Public License for more details.                             *
 *                                                                            *
 *   You should have received a copy of the GNU General Public License        *
 *   along with this program; if not, write to the Free Software              *
 *   Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA *
 *                                                                            *
 ******************************************************************************/

namespace Xbbcode\Tag;

use Xbbcode\Attributes;
use Xbbcode\Xbbcode;

/**
 * Interface Tag
 */
abstract class Tag extends Xbbcode
{
    /**
     * @return Attributes Tag attributes
     */
    abstract protected function getAttributes();
    /**
     * @return string HTML code
     */
    abstract public function __toString();


    /**
     *
     */
    protected function cleanText()
    {
        foreach ($this->tree as $key => $item) {
            if ('text' === $item['type']) {
                unset($this->tree[$key]);
            }
        }
    }

    /**
     * @return string
     */
    protected function getBody()
    {
        if (in_array($this->tag, array('table', 'tr', 'ul', 'ol', 'list'))) {
            $this->cleanText();
        }

        return $this->getHtml($this->tree);
    }

    /**
     * Функция преобразует строку URL в соответствии с RFC 3986
     *
     * @param string $url
     * @return string
     */
    protected function parseUrl($url)
    {
        $parse = parse_url($url);

        $out = '';
        if (isset($parse['scheme'])) {
            $out .= $parse['scheme'] . '://';
        }
        if (isset($parse['user']) && isset($parse['pass'])) {
            $out .= rawurlencode($parse['user']) . ':' . rawurlencode($parse['pass']) . '@';
        } else if (isset($parse['user'])) {
            $out .= rawurlencode($parse['user']) . '@';
        }
        if (isset($parse['host'])) {
            $out .= rawurlencode($parse['host']);
        }
        if (isset($parse['port'])) {
            $out .= ':' . $parse['port'];
        }
        if (isset($parse['path'])) {
            $out .= str_replace('%2F', '/', rawurlencode($parse['path']));
        }
        if (isset($parse['query'])) {
            $query = $this->parseStr($parse['query']);
            //parse_str($parse['query'], $query); //replace spaces and dots

            // PHP 5.4.0 - PHP_QUERY_RFC3986
            $out .= '?' . str_replace('+', '%20', rtrim(http_build_query($query, '', '&'), '='));
        }
        if (isset($parse['fragment'])) {
            $out .= '#' . rawurlencode($parse['fragment']);
        }

        return $out;
    }


    /**
     * Аналог parse_str но без преобразования точек и пробелов в подчеркивания
     *
     * @todo не очень хорошая реализация
     * @param string $str
     * @return array
     */
    private function parseStr ($str)
    {
        $original = array('.', ' ');
        $replace = array("xbbdot\txbbdot", "xbbspace\txbbspace");

        parse_str(str_replace($original, $replace, $str), $query);

        foreach ($query as $k => $v) {
            unset($query[$k]);
            $query[str_replace($replace, $original, $k)] = str_replace($replace, $original, $v);
        }

        return $query;
    }


    /**
     * @param string $size
     * @return bool
     */
    protected function isValidSize($size)
    {
        return (bool)preg_match('/^[0-9]+(?:px|%)?$/i', $size);
    }


    /**
     * @param string $number
     * @return bool
     */
    protected function isValidNumber($number)
    {
        return (bool)preg_match('/^[0-9]+$/', $number);
    }

    /**
     * @param string $size
     * @return bool
     */
    protected function isValidFontSize($size)
    {
        return (bool)preg_match('/^(?:\+|-)?(?:1|2|3|4|5|6|7){1}$/', $size);
    }

    /**
     * @param string $align
     * @return bool
     */
    protected function isValidAlign($align)
    {
        return in_array(strtolower($align), array('left', 'right', 'center', 'justify'));
    }

    /**
     * @param string $valign
     * @return bool
     */
    protected function isValidValign($valign)
    {
        return in_array(strtolower($valign), array('top', 'middle', 'bottom', 'baseline'));
    }

    /**
     * @param string $ulType
     * @return bool
     */
    protected function isValidUlType($ulType)
    {
        return in_array(strtolower($ulType), array('disc', 'circle', 'square'));
    }

    /**
     * @param string $olType
     * @return bool
     */
    protected function isValidOlType($olType)
    {
        return (bool)preg_match('/^[a-z0-9]+$/i', $olType);
    }

    /**
     * @param string $target
     * @return bool
     */
    protected function isValidTarget($target)
    {
        return in_array(strtolower($target), array('_blank', '_self', '_parent', '_top'));
    }
}