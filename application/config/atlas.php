<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['atlas'] = [
  'app_name'      => 'ATLAS ERP Suite',
  'app_version'   => '0.7.3.8',
  'developer'     => 'O R H T E J',
  'timezone'      => 'Asia/Manila',
  'shortcut_ico'  => 'assets/images/atlas.ico',
  'default_password' => 'p1234567890d',
  'access_levels' => [
    'ADMIN'   => 'Administrator',
    'MANAGER' => 'Manager',
    'STAFF'   => 'Staff',
    'VIEWER'  => 'Viewer'
  ],
];