<?php
/**
 * This is a stub file for IDE compatibility only.
 * It should not be included in your projects.
 */
namespace DecodeLabs;
use DecodeLabs\Veneer\Proxy;
use DecodeLabs\Veneer\ProxyTrait;
use DecodeLabs\Tagged\Factory as Inst;
class Tagged implements Proxy { use ProxyTrait; 
const VENEER = 'Tagged';
const VENEER_TARGET = Inst::class;
const PLUGINS = Inst::PLUGINS;
public static $parse;
public static $toText;
public static $icon;
public static $number;
public static $time;
public static $embed;};
