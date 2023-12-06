<?php

namespace App\Enums;

enum RoleEnum: string
{
    case B2B_PANEL = 'B2B Panel';
    case MANAGER = 'Manager';
    case BACKEND_DEVELOPER = 'Backend Developer';
    case FRONTEND_DEVELOPER = 'Frontend Developer';
    case MARKETING = 'Marketing';
    case FINANCE = 'Finance';
    case CUSTOMER = 'Customer';
    case SUPPLIER = 'Supplier';
    case PUBLISHER = 'Publisher';

}
