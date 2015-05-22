<?php

namespace DC\Bundler;

class Node {

    private function __construct() { }

    const Parts = "parts";
    const Transform = "transform";
    const WebRoot = "__webroot";
    const Watch = "watch";
}