<?php


return [
    'permissions' => [
        //UserController actions
        'Can View All Users',
        'Can Create New User',
        'Can Update Exists User',
        'Can Look User Detail',
        'Can Delete User',

        //ZiraatController
        'Can Look Ziraat Extres',

        //AccountController
        'Can Look Kebir Page',
        'Can Look General Account Page',
        'Can See Frequency Data',


        //RoleController
        'Can Create Role',
        'Can Update Role',
        'Can Delete A Role',
        'Can Look A Role Properties',

        //PermissionController
        'Can Create Permission',
        'Can Update Permission',
        'Can Delete A Permission',
        'Can Look A Permission Properties',

        //CategoryController
        'Can Create Category',
        'Delete A Category',
        'Can Look Category',

        //RegionController
        'Can Create Region',
        'Delete A Region',
        'Can Look Region',

        //PublisherController
        'Can Create Publisher',
        'Delete A Publisher',
        'Can List All Publishers',
        'Can Look Publisher',
        'Can Update Publisher',

        //LanguageController
        'Can Create Language',
        'Delete A Language',
        'Can List All Languages',
        'Can Look Language',

        //SupplierController
        'Can Create Supplier',
        'Delete A Supplier',
        'Can List All Suppliers',
        'Can Look Supplier',
        'Can Update Supplier',

        //CustomerController
        'Can Create Customer',
        'Delete A Customer',
        'Can List All Customers',
        'Can Look Customer',
        'Can Update Customer',
        'Can Verify Customer',

        //GameController
        'Can Create Game',
        'Delete A Game',
        'Can List All Games',
        'Can Update Game',
        'Can See Last Stock Updates',
        'Can Stock Export',
        'Can See Stocks',

        //OfferController
        'Can Create Offer',
        'Delete A Offer',
        'Can List All Offers',
        'Can Update Offer',
        'Can Add Money To Offer',

        //KeyController
        'Can Create Key',
        'Delete A Key',
        'Can List All Keys',
        'Can Update Key',
        'Can Add Money To Key',
        'Can Archive Or Restore The Keys',
        'Can Do Follow Key',
        'Can Delete Collect Keys',
        'Can Export Keys',
        'Can Delete Multiple Keys',

        //AccountController
        'Can Check Frequency Data',
        'Can See Kebir Page',
        'Can See Kdv Amounts Per Month',

        //MarketPlaceController
        'Can Change Marketplase Settings',
        'Can List All Matches Game',
        'Can Match A Game With A Vendor',

        //OrderController
        'Can Create Order',
        'Can Show Detail Order',
        'Delete A Order',
        'Can List All Orders',
        'Can Update Order',
        'Can Get Order Detail Zip',
        'Can See Api Sales',
        'Can See Etail Sales',
        'Can Get Export Orders',
        'Can Get Export Etail Orders',

        //NotificationApiController
        'Can See All Notifications',

        //AnalyticController
        'Can Make Analytic By Game',

        //JarController
        'Can Create Moneybox',
        'Can Delete A Moneybox',
        'Can List All Moneyboxes',
        'Can Update Moneybox',
        'Can Add Money To Moneybox',

        //JarTransactionController
        'Can Lis All Transactions',
        'Can List Transactions To Change Keys',
        'Can Update Keys As Group',

        //AdminJarTransactionRequestController
        'Can List Customer Transaction Request',
        'Can Update Transaction Request For Customer',

        //ExpenseController
        'Can List Expenses',
        'Can Add Expense',
        'Can Add Delete Expense',
        'Can Update Expense',
    ],

    'roles' => [
        "B2B Panel",
        'Manager',
        'Backend Developer',
        'Frontend Developer',
        'Marketing',
        'Finance',
        'Customer',
        'Publisher',
        'Supplier',
    ],
];

