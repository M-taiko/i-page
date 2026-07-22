<?php

namespace Database\Seeders;

use App\Models\OrganizationTemplate;
use Illuminate\Database\Seeder;

class OrganizationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'key' => 'hotel',
                'name' => 'Hotel',
                'industry_key' => 'hospitality',
                'default_departments' => ['Front Desk', 'Housekeeping', 'Food & Beverage', 'Maintenance'],
                'default_channels' => [
                    ['name' => 'Guest Announcements', 'type' => 'public'],
                    ['name' => 'Staff Updates', 'type' => 'private'],
                ],
                'default_roles' => ['organization_admin', 'manager', 'staff', 'guest'],
            ],
            [
                'key' => 'university',
                'name' => 'University',
                'industry_key' => 'education',
                'default_departments' => ['Admissions', 'Faculty Affairs', 'Student Services', 'IT Services'],
                'default_channels' => [
                    ['name' => 'Campus Announcements', 'type' => 'public'],
                    ['name' => 'Faculty Channel', 'type' => 'private'],
                ],
                'default_roles' => ['organization_admin', 'manager', 'staff', 'member'],
            ],
            [
                'key' => 'hospital',
                'name' => 'Hospital',
                'industry_key' => 'healthcare',
                'default_departments' => ['Emergency', 'Outpatient', 'Nursing', 'HR'],
                'default_channels' => [
                    ['name' => 'Public Health Notices', 'type' => 'public'],
                    ['name' => 'Clinical Staff', 'type' => 'private'],
                ],
                'default_roles' => ['organization_admin', 'manager', 'staff', 'guest'],
            ],
            [
                'key' => 'retail',
                'name' => 'Retail / Supermarket',
                'industry_key' => 'retail',
                'default_departments' => ['Store Operations', 'Inventory', 'Customer Service'],
                'default_channels' => [
                    ['name' => 'Offers & Promotions', 'type' => 'public'],
                    ['name' => 'Store Staff', 'type' => 'private'],
                ],
                'default_roles' => ['organization_admin', 'manager', 'staff', 'guest'],
            ],
            [
                'key' => 'corporate',
                'name' => 'Corporate Enterprise',
                'industry_key' => 'corporate',
                'default_departments' => ['Human Resources', 'Finance', 'Operations', 'IT'],
                'default_channels' => [
                    ['name' => 'Company Announcements', 'type' => 'public'],
                    ['name' => 'Internal Communications', 'type' => 'private'],
                ],
                'default_roles' => ['organization_admin', 'manager', 'staff', 'member'],
            ],
        ];

        foreach ($templates as $template) {
            OrganizationTemplate::updateOrCreate(['key' => $template['key']], $template);
        }
    }
}
