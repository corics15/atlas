<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['atlas_menu'] = [
  [
    'header' => 'MAIN',
  ],
  [
    'title' => 'Dashboard',
    'icon'  => 'fas fa-fire',
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
        'url'   => 'purchase-orders/list',
        'access' => ['ADMIN', 'MANAGER', 'STAFF', 'VIEWER'],
      ],
      [
        'title' => 'Goods Receiving List',
        'icon'  => 'fas fa-dolly',
        'url'   => 'goods-receipts',
        'access' => ['ADMIN', 'MANAGER', 'STAFF', 'VIEWER'],
      ],
      [
        'title' => 'Purchase Returns',
        'icon'  => 'fas fa-exchange-alt',
        'url'   => 'purchase-returns',
        'access' => ['ADMIN', 'MANAGER', 'STAFF', 'VIEWER'],
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
        'url'   => 'inventory',
        'access' => ['ADMIN', 'MANAGER', 'STAFF', 'VIEWER'],
      ],
      [
        'title' => 'Adjustment List',
        'icon'  => 'fas fa-sliders-h',
        'url'   => 'inventory-adjustments',
        'access' => ['ADMIN', 'MANAGER', 'STAFF'],
      ],
      [
        'title' => 'Stock Transfer List',
        'icon'  => 'fas fa-exchange-alt',
        'url'   => 'stock-transfers',
        'access' => ['ADMIN', 'MANAGER', 'STAFF'],
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
        'url'   => 'sales-orders',
        'access' => ['ADMIN', 'MANAGER', 'STAFF', 'VIEWER'],
      ],
      [
        'title' => 'Delivery Receipts',
        'icon'  => 'fas fa-truck',
        'url'   => 'delivery-receipts',
        'access' => ['ADMIN', 'MANAGER', 'STAFF', 'VIEWER'],
      ],
      [
        'title' => 'Sales Invoice List',
        'icon'  => 'fas fa-receipt',
        'url'   => 'sales-invoices',
        'access' => ['ADMIN', 'MANAGER', 'STAFF', 'VIEWER'],
      ],
      [
        'title' => 'Sales Return List',
        'icon'  => 'fas fa-exchange-alt',
        'url'   => 'sales-returns',
        'access' => ['ADMIN', 'MANAGER', 'STAFF', 'VIEWER'],
      ]
    ]
  ],

  [
    'title' => 'Accounting',
    'icon'  => 'fas fa-calculator',

    'children' => [
      [
        'title' => 'Customer Payments',
        'icon'  => 'fas fa-money-check-alt',
        'url'   => 'customer-payments',
        'access' => ['ADMIN', 'MANAGER', 'STAFF', 'VIEWER'],
      ],
      [
        'title' => 'Customer Ledger',
        'icon'  => 'fas fa-book',
        'url'   => 'customer-ledger',
        'access' => ['ADMIN', 'MANAGER', 'STAFF', 'VIEWER'],
      ],
      [
        'title' => 'Statement of Account',
        'icon'  => 'fas fa-file-alt',
        'url'   => 'statement-of-account',
        'access' => ['ADMIN', 'MANAGER', 'STAFF', 'VIEWER'],
      ],
      [
        'title' => 'A/R Aging',
        'icon'  => 'fas fa-calendar-alt',
        'url'   => 'accounts-receivable-aging',
        'access' => ['ADMIN', 'MANAGER', 'STAFF', 'VIEWER'],
      ],
    ],
  ],

  [
    'header' => 'REPORTS',
  ],
  [
    'title' => 'Reports',
    'icon'  => 'fas fa-chart-bar',

    'children' => [
      [
        'title' => 'Sales Per Supplier',
        'icon'  => 'fas fa-chart-area',
        'url'   => 'reports/sales-per-supplier',
        'access' => ['ADMIN', 'MANAGER', 'STAFF', 'VIEWER'],
      ],
      [
        'title' => 'Sales Per Customer',
        'icon'  => 'fas fa-chart-bar',
        'url'   => 'reports/sales-per-customer',
        'access' => ['ADMIN', 'MANAGER', 'STAFF', 'VIEWER'],
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
        'title' => 'Company',
        'icon'  => 'fas fa-cog',
        'url'   => 'company',
        'access' => ['ADMIN'],
      ],
      [
        'title' => 'Users',
        'icon'  => 'fas fa-user-cog',
        'url'   => 'users',
        'access' => ['ADMIN']
      ],
      [
        'title' => 'Document Numbering',
        'icon'  => 'fas fa-list-ol',
        'url'   => 'document-numbering',
        'access' => ['ADMIN'],
      ],
    ]
  ],
];