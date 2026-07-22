<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['atlas_menu'] = [
  [
    'header' => 'MAIN',
  ],
  [
    'title' => 'Dashboard',
    'icon'  => 'fas fa-tachometer-alt',
    'url'   => 'dashboard'
  ],

  [
    'header' => 'MASTER DATA',
  ],
  [
    'title' => 'Masters',
    'icon'  => 'fas fa-database',

    'children' => [
      [
        'title' => 'Customers',
        'icon'  => 'fas fa-users',
        'url'   => 'customers'
      ],

      [
        'title' => 'Suppliers',
        'icon'  => 'fas fa-truck',
        'url'   => 'suppliers'
      ],

      [
        'title' => 'Products',
        'icon'  => 'fas fa-boxes',
        'url'   => 'products'
      ],

      [
        'title' => 'UOM',
        'icon'  => 'fas fa-ruler',
        'url'   => 'uom'
      ],

      [
        'title' => 'Terms',
        'icon'  => 'fas fa-file-invoice',
        'url'   => 'terms'
      ],

      [
        'title' => 'Salesmen',
        'icon'  => 'fas fa-user-tie',
        'url'   => 'salesmen'
      ],
    ]
  ],

  [
    'header' => 'TRANSACTIONS',
  ],
  [
    'title' => 'Purchasing',
    'icon'  => 'fas fa-file-signature',

    'children' => [
      [
        'title' => 'New Purchase Order',
        'icon'  => 'fas fa-plus-circle',
        'url'   => 'purchase_orders'
      ],

      [
        'title' => 'Purchase Order List',
        'icon'  => 'fas fa-file-invoice',
        'url'   => 'purchase_orders/list'
      ],
      [
        'title' => 'Goods Receiving List',
        'icon'  => 'fas fa-dolly',
        'url'   => 'goods_receipts'
      ],
    ]
  ],
  [
    'title' => 'Inventory',
    'icon'  => 'fas fa-boxes',

    'children' => [
      [
        'title' => 'Inventory Inquiry',
        'icon'  => 'fas fa-search',
        'url'   => 'inventory'
      ],
      [
        'title' => 'New Adjustment',
        'icon'  => 'fas fa-plus-circle',
        'url'   => 'inventory_adjustments/create'
      ],
      [
        'title' => 'Adjustment List',
        'icon'  => 'fas fa-sliders-h',
        'url'   => 'inventory_adjustments'
      ],
    ]
  ],

  [
    'header' => 'ADMINISTRATION',
  ],
  [
    'title' => 'Administration',
    'icon'  => 'fas fa-cogs',

    'children' => [
      [
        'title' => 'Settings',
        'icon'  => 'fas fa-cog',
        'url'   => 'company'
      ],

      [
        'title' => 'Users',
        'icon'  => 'fas fa-user-cog',
        'url'   => 'users'
      ],
    ]
  ],
];