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
      [
        'title' => 'Outlet Types',
        'icon'  => 'fas fa-store',
        'url'   => 'outlet-types'
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
        'title' => 'Purchase Order List',
        'icon'  => 'fas fa-file-invoice',
        'url'   => 'purchase-orders/list'
      ],
      [
        'title' => 'Goods Receiving List',
        'icon'  => 'fas fa-dolly',
        'url'   => 'goods-receipts'
      ],
      [
        'title' => 'Purchase Returns',
        'icon'  => 'fas fa-exchange-alt',
        'url'   => 'purchase-returns'
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
        'title' => 'Adjustment List',
        'icon'  => 'fas fa-sliders-h',
        'url'   => 'inventory-adjustments'
      ],
      [
        'title' => 'Stock Transfer List',
        'icon'  => 'fas fa-exchange-alt',
        'url'   => 'stock-transfers'
      ],
    ]
  ],

  [
    'title' => 'Sales',
    'icon'  => 'fas fa-shopping-cart',

    'children' => [
      [
        'title' => 'Sales Order List',
        'icon'  => 'fas fa-clipboard-list',
        'url'   => 'sales-orders'
      ],
      /*** below code removed, users should not manually create a Sales Invoice from the menu */
      // [
      //   'title' => 'New Sales Invoice',
      //   'icon'  => 'fas fa-file-contract',
      //   'url'   => 'sales-invoices/create'
      // ],
      [
        'title' => 'Delivery Receipts',
        'icon'  => 'fas fa-truck',
        'url'   => 'delivery-receipts'
      ],
      [
        'title' => 'Sales Invoice List',
        'icon'  => 'fas fa-receipt',
        'url'   => 'sales-invoices'
      ],
      [
        'title' => 'Sales Return List',
        'icon'  => 'fas fa-exchange-alt',
        'url'   => 'sales-returns'
      ]
    ]
  ],

  [
    'header' => 'REPORTS',
  ],
  [
    'title' => 'Reports',
    'icon'  => 'fas fa-chart-bar',

    'children' => [
      [
        'title' => 'Sales per Supplier',
        'icon'  => 'fas fa-truck',
        'url'   => 'reports/sales-per-supplier'
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
      [
        'title' => 'Document Numbering',
        'icon'  => 'fas fa-list-ol',
        'url'   => 'document-numbering'
      ],
    ]
  ],
];